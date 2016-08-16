<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\City;
use App\Raion;
use App\Branch;
use App\Category;
use App\Organization;
use DB;

class ApiController extends Controller
{
    public function __construct()
	{
		header('Content-Type: application/json; charset=utf-8');
		
		// TODO: check for api key!
	}


	public function getCities(Request $request)
	{
		try
		{
			$cities = City::published()->orderBy("order")->get();
			$result = [];
			
			foreach ($cities as $city)
			{
				$result[] = [
					"id" => $city->id,
					"name" => $city->name
				];
			}
			
			return response()->json([
				"status" => "success",
				"cities" => $result
			]);
		}
		catch (Exception $e)
		{
			return response()->json([
				"status" => "error",
				"message" => $e->getMessage()
			]);
		}
	}

	
	public function searchAutoComplete(Request $request)
	{
		$noBranchesFound = false;
		$noCategoriesFound = false;
		
		if (!$request->has("city_id"))
		{
			return response()->json([
				"status" => "error", 
				"result" => "No city id"
			]);
		}
		
		if (!$request->has("query"))
		{
			return response()->json([
				"status" => "error", 
				"result" => "No query"
			]);
		}
		
		$result = [];
		$cityId = $request->input("city_id");
		$query = $request->input("query");
		
		// categories
		$categoriesDb = DB::table("categories")
			->where("status", "published")
			->where("name", "like", "%" . $query . "%")
			->orderBy("name", "ASC");

		$categories = $categoriesDb->get(["id", "name", "slug", "icon", "parent_id"]);
		
		foreach ($categories as $cat)
		{
			$cat->parent_id = ($cat->parent_id == null) ? 0 : $cat->parent_id;
		}
		
		if (count($categories) > 0)
		{
			$result["categories"] = $categories;
		}
		else
		{
			$noCategoriesFound = true;
		}
		
		// branches
		
		// by tags
		$idsByTags = DB::table('tagging_tagged')
			->where('taggable_type', 'App\Branch')
			->where('tag_name', 'like', '%' . $query . '%')
			->lists('taggable_id');
		
		$branchesByTags = Branch::published()
			->where("city_id", $cityId)
			->whereIn('id', $idsByTags)
			->limit(25)
			->orderBy('is_featured', 'DESC')
			->orderBy('name', 'ASC')
			->get(["id", "organization_id", "name", "city_id", "is_featured", "address"]);
		//dd($branchesByTags);
			
		// by name & descriptions
		$branches = Branch::published()
			->where("city_id", $cityId)
			->where(function ($q) use ($query) 
			{
                $q->where("description", "like", "%" . $query . "%")
				  ->orWhere("name", "like", "%" . $query . "%");
            })
			->orderBy('is_featured', 'DESC')
			->orderBy("name", "ASC")
			->limit(25)
			->get(["id", "organization_id", "name", "city_id", "is_featured", "address"]);
		//dd($branches);
			
		if (count($branches) > 0)
		{
			$result['branches'] = $branchesByTags->merge($branches);
		}
		else
		{
			$noBranchesFound = true;
		}
		
		// no results
		if ($noBranchesFound && $noCategoriesFound)
		{
			return response()->json([
				'status' => 'notfound'
			]);
		}
		
		return response()->json([
				"status" => "success",
				"result" => $result
			]);
	}
	
	public function getPhotos(Request $request)
	{
		if (!$request->has("branch_id"))
		{
			return response()->json([
				"status" => "error", 
				"result" => "No branch id"
			]);
		}
		
		try
		{
			$branchId = $request->input("branch_id");
			$branch = Branch::with([
				'photos' => function($q) {
					$q->where("type", "picture");
				}])
				->findOrFail($branchId);
			
			return response()->json([
				"status" => "success",
				"branch" => $branch->toArray()
			]);
		}
		catch (Exception $e)
		{
			
		}
	}
	
