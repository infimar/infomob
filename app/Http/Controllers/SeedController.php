<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Response;
use File;
use DB;
use Excel;

use App\Category;
use App\City;
use App\Raion;
use App\Organization;
use App\Branch;
use App\Phone;
use App\Social;
use App\Photo;

class SeedController extends Controller
{
	public function __construct()
	{
		// header('Content-Type: text/html; charset=utf-8');
	}

    public function index()
    {
    	return view('seed/index');
    }

    /**
     * Search for query
     * @return string query
     */
    public function search($query)
    {
    	$results = [];
    	$param = explode("=", $query);

    	switch ($param[0]) 
    	{
    		case "phone":
    			$results = $this->searchByPhone($param[1]);
    			break;
    		
    		default:
    			# code...
    			break;
    	}

    	return Response::json($results);
    }

    /**
     * Compare organization's name with others in "category_id" category
     * @param  int $categoryId category id
     * @param  string $name       organization's name
     */
    public function compare($category_id = 0, $name = "")
    {
    	// dd($category_id);
    	echo "<pre>";

    	// $this->showCategoriesTree();

		// get all organizations for specified category
		$organizations = [];

		$categories = Category::with('organizations')->whereParentId($category_id)->get();
		foreach ($categories as $key => $category) 
		{
			foreach ($category->organizations as $organization)
			{
				$organizations[] = $organization;
			}
		}

		foreach ($organizations as $organization)
		{
			$similarity = $this->isSimilar($organization->name, $name);
			if ($similarity['percent'] >= 90)
			{
				echo $organization->name . " <> " . $name . " = " . $similarity['percent'];
				echo "\n";
			}
		}				

		echo "</pre>";
    }

    /**
     * Check table data
     * @param  string $table table name
     */
    public function check($table)
    {
    	$countParents = 0;
    	$countChildren = 0;

    	echo "<pre>";

    	if ($table === "categories")
    	{
    		echo "ВСЕ КАТЕГОРИИ:\n\n";
    		$roots = Category::roots()->orderBy("name", "ASC")->get();

    		foreach ($roots as $root)
    		{
    			$countParents++;
    			echo $root->name . ":\n";

    			foreach ($root->descendants()->get() as $child)
    			{
    				$countChildren++;
    				echo "\t- " . $child->name . "\n";
    			}    			
    		}
    	}
    	else if ($table === "cities")
    	{
    		echo "ВСЕ ГОРОДА:\n\n";
    		$cities = City::with("raions")->orderBy("name", "ASC")->get();

    		foreach ($cities as $key => $city) 
    		{
    			$countParents++;
    			echo $city->name . ":\n";

    			foreach ($city->raions as $raion)
    			{
    				$countChildren++;
    				echo "\t- " . $raion->name . "\n";
    			}
    		}

    	}
    	
    	echo "\n\n";
    	echo " Count (parents): " . $countParents . "\n";
    	echo "Count (children): " . $countChildren . "\n";
    	echo "</pre>";
    }

    /**
     * Load and seed a category.
     * @param string $src json filename to be seeded
     */
    public function category($src)
    {
    	echo "<pre>";

    	$data = $this->getJson($src);
    	
    	if ($data === null)
    	{
    		return "No data. Maybe there is an error?\n";
    	}

	    foreach ($data as $key => $item) 
	    {
	    	$this->seedCategory($item);
	    	echo "\n\n";
	    }
    	

	    echo "\n-----\n";
	    echo "DONE.";
		echo "</pre>";
    }

    /**
     * Load and seed cities and their rains.
     * @param  string $src source json file
     */
    public function city($src)
    {
    	echo "<pre>";

    	$data = $this->getJson($src);
    	
    	if ($data === null)
    	{
    		return "No data. Maybe there is an error?\n";
    	}

	    foreach ($data as $key => $item) 
	    {
	    	$this->seedCity($item);
	    	echo "\n\n";
	    }    	

	    echo "\n-----\n";
	    echo "DONE.";
		echo "</pre>";
    }

    public function organization($src)
    {
    	echo "<pre>";
    	$data = $this->getJson($src);

    	if ($data === null)
    	{
    		return "No data. Maybe there is an error?\n";
    	}

    	foreach ($data as $key => $item) 
	    {
	    	$this->seedOrganization($item);
	    	echo "\n\n";
	    }

	    echo "\n-----\n";
	    echo "DONE.";
		echo "</pre>";
    }


