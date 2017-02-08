<?php

/**
 * Seeding:
 *
 * - show form (category name, excel file)
 * - read from excel data row by row
 * - skip first header row
 * - for each row prepare info (if starts with * - also for organization)
 * 		- name of organization & branch (if starts with *)
 * 		- city (get id by name) - skip if there is no such city (but print it)
 * 		- description
 * 		- address
 * 		- email (if contains comma - take before comman)
 * 		- working hours
 * 		- phones (comma-separated)
 */

/**
 * Keys
    "nazvanie_organizatsii" => "*ТОО "Bai&R Group"*"
    "gorod" => "Шымкент"
    "opisanie_deyatelnosti" => "террасные доски, фасадные панели, садово-парковая мебель"
    "adres" => "ул.Ташенова, строение 51"
    "email" => "bairgroup@bk.ru"
    "chasy_rabota" => "Пн-Пт: 9:00 - 18:00, Сб-Вс - выходной"
    "telefony" => "7(7252)536666"
    "vebsayt_sots_seti" => "www.polydeck.kz"
    0 => null
 */


namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Excel;
use DB;
use Flash;
use Exception;
use App\Organization;
use App\Branch;
use App\Phone;
use App\Social;
use App\City;

class ExcelSeeder extends Controller
{
  protected $organization = null;

  public function index()
  {
  	return view('seeders.excel.index');
  }

  public function seed(Request $request)
  {
  	// get input
  	$input = $request->all();
  	$file = $request->file('file');
  	// dd($input);
  	
  	// upload file first
  	$destinationPath = public_path() . '/docs/excels/';
    $filename = uniqid() . '.' . $file->getClientOriginalExtension();
    $file->move($destinationPath, $filename);

    // read data
    $data = [];
  	Excel::load($destinationPath . $filename, function($reader) use (&$data) 
  	{
  		$items = [];

  		$result = $reader->get();
  		// dd($result);

  		foreach ($result as $key => $res) 
  		{
        // dd($res);
        
  			// exit when rows ended
  			if (is_null($res['gorod']) || empty($res['gorod'])) break;

  			$items[] = [
          'key'           => $key,
  				'name'          => $res['nazvanie_organizatsii'],
  				// 'city'          => $res['gorod'],
          'city'          => 'Шымкент', 
  				'description'   => $res['opisanie_deyatelnosti'],
  				'address'       => $res['adres'],
  				'email'         => $res['email'],
  				'working_hours' => $res['chasy_rabota'],
  				'contacts'      => $res['telefony'],
  				'socials'       => $res['vebsayt_sots_seti']
  			];
  		}

  		$data = $items;
  	});

  	// dd($data);
  
  	// parse data
  	$this->parseAndSave($data, $input['category_id']);

  	Flash::success("Imported");
  	return redirect()->action('ExcelSeeder@index');
  }


  protected function parseAndSave($data, $categoryId) 
  {
  	DB::beginTransaction();

  	foreach ($data as $item)
  	{
      // city
      $city = City::where('name', $item['city'])->first();
      if (is_null($city))
      {
        // wrong city, raise error
        throw new Exception("Wrong city name: " . $item['city']); 
      }

  		// create organization
      $organization = null;

      if ($item['key'] == 0) 
      {
        // first item - main branch
        $organization = $this->createOrganization($item['name'], $item['description'], $city);
        $this->organization = $organization;
        // dd([$organization, $this->organization]);
      } 
      else 
      {
        // others - custom branches, and organization is already created
        $organization = $this->organization;
      }
  		
  		// dd($organization);

  		// create branch
      // if key > 0 - custom branch 
  		$branch = $this->createBranch($item, $organization, $categoryId, $city, $item['key'] == 0);

  		// dd($branch);

			// attach to category
      $exists = DB::table('branch_category')
        ->where('branch_id', $branch->id)
        ->where('category_id', $categoryId)
        ->first();

      if (is_null($exists))
      {
        DB::table('branch_category')->insert([
          'branch_id'   => $branch->id,
          'category_id' => $categoryId
        ]);
      }
  	}

  	DB::commit();
  }


  private function createOrganization($name, $description, $city)
 	{
 		try
 		{
    	// clear name from stars
    	$name = str_replace("*", "", $name);

    	// check for existence
    	$organizationInDb = Organization::whereName($name)
                          ->whereHas('branches', function($query) use ($city) {
                            $query->where('city_id', $city->id);
                          })
                          ->first();

    	if (!is_null($organizationInDb))
    	{
    		return $organizationInDb;
    	}

    	// create organization to get its ID
    	$organization = Organization::create([
    		"name" 			   => trim($name),
    		"type" 			   => 'custom',
		  	"description"  => is_null($description) ? "" : $description,
		  	'status' 		   => 'published'
	    ]);

	    return $organization;
 		}
 		catch (\Exception $e)
 		{
 			throw new \Exception("Organization cannot be created: " . $e->getMessage());
 		}
 	}