	public function getBranch(Request $request)
	{
		if (!$request->has("branch_id"))
		{
			return response()->json(["status" => "error", "result" => "No branch id"]);
		}
		
		$branchId = $request->input("branch_id");
		
		try
		{
			$branch = Branch::published()->with([
				"phones" => function ($query) {
					$query
						->select(["branch_id", "type", "code_country", "code_operator", "number", "contact_person"]);
				}, 
				"socials" => function ($query) {
					$query
						->select(["branch_id", "type", "name", "contact_person"]);
				},
				"photos" => function ($query) {
					$query
						->where("type", "picture")
						->select(["branch_id", "type", "path", "description"]);
				}, 
				"organization" => function ($query) {
					$query->select(["id"]);
				},
				"categories" => function ($query) {
					$query->select(["id", "name", "parent_id"]);
				}])
				->findOrFail($branchId);
				
			// category
			$categoryLabel = "";
			
			$length = count($branch->categories);
			foreach ($branch->categories as $key => $category)
			{
				// get root
				$parent = $category->parent()->first();				
				if ($parent != null) $categoryLabel .= $parent->name . " / ";
				
				$categoryLabel .= $category->name;
				$categoryLabel .= "\n";
			}

			
			// other branches?
			$otherBranches = Branch::where("organization_id", $branch->organization->id)
				->with([
				"city" => function ($query) {
					$query->select(["id", "name"]);
				}])
				->where("id", "!=", $branch->id)
				->get(["id", "name", "address", "city_id"]);
			
			return response()->json([
				"status" => "success",
				"branch" => $branch->toArray(),
				"category" => $categoryLabel,
				"otherBranches" => $otherBranches
			]);
		}
		catch (Exception $e)
		{
			
		}
	}
	
	public function getOrganization(Request $request)
	{
		if (!$request->has('city_id'))
		{
			return response()->json(["status" => "error", "result" => "No city id"]);
		}
		
		if (!$request->has('category_id'))
		{
			return response()->json(["status" => "error", "result" => "No category id"]);
		}
		
		if (!$request->has('organization_id'))
		{
			return response()->json(["status" => "error", "result" => "No organization id"]);
		}
		
		$cityId = $request->input('city_id');
		$categoryId = $request->input('category_id');
		$organizationId = $request->input("organization_id");
		
		try
		{
			$city = City::findOrFail($cityId);
		
			$organization = Organization::findOrFail($organizationId);
			
			// for the city
			$branches = Branch::published()
				->orderBy("name", "ASC")
				->where("organization_id", $organization->id)
				->whereHas("city", function ($query) use ($cityId) {
					$query->where("id", $cityId);
				})
				->whereHas("categories", function($query) use ($categoryId) {
					$query->where("category_id", $categoryId);
				})
				->with(["photos" => function ($query) {
					$query->where("type", "logo")->take(1);				
				}, "city"])
				->get(["id", "name", "address", "type", "city_id"]);
			
			// other branches
			$otherBranchesDB = Branch::published()
				->orderBy("name", "ASC")
				->where("organization_id", $organization->id)
				->whereHas("city", function ($query) use ($cityId) {
					$query->where("id", "!=", $cityId);
				})
				->with(["photos" => function ($query) {
					$query->where("type", "logo")->take(1);				
				}, "city"])
				->get(["id", "name", "address", "type", "city_id"]);
			
			$result = $branches->toArray();
			
			// if there is no -> notfound
			if (count($result) <= 0) {
				return response()->json(["status" => "notfound", "result" => "No branches found"]);
			}
			
			// other branches
			$otherBranches = $otherBranchesDB->toArray();
				
			return response()->json([
				"status" => "success",
				"city" => $city->name,
				"organization" => $organization->toArray(),
				"count" => count($result),
				"count_other" => count($otherBranches),
				"branches" => $result,
				"other_branches" => $otherBranches
			]);
		}
		catch (Exception $e)
		{
			return response()->json([
				"status" => "error",
				"error" => "DB error: " . $e->getMessage()
			]);
		}
	}
	
	public function getOrganizations(Request $request)
	{
		if (!$request->has('city_id'))
		{
			return response()->json(["status" => "error", "result" => "No city id"]);
		}
		
		if (!$request->has('category_id'))
		{
			return response()->json(["status" => "error", "result" => "No category id"]);
		}
		
		$cityId = $request->input('city_id');
		$categoryId = $request->input('category_id');
		
		$organizations = Organization::published()
			//->orderBy("name", "ASC")
			->orderBy("order", "ASC")
			->whereHas("branches", function ($query) use ($cityId, $categoryId) {
				$query->whereHas("city", function ($query) use ($cityId) {
					$query->where("id", $cityId);
				});
				$query->whereHas("categories", function ($query) use ($categoryId) {
					$query->where("category_id", $categoryId); 
				});
			})
			->get(["id", "name"]);
		
		$result = $organizations->toArray();
		
		if (count($result) <= 0) {
			return response()->json(["status" => "notfound", "result" => "No organizations found"]);
		}
		
		return response()->json(["status" => "success", "result" => $result]);
	}
	
