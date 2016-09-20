<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Organization;
use App\Category;
use App\Branch;
use App\Phone;
use App\Social;
use App\Photo;
use JavaScript;
use Flash;
use DB;
use File;

class BranchesController extends AdminController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($organizationId)
    {
    	try 
    	{
    		$organization = Organization::findOrFail($organizationId);
        	$backUrl = url()->previous();
    		
    		JavaScript::put(['activeLink' => 'branches_create']);
        	return view('branches.admin.create', compact("organization", "backUrl"));
    	} 
    	catch (Exception $e) 
    	{
    		flash()->error('Ошибка: ' . $e->getMessage());
            return redirect()->back();
    	}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $organizationId)
    {
        // TODO: validation
        // dd($request->all());
        $input = $request->all();
        DB::beginTransaction();

        try 
        {
            // organization
            $organization = Organization::findOrFail($organizationId);
            // dd($organization);

            // branch
            // lat, lng
            $lat = (empty($input['branch_lat'])) ? "0.00" : $input['branch_lat'];
            $lng = (empty($input['branch_lng'])) ? "0.00" : $input['branch_lng'];

            // branch info

            // pricingfile
            $pricingfile = $request->file('branch_pricingfile');
            $pricingFilename = '';

            if ($pricingfile && $pricingfile->isValid()) 
            {
                $destinationPath = public_path() . '/docs/pricingfiles/';
                $filename = uniqid() . '.' . $pricingfile->getClientOriginalExtension();
                $pricingfile->move($destinationPath, $filename);

                $pricingFilename = $filename;
            }

            // create
            $branch = Branch::create([
                'organization_id' => $organization->id,
                'city_id' => $input['branch_cityId'],
                'name' => $input['branch_name'],
                'description' => $input['branch_description'],
                'address' => $input['branch_address'],
                'post_index' => $input['branch_postIndex'],
                'email' => $input['branch_email'],
                'lat' => $lat,
                'lng' => $lng,
                'working_hours' => $input['branch_workingHours'],
                'status' => $input['branch_status'],
                'type' => 'custom',
                'pricingfile' => $pricingFilename
            ]);
            // dd($branch);

            // set branch category
            $categoryIds = [];
            foreach ($input['branch_categoryIds'] as $key => $catId) 
            {
                $categoryIds[] = $catId;
            }

            $categories = Category::whereIn("id", $categoryIds)->get();
            foreach ($categories as $category) 
            {
                $success = DB::table('branch_category')->insert([
                    'branch_id' => $branch->id,
                    'category_id' => $category->id
                ]);
                // dd($success);
            }
            
            // branch phones
            if (isset($input['branch_phones']))
            {
            	foreach ($input['branch_phones'] as $phone)
	            {
	                $phone = Phone::create([
	                    'branch_id' => $branch->id,
	                    'type' => $phone['type'],
	                    'code_country' => $phone['code_country'],
	                    'code_operator' => $phone['code_operator'],
	                    'number' => $phone['number'],
	                    'contact_person' => $phone['contact_person'],
	                ]);
	                // dd($phone);
	            }
            }

            // branch socials
            if (isset($input['branch_socials']))
            {
	            foreach ($input['branch_socials'] as $social)
	            {
	                $social = Social::create([
	                    'branch_id' => $branch->id,
	                    'type' => $social['type'],
	                    'name' => $social['name'],
	                    'contact_person' => $social['contact_person'],
	                ]);
	                // dd($social);
	            }
	        }

            // tags
            $tags = explode("|", $input['branch_tags']);
            // dd($tags);
            foreach ($tags as $key => $tag) 
            {
                if (empty($tag)) continue;
                $branch->tag($tag);
            }
            // dd($branch->tagNames());

            DB::commit();
        } 
        catch (Exception $e) 
        {
            DB::rollBack();
            flash()->error('Ошибка: ' . $e->getMessage());
            return redirect()->back()->withInput();
        } 
        
        flash()->success("Филиал успешно добавлен");
        return redirect()->action('OrganizationsController@edit', ['organizationId' => $organization->id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $backUrl = url()->previous();

        try
        {
            $branch = Branch::
                with(['organization', 'phones', 'socials', 'photos', 'city', 'categories'])
                ->findOrFail($id);

            $tags = [];
            foreach ($branch->tagNames() as $key => $tag)
            {
                $tags[] = $tag;
            }

            $lastPhoneId = ($branch->phones->last() !== null) ? $branch->phones->last()->id : 0;
            $lastSocialId = ($branch->socials->last() !== null) ? $branch->socials->last()->id : 0;

            JavaScript::put([
                'activeLink' => 'branches_edit',
                'phoneId' => $lastPhoneId,
                'socialId' => $lastSocialId,
                'tags' => $tags
            ]);

            return view('branches.admin.edit', compact("branch", "backUrl", "tags"));
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка редактирования: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // TODO: validation
        // dd($request->all());
        $input = $request->all();

        try 
        {
            // organization
            $branch = Branch::findOrFail($id);
            // dd($branch);

            // pricing was changed ?
            $pricingWasChanged = false;
            $newPricing = null;
            $pricing = $request->file('branch_pricingfile');

            if ($pricing && $pricing->isValid()) 
            {   
                // remove old
                $oldPricing = public_path() . '/docs/pricingfiles/' . $branch->pricingfile;
                if (File::exists($oldPricing)) { File::delete($oldPricing); }

                // upload new
                $destinationPath = public_path() . '/docs/pricingfiles/';
                $filename = uniqid() . '.' . $pricing->getClientOriginalExtension();
                $pricing->move($destinationPath, $filename);

                $pricingWasChanged = true;
                $newPricing = $filename;
            }

            // lat, lng
            $lat = (empty($input['branch_lat'])) ? "0.00" : $input['branch_lat'];
            $lng = (empty($input['branch_lng'])) ? "0.00" : $input['branch_lng'];

            // branch info
            $branch->city_id = $input['branch_cityId'];
            $branch->name = $input['branch_name'];
            $branch->description = $input['branch_description'];
            $branch->address = $input['branch_address'];
            $branch->post_index = $input['branch_postIndex'];
            $branch->email = $input['branch_email'];
            $branch->lat = $lat;
            $branch->lng = $lng;
            $branch->working_hours = $input['branch_workingHours'];
            $branch->status = $input['branch_status'];

            if ($pricingWasChanged) { $branch->pricingfile = $newPricing; }

            $branch->save();
            // dd($branch);

            // delete old categories
            DB::table('branch_category')->where('branch_id', $branch->id)->delete();
             
            // set new categories
            $categoryIds = [];
            foreach ($input['branch_categoryIds'] as $key => $catId) 
            {
                $categoryIds[] = $catId;
            }

            $categories = Category::whereIn("id", $categoryIds)->get();
            foreach ($categories as $category) 
            {
                $success = DB::table('branch_category')->insert([
                    'branch_id' => $branch->id,
                    'category_id' => $category->id
                ]);
                // dd($success);
            }
            // dd(DB::table('branch_category')->where('branch_id', $branch->id)->get());
            
            // delete old phones
            Phone::where('branch_id', $branch->id)->delete(); 
            
            if (isset($input['branch_phones']))
            {
                foreach ($input['branch_phones'] as $phone)
                {
                    $phone = Phone::create([
                        'branch_id' => $branch->id,
                        'type' => $phone['type'],
                        'code_country' => $phone['code_country'],
                        'code_operator' => $phone['code_operator'],
                        'number' => $phone['number'],
                        'contact_person' => $phone['contact_person'],
                    ]);
                    // dd($phone);
                }
            }
            // dd(Phone::where('branch_id', $branch->id)->get()->toArray());

            // delete old socials
            Social::where('branch_id', $branch->id)->delete();

            if (isset($input['branch_socials']))
            {
                foreach ($input['branch_socials'] as $social)
                {
                    $social = Social::create([
                        'branch_id' => $branch->id,
                        'type' => $social['type'],
                        'name' => $social['name'],
                        'contact_person' => $social['contact_person'],
                    ]);
                    // dd($social);
                }
            }
            // dd(Social::where('branch_id', $branch->id)->get()->toArray());
             
            // tags
            $tagsInput = explode(",", $input['hidden-tags']);
            // dd($tagsInput);
            
            if (empty($tagsInput[0]))
            {
                $branch->untag();
            }
            else
            {
                $branch->retag($tagsInput);
            }
            // dd($branch->tagNames());         

            DB::commit();
        } 
        catch (Exception $e) 
        {
            DB::rollBack();
            flash()->error('Ошибка: ' . $e->getMessage());
            return redirect()->back()->withInput();
        } 
        
        flash()->success("Филиал успешно обновлен");
        return redirect()->action('OrganizationsController@edit', ['organizationId' => $branch->organization_id]);
    }

    public function editGallery(Request $request, $id)
    {
        try
        {
            $branch = Branch::findOrFail($id);
            $photos = Photo::where('branch_id', $branch->id)->get();

            JavaScript::put([
                'activeLink' => 'branches_editgallery',
            ]);

            return view('branches.admin.gallery', compact("branch", "photos"));
        }
        catch (Exception $e)
        {
            flash()->error("Ошибка: " . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try 
        {
            $branch = Branch::with(['phones', 'photos', 'socials'])->findOrFail($id);

            foreach ($branch->phones as $key => $phone) 
            {
                $phone->delete();
            }

            foreach ($branch->socials as $key => $social) 
            {
                $social->delete();
            }

            foreach ($branch->photos as $key => $photo) 
            {
                File::delete(public_path() . "/images/photos/" . $photo->path);                
                $photo->delete();
            }

            $branch->delete();

            flash()->info("Филиал удален");
            return redirect()->back();
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