 	// createBranch($data, $organization)
 	private function createBranch($info, $organization, $categoryId, $city, $main)
 	{
 		try
 		{
 			// clear name
      $name = str_replace("*", "", $info['name']);
      $name = ($name == '') ? $this->organization->name : $name;
      $name = trim($name);

		  // check for existence
    	// $branchInDb = Branch::
     //                  whereName($name)
     //                  ->whereOrganizationId($organization->id)
     //                  ->first();
    	// if (!is_null($branchInDb) && $branchInDb->organization->id == $organization->id)
    	// {
    	// 	return $branchInDb;
    	// }

      $type = ($main == true) ? 'main' : 'custom';
      // dd()

			// create branch
			$branch = Branch::create([
				"organization_id" => $organization->id,
				"city_id"			    => $city->id,
				"type" 				    => $type,
				"name" 				    => $name,
				"description" 		=> is_null($info['description']) ? "" : $info['description'],
				"address" 			  => is_null($info['address']) ? "" : $info['address'],
				"post_index" 		  => '',
				"email"				    => is_null($info['email']) ? "" : $info['email'],
				"hits"				    => 0,
				"lat"				      => '0.00',
				"lng"				      => '0.00',
				'pricingfile'		  => '',
				'status'			    => 'published',
				'working_hours'   => is_null($info['working_hours']) ? "" : $info['working_hours'],
			]);

      // with phones
      $phones = $this->createPhones($info['contacts'], $branch);
      // dd($phones);

      // and socials
      if (!empty($info['socials']) || !is_null($info['socials']))
      {
        $socials = $this->createSocials($info['socials'], $branch);
        // dd($socials);  
      }

		  return $branch;
 		}
 		catch (\Exception $e)
 		{
 			throw new \Exception("Branch cannot be created: " . $e->getMessage());
 		}
 	}

 	// create phones
 	private function createPhones($phonesString, $branch)
 	{
 		$phones = [];
 		$data = explode(",", $phonesString);
 		
 		foreach ($data as $item)
 		{
 			$item = trim($item);

      // if empty - skip
      if (empty($item)) continue;

 			// parse phone
 			$firstParenthesis = strpos($item, "(");
 			$lastParenthesis = strpos($item, ")");

 			// if no parenthesises
 			if ($firstParenthesis == false && $lastParenthesis == false)
 			{
 				// $codeCountry = "7";
 				// $codeOperator = "7252";
 				// $number = $item;
 				// $type = "work";

        $codeCountry = "7";
        $codeOperator = "short_numb";
        $number = $item;
        $type = "work";
 			}
 			else
 			{
 				// build phones
   			$codeCountry = "+" . substr($item, 0, $firstParenthesis);
   			$codeOperator = substr($item, $firstParenthesis + 1, $lastParenthesis - $firstParenthesis - 1);
   			$number = substr($item, $lastParenthesis + 1);
   			$type = strlen($codeOperator) >= 4 ? "work" : "mobile";   // works only for shymkent
 			}
 			
 			$phones[] = $this->createPhone([
 				'type'           => $type,
 				'code_country'   => $codeCountry,
 				'code_operator'  => $codeOperator,
 				'number'         => $number
 			], $branch);
 		}

 		return $phones;
 	}

 	// createPhone($data, $branch)
 	private function createPhone($info, $branch)
 	{
		try
 		{
 			$phone = Phone::create([
			"branch_id"			  => $branch->id,
			"type"				    => $info['type'],
			"code_country"		=> $info['code_country'],
			"code_operator" 	=> $info['code_operator'],
			"number" 			    => $info['number'],
			"contact_person"	=> ''
		]);

		return $phone;
 		}
 		catch (\Exception $e)
 		{
 			throw new \Exception("Phone cannot be created: " . $e->getMessage());
 		}
 	}

 	// create socials
 	private function createSocials($socialsInfo, $branch)
 	{
 		$socials = [];
 		$data = explode(",", $socialsInfo);

 		foreach ($data as $item)
 		{
 			$socials[] = $this->createSocial(trim($item), $branch);
 		}

 		return $socials;
 	}

 	// createSocial($data, $branch)
 	private function createSocial($info, $branch)
 	{
    if (strpos($info, 'http://') === false && strpos($info, 'https://') === false) 
    {
      // it is a lat;lng
      $points = [];
      $points = explode(";", $info);
      // dd($points);

      $branch->lat = $points[0];
      $branch->lng = $points[1];
      $branch->save();

      return;
    }

		try
 		{
 			$social = Social::create([
				"branch_id"			  => $branch->id,
				"type"				    => 'website',
				"name"				    => mb_strtolower($info),
				"contact_person"	=> ''
			]);

	    return $social;
 		}
 		catch (\Exception $e)
 		{
			  throw new \Exception("Social cannot be created: " . $e->getMessage());
 		}
 	}


}