	// TODO: get only favs branches
	public function getFavorites($favs)
	{
		$result = [];
		
		try
		{
			$ids = explode(",", $favs);
			$branches = Branch::whereIn("id", $ids)
				->orderBy("name", "ASC")
				->get(["id", "name"]);
			
			return response()->json([
				"status" => "success", 
				"result" => $branches->toArray()
			]);
		}
		catch (Exception $e)
		{
			return response()->json(["status" => "error", "result" => $e->getMessage()]);
		}
	}
	
	public function getCategories($cityId = 1)
	{
		$result = [];
		$sections = ["Категории"];
		$list = [];
		
		if ($cityId == 0)
		{
			$city = City::published()->first();
			$cityId = $city->id;
		}

		try
		{
			$city = City::findOrFail($cityId);
			$categories = Category::published()
				->where("parent_id", null)
				->orderBy("name", "ASC")
				->get(["id", "name", "slug", "icon"]);
			
			foreach ($categories as $category)
			{					
				$names = explode("|", $category->name);
				
				if (count($names) > 1)				
					$list[] = [
						"id" 			=> $category->id,
						"short_name" 	=> $names[0],
						"full_name" 	=> $names[1],
						"slug" 			=> $category->slug,
						"icon" 			=> $category->icon
					];
				else
					$list[] = [
						"id" 			=> $category->id,
						"short_name" 	=> $category->name,
						"full_name" 	=> $category->name,
						"slug" 			=> $category->slug,
						"icon" 			=> $category->icon
					];
			}
			
			$length = count($sections);
			for ($i = 0; $i < $length; $i++)
			{
				$result[] = [
					"id" 		=> $i,
					"section" 	=> $sections[$i],
					"items" 	=> $list
				];
			}
			
			return response()->json([
				"status" => "success",
				"result" => $result
			]);
		}
		catch(Exception $e)
		{
			return response()->json(['status' => 'error', 'result' => $e->getMessage()]);
		}
	}
	
	public function getSubcategories($parentId, $cityId = 1)
	{
		$result = [];
		$list = [];
		
		try
		{
			$city = City::findOrFail($cityId);
			$categories = Category::published()
				->whereParentId($parentId)
				->orderBy("name", "ASC")
				->get(["id", "name", "slug", "icon"]);
			
			// TODO: what if there are no categories?
			
			foreach ($categories as $category)
			{
				$list[] = [
					"id" 	=> $category->id,
					"name" 	=> $category->name,
					"slug" 	=> $category->slug,
					"icon" 	=> $category->icon
				];
			}
			
			return response()->json([
				"status" => "success",
				"result" => $categories->toArray()
			]);
		}
		catch(Exception $e)
		{
			return response()->json(['status' => 'error', 'result' => $e->getMessage()]);
		}
	}
	
	
	public function getServices($cityId = 1)
	{
		$result = [];
		
		try
		{
			$city = City::findOrFail($cityId);
			
			// TODO: get services for the given city! or raions (then fetch by raions as well)
			
			$sections = [
				"Экстренные службы"
			];
			
			$names = [
				"Пожарная служба",
				"Полиция", 
				"Скорая помощь",
				"Аварийная служба газоподачи",
				"Служба спасения ЧС",
				"Диспетчерская ЧС",
				"Справочная аптек",
				"АО Казахтелеком",
				"ЦБР"
			];

			$tips = [
				"101",
				"бесплатно со стационарного и мобильного телефонов",
				"с любого номера",
				"с любого номера",
				"с любого номера",
				"",
				"с любого номера",
				"прием телеграм",
				"ремонт",
			];

			$phones = [
				"101",
				"102",
				"103",
				"104",
				"112",
				"+77252539345",
				"001",
				"116",
				"165"
			];

			$services = [];

			$length = count($names);
			for ($i = 0; $i < $length; $i++) 
			{
				$services[] = [
					"name" 	=> $names[$i],
					"tips" 	=> $tips[$i],
					"phone" => $phones[$i]
				];
			}
			
			$length = count($sections);
			for ($i = 0; $i < $length; $i++)
			{
				$result[] = [
					"id" 		=> $i,
					"section" 	=> $sections[$i],
					"items" 	=> $services
				];
			}
	
			return response()->json([
				"status" 	=> "success",
				"city" 		=> $city,
				"result" 	=> $result
			]);
		}
		catch (Exception $e)
		{
			return response()->json(['status' => 'error', "result" => $e->getMessage()]);
		}
	}
}