    /**
     * Seed and print category
     * @param  json $category category to be seeded
     */
    private function seedCategory($category)
    {
    	// check for existing root category		   
	    if (Category::whereName($category->name)->first())
	    {
	    	// interrupt
	    	echo "Category [" . $category->name . "] already exists.\n";
	    	return;
	    }

	    // create root category
	    $root = Category::create(['name' => $category->name]);
	    echo "Category [" . $category->name . "] created.\n";
	    echo "-----\n\n";

	    // create its subcategories
	    foreach ($category->subcategories as $key => $subcategory) 
	    {
	    	$root->children()->create(['name' => $subcategory->name]);
	    	echo "\tsubcategory [" . $subcategory->name . "] created.\n";
	    }
    }

    /**
     * Seed city
     * @param  StdClass $cityData parsed json object
     */
    private function seedCity($cityData)
    {
    	if (City::whereName($cityData->name)->first())
    	{
    		// interrupt
    		echo "City [" . $cityData->name . "] already exists.\n";
    		return;
    	}

    	// create city
    	$city = City::create(['name' => $cityData->name]);	// now we have city's id
    	echo "City [" . $cityData->name . "] created.\n";
	    echo "-----\n\n";

    	// create its raions
    	foreach ($cityData->raions as $key => $raion) 
	    {
	    	$raion = Raion::create(['name' => $raion->name, 'city_id' => $city->id]);
	    	echo "\traion [" . $raion->name . "] created.\n";
	    }
    }

    /**
     * Seed organization with branches
     * @param  StdClass $organizationData parsed json object
     */
    private function seedOrganization($organizationData)
    {
    	DB::beginTransaction();

    	if (Organization::whereName($organizationData->name)->first())
    	{
    		// interrupt
    		echo "Organization [" . $organizationData->name . "] already exists.\n";
    		return;
    	}

    	// get category's id
    	$category = Category::whereName($organizationData->category)->first();
    	if (!$category) dd("Could not find category.");

    	// create organization to get its ID
    	$organization = Organization::create([
    		"name" 			=> $organizationData->name,
    		"type" 			=> $organizationData->type,
			"category_id" 	=> $category->id,
			"description" 	=> $organizationData->description
		]);

		// create branches
		foreach ($organizationData->branches as $key => $branchData)
		{
			// copied name and description?
			$name = ($branchData->name === "_copy_") ? $organization->name : $branchData->name;
			$description = ($branchData->description === "_copy_") ? $organization->description : $branchData->description;

			// TODO: get raions' id
			$raion = ($branchData->raion === "_astana_") ? 5 : 1;

			// create branch
			$branch = Branch::create([
				"organization_id" 	=> $organization->id,
				"type" 				=> $branchData->type,
				"name" 				=> $name,
				"description" 		=> $description,
				"raion_id" 			=> $raion,
				"address" 			=> $branchData->address,
				"post_index" 		=> $branchData->post_index,
				"email"				=> $branchData->email,
				"hits"				=> $branchData->hits,
				"lat"				=> $branchData->lat,
				"lng"				=> $branchData->lng
			]);

			// create its phones
			foreach ($branchData->phones as $phoneData)
			{
				Phone::create([
					"branch_id"			=> $branch->id,
					"type"				=> $phoneData->type,
					"code_country"		=> $phoneData->code_country,
					"code_operator" 	=> $phoneData->code_operator,
					"number" 			=> $phoneData->number,
					"contact_person"	=> $phoneData->contact_person
				]);
			}

			// create its socials
			foreach ($branchData->socials as $socialData)
			{
				Social::create([
					"branch_id"			=> $branch->id,
					"type"				=> $socialData->type,
					"name"				=> $socialData->name,
					"contact_person"	=> $socialData->contact_person
				]);
			}

			// create its photos (logo)
			foreach ($branchData->photos as $key => $photoData) 
			{
				Photo::create([
					"branch_id"		=> $branch->id,
					"type"			=> $photoData->type,
					"path"			=> $photoData->path,
					"description"	=> $photoData->description
				]);
			}
		}

		// everything is fine -> go ahead
		DB::commit();
		echo "Organization [" . $organization->name . "] created.";
    }

