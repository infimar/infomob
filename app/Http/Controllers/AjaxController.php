<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Category;
use App\Organization;
use App\Branch;
use App\City;
use DB;

class AjaxController extends InfomobController
{
    public function getOrganizationsByName(Request $request)
    {
        $searchTerm = $request->input('q');
        $pageNum = $request->input('page');
        $perPage = 30;  // like in select2

        $organizations = Organization::where('name', 'LIKE', '%' . $searchTerm . '%')
                            ->skip(($pageNum - 1) * $perPage)
                            ->take($perPage)
                            ->orderBy('name', 'ASC')
                            ->get(['id', 'name', 'status']);

        return response()->json([
            'items' => $organizations->toArray(),
            'total_count' => Organization::where('name', 'LIKE', '%' . $searchTerm . '%')->count()
        ]);
    }

    public function changeCategoryParent(Request $request)
    {
        // dd($request->all());

        try
        {
            $root = Category::findOrFail($request->input('parentId'));
            
            $category = Category::findOrFail($request->input('id'));
            $category->makeChildOf($root);

            return response()->json(['code' => 200]);
        } 
        catch (Exception $e)
        {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function removeFromTopTen(Request $request)
    {
        $input = $request->input('data');

        DB::table('toptens')
            ->where('city_id', $input['cityId'])
            ->where('category_id', $input['categoryId'])
            ->where('organization_id', $input['organizationId'])
            ->delete();

        return response()->json(["code" => 200]);
    }

    public function topTenReorder(Request $request)
    {
        $data = $request->input('data');

        $input = $data['input']; 
        $input = str_replace("item[]=", "", $input);

        $ids = explode("&", $input);
        
        foreach ($ids as $key => $id)
        {
            DB::table('toptens')
                ->where('city_id', $data['cityId'])
                ->where('category_id', $data['categoryId'])
                ->where('organization_id', $id)
                ->update(['order' => $key + 1]);
        }

        return response()->json(["code" => 200]);
    }

    public function addToTopTen(Request $request)
    {
        $input = $request->input('data');

        $organization = Organization::findOrFail($input['id']);
        if ($organization->status != "published")
        {
            return response()->json(["code" => "error", "msg" => "Ошибка: сначала опубликуйте организацию"]);
        }

        $topTen = DB::table('toptens')
            ->where('city_id', $input['cityId'])
            ->where('category_id', $input['categoryId'])
            ->where('organization_id', $input['id'])
            ->first();

        if ($topTen == null)
        {
            // add
            DB::table('toptens')->insert([
                'city_id' => $input['cityId'],
                'category_id' => $input['categoryId'],
                'organization_id' => $input['id']
            ]);

            return response()->json(["code" => "added"]);
        } 
        else
        {
            // remove one
            DB::table('toptens')
            ->where('city_id', $input['cityId'])
            ->where('category_id', $input['categoryId'])
            ->where('organization_id', $input['id'])
            ->delete();

            return response()->json(["code" => "removed"]);
        }
    }

    public function makeFeaturedBranch(Request $request)
    {
        $branch = Branch::findOrFail($request->input('id'));
        $branch->is_featured = ($branch->is_featured == 0) ? 1 : 0;
        $branch->save();

        return response()->json(["code" => 200, "is_featured" => $branch->is_featured]);
    }

    public function makeMainBranch(Request $request)
    {
        $branch = Branch::findOrFail($request->input('id'));
        $branch->type = "main";
        $branch->save();

        // other branches are custom now
        // $otherBranches = Branch::where("organization_id", $branch->organization_id)->where("id", "!=", $branch->id)->get();
        // foreach ($otherBranches as $key => $otherBranch) 
        // {
        //     $otherBranch->type = "custom";
        //     $otherBranch->save();
        // }

        return response()->json(["code" => 200]);
    }

    public function toggleStatus(Request $request)
    {
    	$result = [
    		"status" => "",
    		"response" => ""
    	];

    	$input = $request->input('data');
    	$class = "";
    	$statusLabel = "";

    	switch ($input['model']) 
    	{
    		case "category":
    			try
    			{
    				$category = Category::findOrFail($input["id"]);
    				$category->status = ($category->status == "published") ? "draft" : "published";
    				$category->save();

    				if ($category->status == "published")
					{	
						$class = "label label-success";
					}
					elseif ($category->status == "draft") 
					{
						$class = "label label-danger";
					}
					else
					{
						$class = "label label-default";
					}
					
					$statusLabel = Category::statuses($category->status);

    				$result["status"] = "success";
    				$result["response"] = [
    					"class" => $class,
    					"status" => $category->status,
    					"statusLabel" => $statusLabel
					];
    			}
    			catch (Exception $e)
    			{
    				$result["status"] = "error";
    				$result["response"] = "DB error: " . $e->getMessage();
    			}

    			break;

            case "organization":
                try
                {
                    $organization = Organization::findOrFail($input["id"]);
                    $organization->status = ($organization->status == "published") ? "draft" : "published";
                    $organization->save();

                    if ($organization->status == "published")
                    {   
                        $class = "label label-success";
                    }
                    elseif ($organization->status == "draft") 
                    {
                        $class = "label label-danger";
                    }
                    else
                    {
                        $class = "label label-default";
                    }
                    
                    $statusLabel = Category::statuses($organization->status);

                    $result["status"] = "success";
                    $result["response"] = [
                        "class" => $class,
                        "status" => $organization->status,
                        "statusLabel" => $statusLabel
                    ];
                }
                catch (Exception $e)
                {
                    $result["status"] = "error";
                    $result["response"] = "DB error: " . $e->getMessage();
                }

                break;

            case "branch":
                try
                {
                    $branch = Branch::findOrFail($input["id"]);
                    $branch->status = ($branch->status == "published") ? "draft" : "published";
                    $branch->save();

                    if ($branch->status == "published")
                    {   
                        $class = "label label-success";
                    }
                    elseif ($branch->status == "draft") 
                    {
                        $class = "label label-danger";
                    }
                    else
                    {
                        $class = "label label-default";
                    }
                    
                    $statusLabel = Category::statuses($branch->status);

                    $result["status"] = "success";
                    $result["response"] = [
                        "class" => $class,
                        "status" => $branch->status,
                        "statusLabel" => $statusLabel
                    ];
                }
                catch (Exception $e)
                {
                    $result["status"] = "error";
                    $result["response"] = "DB error: " . $e->getMessage();
                }

                break;

            case "city":
                try
                {
                    $city = City::findOrFail($input["id"]);
                    $city->status = ($city->status == "published") ? "draft" : "published";
                    $city->save();

                    if ($city->status == "published")
                    {   
                        $class = "label label-success";
                    }
                    elseif ($city->status == "draft") 
                    {
                        $class = "label label-danger";
                    }
                    else
                    {
                        $class = "label label-default";
                    }
                    
                    $statusLabel = Category::statuses($city->status);

                    $result["status"] = "success";
                    $result["response"] = [
                        "class" => $class,
                        "status" => $city->status,
                        "statusLabel" => $statusLabel
                    ];
                }
                catch (Exception $e)
                {
                    $result["status"] = "error";
                    $result["response"] = "DB error: " . $e->getMessage();
                }

                break;

    		default:
    			$result["status"] = "error";
    			$result["response"] = "Invalid model.";
    			break;
    	}

    	return response()->json($result);
    }

    public function topIt(Request $request)
    {
        $id = $request->input('id');
        $result = [
            "status" => "",
            "response" => ""
        ];
        $class = "";
        $order = "";

        try
        {
            $organization = Organization::findOrFail($id);

            if ($organization->status != "published")
            {
                $result["status"] = "error";
                $result["response"] = "Сначала опубликуйте организацию";
            }
            else
            {
                if ($organization->order != 9999)
                {
                    $organization->order = 9999;
                    $class = "btn btn-sm btn-default";
                }
                else
                {
                    $lastTopOrganization = Organization::where("order", "!=", 9999)->orderBy("order", "DESC")->first();

                    $organization->order = $lastTopOrganization->order + 1;
                    $class = "btn btn-sm btn-warning";
                }

                $organization->save();

                $result["status"] = "success";
                $result["response"] = [
                    "class" => $class,
                    "order" => $organization->order
                ];
            }            
        }
        catch (Exception $e)
        {
            $result["status"] = "error";
            $result["response"] = $e->getMessage();
        }

        return $result;
    }
}
