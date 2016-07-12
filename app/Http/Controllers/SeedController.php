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

use Goutte\Client;

class SeedController extends Controller
{
  protected $client;

	public function __construct()
	{
		// header('Content-Type: text/html; charset=utf-8');
    
    $this->client = new Client(); 
	}

  public function parse()
  {
    $urls = [
      "http://www.e-shymkent.kz/catalog/services/legal/1/0/",
      "http://www.e-shymkent.kz/catalog/services/legal/1/1/",
      "http://www.e-shymkent.kz/catalog/services/legal/1/2/",
      "http://www.e-shymkent.kz/catalog/services/legal/1/3/",
      "http://www.e-shymkent.kz/catalog/services/legal/1/4/",
    ];
    
    $result = [];
    
    foreach ($urls as $url) 
    {
      $data = $this->crawl($url);
      $result[] = $data;
    }
    
    $i = 0;
    foreach ($result as $items)
    {
      foreach ($items as $item)
      {
        // dd($item);
        $isCreated = $this->seedOrganization($item);
        
        if ($isCreated) $i++;
      }
    }

    echo "<hr>";
    echo "DONE: " . $i . " organizations created.";
  }

  public function crawl($url)
  {
    $crawler = $this->client->request('GET', $url);
    
    $indicatorKey = "Подробнее";
    $result = [];
    $idx = 0;
    $skip = false;
    $item = [];

    $categories = [];

    $data = $crawler->filterXPath('//table[@cellpadding="2"]/tr');
    $data->each(function ($node, $i) use (&$result, $indicatorKey, &$idx, $skip, &$item, &$categories) 
    {
      if ($i < 5) return;

      $text = $node->text();
      //echo $text . "<br>";

      if (mb_strpos($text, $indicatorKey) !== false) 
      {
        return;
      } 
      elseif (empty($text)) 
      {
        $result[$idx] = $item;

        $item = [];
        $idx += 1;
      }
      elseif (mb_strpos($text, "Категория") !== false) 
      {
        $item["category"] = $text;
      }
      elseif (mb_strpos($text, "Продукты") !== false) 
      {
        $item["description"] = $text;
      }
      elseif (mb_strpos($text, "Адрес") !== false) 
      {
        $item["address"] = $text;
      }
      elseif (mb_strpos($text, "Телефон") !== false) 
      {
        $item["phone"] = $text;
      }
      elseif (mb_strpos($text, "Факс") !== false) 
      {
        $item["fax"] = $text;
      }
      else 
      {
        $item["name"] = $text;
      }
    });

    // dd($result);

    $db = [];
    $i = 0;

    foreach ($result as $item) 
    {
      foreach ($item as $key => $value) 
      {
        if ($key == "name")
        {
          $value = str_replace(["\n", "\t", "\r"], '', $value);
          $start = mb_strpos($value, ".");
          $name = mb_substr(htmlspecialchars_decode($value), $start + 1);
          $db[$i]["name"] = trim($name);
        }
        elseif ($key == "description")
        {
          $start = mb_strpos($value, ":");
          $description = mb_substr(htmlspecialchars_decode($value), $start + 1);
          $db[$i]["description"] = trim($description);
        }
        elseif ($key == "category")
        {
          $value = str_replace(['.', "\n", "\t", "\r"], '', $value);
          $start = mb_strpos($value, '/');
          $category = mb_substr($value, $start + 1);          
          $db[$i]["category"] = trim($category);
        }
        elseif ($key == "address")
        {
          $value = str_replace(["\n", "\t", "\r"], '', $value);
          $start = mb_strpos($value, ':');
          $address = mb_substr($value, $start + 1);          
          $db[$i]["address"] = trim($address);
        }
        elseif ($key == "phone")
        {
          $start = mb_strpos($value, ':');
          $phone = mb_substr($value, $start + 1);

          // parse phone
          $phone = trim($phone);
          $start = mb_strpos($phone, "(");
          $codeCountry = mb_substr($phone, 0, $start);

          $end = mb_strpos($phone, ")");
          $codeOperator = mb_substr($phone, $start + 1, $end - $start - 1);

          $number = mb_substr($phone, $end + 1);
          $number = str_replace(["-", " "], "", $number);

          // if number contains ',' -> 2 numbers
          if (mb_strpos($number, ","))
          {
            $numbers = explode(",", $number);

            foreach ($numbers as $key => $num)
            {
              $db[$i]["phones"][] = [
                "type" => "work",
                "code_country" => trim($codeCountry),
                "code_operator" => trim($codeOperator),
                "number" => trim($num)
              ];
            }
          }
          else
          {
            $db[$i]["phones"][] = [
              "type" => "work",
              "code_country" => trim($codeCountry),
              "code_operator" => trim($codeOperator),
              "number" => trim($number)
            ];
          }
        }
        elseif ($key == "fax")
        {
          $start = mb_strpos($value, ':');
          $fax = mb_substr($value, $start + 1);

          // parse fax
          $fax = trim($fax);
          $start = mb_strpos($fax, "(");
          $codeCountry = mb_substr($fax, 0, $start);

          $end = mb_strpos($fax, ")");
          $codeOperator = mb_substr($fax, $start + 1, $end - $start - 1);

          $number = mb_substr($fax, $end + 1);
          $number = str_replace(["-", " "], "", $number);

          // if number contains ',' -> 2 numbers
          if (mb_strpos($number, ","))
          {
            $numbers = explode(",", $number);

            foreach ($numbers as $key => $num)
            {
              $db[$i]["faxes"][] = [
                "type" => "fax",
                "code_country" => trim($codeCountry),
                "code_operator" => trim($codeOperator),
                "number" => trim($num)
              ];
            }
          }
          else
          {
            $db[$i]["faxes"][] = [
              "type" => "fax",
              "code_country" => trim($codeCountry),
              "code_operator" => trim($codeOperator),
              "number" => trim($number)
            ];
          }
        }
      }

      $i += 1;
    }

    return $db;
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
          echo "<img src='" . asset("images/icons/" . $root->icon) . "'>";
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
      else if ($table == "organization")
      {
        $organization = Organization::with(["branches", "branches.phones", "branches.categories"])->first();
        dd($organization);
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
	    if (Category::where("slug", $category->slug)->first())
	    {
	    	// interrupt
	    	echo "Category [" . $category->name . "] already exists.\n";
	    	return;
	    }

	    // create root category
	    $root = Category::create([
        'name' => $category->name,
        'slug' => $category->slug,
        'icon' => $category->icon,
      ]);

	    echo "Category [" . $category->name . "] created.\n";
	    echo "-----\n\n";

	    // create its subcategories
	    foreach ($category->subcategories as $key => $subcategory) 
	    {
	    	$root->children()->create([
          'name' => $subcategory->name,
          'slug' => $subcategory->slug,
          'icon' => $subcategory->icon,
        ]);
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
   * @param  StdClass $data parsed json object
   */
  private function seedOrganization($data)
  {
  	DB::beginTransaction();

    if (!isset($data["name"]))
    {
      DB::rollBack();
      echo "No name<br>";
      return false;
    }

  	if (Organization::whereName($data["name"])->first())
  	{
  		// interrupt
  		echo "Organization [" . $data["name"] . "] already exists.<br>";
  		return false;
  	}

  	// get category's id
    if (!isset($data["category"]))
    {
      DB::rollBack();
      echo "No category<br>";
      return false;
    }

  	$category = Category::whereName($data["category"])->first();
  	if (!$category) dd("Could not find category.");

  	// create organization to get its ID
  	$organization = Organization::create([
  		"name" 			    => $data["name"],
  		"type" 			    => "custom",
		  "description" 	=> $data["description"]
	  ]);
    // dd($organization);

		// create branch
    $address = (isset($data["address"])) ? $data["address"] : "";

    $branch = Branch::create([
      "organization_id"   => $organization->id,
      "type"              => "main",
      "name"              => $organization->name,
      "description"       => $organization->description,
      "raion_id"          => 1,
      "address"           => $address,
      "post_index"        => "160000",
      "email"             => "",
      "hits"              => 0,
      "lat"               => "0.00",
      "lng"               => "0.00"
    ]);
    //dd($branch->toArray());

    // map branch to category!
    $pivotRecord = DB::table("branch_category")->insert([
      "branch_id"   => $branch->id,
      "category_id" => $category->id
    ]);
    // dd($pivotRecord);

    // create its phones
		if (isset($data["phones"]))
    {
      foreach ($data["phones"] as $phone)
      {
        $phoneRecord = Phone::create([
          "branch_id"      => $branch->id,
          "type"           => $phone["type"],
          "code_country"   => $phone["code_country"],
          "code_operator"  => $phone["code_operator"],
          "number"         => $phone["number"],
          "contact_person" => ""
        ]);
        //dd($phoneRecord);
      }
    }
			
    // create its faxes
    if (isset($data["faxes"]))
    {
      foreach ($data["faxes"] as $fax)
      {
        $phoneRecord = Phone::create([
          "branch_id"      => $branch->id,
          "type"           => $fax["type"],
          "code_country"   => $fax["code_country"],
          "code_operator"  => $fax["code_operator"],
          "number"         => $fax["number"],
          "contact_person" => ""
        ]);
        // dd($phoneRecord);
      }
    }
			

		// // create its socials
		// foreach ($branchData->socials as $socialData)
		// {
		// 	Social::create([
		// 		"branch_id"			=> $branch->id,
		// 		"type"				=> $socialData->type,
		// 		"name"				=> $socialData->name,
		// 		"contact_person"	=> $socialData->contact_person
		// 	]);
		// }

		// // create its photos (logo)
		// foreach ($branchData->photos as $key => $photoData) 
		// {
		// 	Photo::create([
		// 		"branch_id"		=> $branch->id,
		// 		"type"			=> $photoData->type,
		// 		"path"			=> $photoData->path,
		// 		"description"	=> $photoData->description
		// 	]);
		// }

		// everything is fine -> go ahead
		DB::commit();
		echo "Organization [" . $organization->name . "] created.<br>";
    return true;
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