    /**
     * Get data from json file.
     * TODO: check for "json" extension
     * @param  string $filename filename
     */
   	private function getJson($filename)
   	{
   		$data = null;
   		$path = public_path() . '/data/' . $filename;

   		try
		{
		    $contents = file_get_contents($path);
		    $contents = preg_replace('/\s+/', ' ', trim($contents));
		    // dd($contents);

		    $data = json_decode($contents);
		    // dd($data);
		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}
		
		return $data;
   	}

   	/**
   	 * Get similarity percentage of two words.
   	 * @param  string  $first  first string
   	 * @param  string  $second second string
   	 * @return Array         (percent, chars)
   	 */
   	private function isSimilar($first, $second)
   	{
   		$percent = 0;

   		$firstString = mb_strtoupper($first);
   		$secondString = mb_strtoupper($second);

   		$chars = similar_text($firstString, $secondString, $percent);

   		return [
   			"percent" => $percent,
   			"chars" => $chars
   		];
   	}

   	private function showCategoriesTree()
   	{
   		// first display categories table
    	$roots = Category::roots()->orderBy("name", "ASC")->get();

		foreach ($roots as $root)
		{
			echo $root->name . " [" . $root->id . "]:\n";

			foreach ($root->descendants()->get() as $child)
			{
				echo "\t- " . $child->name . " [" . $child->id . "]\n";
			}    			
		}
   	}

   	//
   	// LOAD BY EXCEL
   	//
   	public function excel($filename)
   	{
   		$path = public_path() . "/data/" . $filename;
   		
   		Excel::load($path, function($reader) 
   		{
   			$count = 0;
   			
   			// $reader->dd();
   			$rows = $reader->all();
   			
   			foreach ($rows as $key => $row) 
   			{
   				DB::beginTransaction();

   				// dd($rows);
   				// create organization
   				try
   				{
   					$organizationDB = $this->createOrganization($row);
   					// dd($organizationDB);

   					// create branches
	   				$branches = explode("***", $row['branches']);
	   				// dd($branches);

	   				foreach ($branches as $branch)
	   				{
	   					$branchInfo = explode("|", $branch);
	   					// dd($branchInfo);

	   					// from 1-10 branch info
	   					$branchDB = $this->createBranch($branchInfo, $organizationDB);
	   					// dd($branchDB);

	   					// 11 - phones
	   					$phones = explode(";", $branchInfo[10]);
	   					foreach ($phones as $phone) 
	   					{
	   						if (!$phone) continue;

	   						$phoneInfo = explode("_", $phone);
	   						// dd($phoneInfo);

	   						$phoneDB = $this->createPhone($phoneInfo, $branchDB);
	   						// dd($phoneDB);
	   					}

	   					// 12 - socials
	   					$socials = explode(";", $branchInfo[11]);
	   					foreach ($socials as $social) 
	   					{
	   						if (!$social) continue;

	   						$socialInfo = explode("_", $social);
	   						// dd($socialInfo);

	   						$social = $this->createSocial($socialInfo, $branchDB);
	   						// dd($social);
	   					}

	   					// 13 - photos
	   					$photos = explode(";", $branchInfo[12]);
	   					foreach ($photos as $photo) 
	   					{
	   						if (!$photo) continue;

	   						$photoInfo = explode("_", $photo);
	   						// dd($photoInfo);

	   						$photo = $this->createPhoto($photoInfo, $branchDB);
	   						// dd($photo);
	   					}

	   					$count++;	// inc organizations counter
	   					echo "Organization [" . $organizationDB->name . "] was created.<br>";
	   				}

	   				DB::commit();	   				
   				}
   				catch (\Exception $e)
   				{
   					echo("Error: organization [" . $row['name'] . "]: " . $e->getMessage() . "<br>");
   					DB::rollBack();
   				}
   			}


   			echo "<br><br>-----<br>DONE: " . $count . " organizations were created.";	
		});
   	} 

   	// createOrganization($data)
   	private function createOrganization($data)
   	{
   		$info = $data->toArray();
   		
   		try
   		{
   			if (Organization::whereName($info['name'])->first())
	    	{
	    		// interrupt
	    		// echo "Organization [" . $info['name'] . "] already exists.\n";
	    		throw new \Exception("Organization " . $info["name"] . " already exists");
	    	}

	    	// get category's id
	    	$category = Category::whereName($info['category'])->first();
	    	if (!$category) throw new \Exception("Cannot find the category: " . $category);

	    	// create organization to get its ID
	    	$organization = Organization::create([
	    		"name" 			=> $info['name'],
	    		"type" 			=> $info['type'],
				"category_id" 	=> $category->id,
				"description" 	=> $info['description']
			]);

			return $organization;
   		}
   		catch (\Exception $e)
   		{
   			throw new \Exception("Organization cannot be created: " . $e->getMessage());
   		}
   	}

   	// createBranch($data, $organization)
   	private function createBranch($info, $organization)
   	{
   		// 0 => "main"
	  	// 1 => "copy"
	  	// 2 => "copy"
	  	// 3 => "Абайский"
	  	// 4 => "мкр. Самал-3, 631"
	  	// 5 => "160000"
	  	// 6 => "email"
	  	// 7 => "0"
	  	// 8 => "0.00"
	  	// 9 => "0.00"
	  	// 10 => "mobile_7_701_7247299_cp;mobile_7_701_7247299_cp;"
	  	// 11 => "type_name_cp"
	  	// 12 => "type_path_description"

   		try
   		{
   			// copied name and description?
			$name = ($info[1] === "copy") ? $organization->name : $info[1];
			$description = ($info[2] === "copy") ? $organization->description : $info[2];
			$email = ($info[6] === "email") ? "" : $info[6];

			// TODO: get raions' id
			$raion = ($info[3] === "_astana_") ? 5 : 1;

			// create branch
			$branch = Branch::create([
				"organization_id" 	=> $organization->id,
				"type" 				=> $info[0],
				"name" 				=> $name,
				"description" 		=> $description,
				"raion_id" 			=> $raion,
				"address" 			=> $info[4],
				"post_index" 		=> $info[5],
				"email"				=> $email,
				"hits"				=> $info[7],
				"lat"				=> $info[8],
				"lng"				=> $info[9]
			]);

			return $branch;
   		}
   		catch (\Exception $e)
   		{
   			throw new \Exception("Branch cannot be created: " . $e->getMessage());
   		}
   	}

   	// createPhone($data, $branch)
   	private function createPhone($info, $branch)
   	{
   		// 0 => "mobile"
  		// 1 => "7"
  		// 2 => "701"
  		// 3 => "7247299"
  		// 4 => "cp"

   		$code_operator = str_replace(")", "", str_replace("(", "", str_replace(" ", "", $info[2])));
   		$number = str_replace("-", "", str_replace(" ", "", $info[3]));
   		$cp = ($info[4] === "cp") ? "" : $info[4];

  		try
   		{
   			$phone = Phone::create([
				"branch_id"			=> $branch->id,
				"type"				=> $info[0],
				"code_country"		=> $info[1],
				"code_operator" 	=> $code_operator,
				"number" 			=> $number,
				"contact_person"	=> $cp
			]);

			return $phone;
   		}
   		catch (\Exception $e)
   		{
   			throw new \Exception("Phone cannot be created: " . $e->getMessage());
   		}
   	}

   	// createSocial($data, $branch)
   	private function createSocial($info, $branch)
   	{
   		// 0 => "type"
  		// 1 => "name"
  		// 2 => "cp"

   		$cp = ($info[2] === "cp") ? "" : $info[2];

  		try
   		{
   			$social = Social::create([
				"branch_id"			=> $branch->id,
				"type"				=> $info[0],
				"name"				=> $info[1],
				"contact_person"	=> $cp
			]);

			return $social;
   		}
   		catch (\Exception $e)
   		{
   			throw new \Exception("Social cannot be created: " . $e->getMessage());
   		}
   	}

   	// createPhoto($data, $branch)
	private function createPhoto($info, $branch)
   	{
   		// 0 => "type"
  		// 1 => "path"
  		// 2 => "description"

   		$description = ($info[2] === "description") ? "" : $info[2];

  		try
   		{
   			$photo = Photo::create([
				"branch_id"		=> $branch->id,
				"type"			=> $info[0],
				"path"			=> $info[1],
				"description"	=> $description
			]);

			return $photo;
   		}
   		catch (\Exception $e)
   		{
   			throw new \Exception("Photo cannot be created: " . $e->getMessage());
   		}
   	}

   	//
   	// SEARCH
   	// 

}