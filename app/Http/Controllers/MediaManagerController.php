<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Photo;
use App\Branch;
use JavaScript;
use File;

class MediaManagerController extends InfomobController
{
    public function index()
    {
    	$icons = [];
    	$path = public_path() . "/images/icons/";
    	$files = array_diff(scandir($path), array('.', '..'));

    	foreach ($files as $file)
    	{
    		$ext = pathinfo($path . $file, PATHINFO_EXTENSION);
    		// dd($ext);

    		if (in_array($ext, ["png"]))
    		{
    			$icons[] = $file;
    		}
    	}

    	JavaScript::put(['activeLink' => 'mediamanager_index']);
    	return view('mediamanager.admin.index', compact("icons"));
    }

    public function uploadIcon(Request $request)
    {
    	$file = $request->file('file');

    	$destinationPath = public_path() . "/images/icons/";
    	$filename = uniqid() . ".png";

    	$file->move($destinationPath, $filename);

    	return response()->json(["path" => $destinationPath . $filename]);
    }

    public function deleteIcon(Request $request)
    {
    	File::delete($request->input('path'));
    	return response()->json(["code" => 200]);
    }

    public function uploadPhoto(Request $request)
    {
        $file = $request->file('file');
        $branchId = $request->input('branch_id');

        $destinationPath = public_path() . "/images/photos/";
        $filename = uniqid() . "." . $file->getClientOriginalExtension();
        $file->move($destinationPath, $filename);

        $photo = Photo::create([
            'branch_id' => $branchId,
            'type' => 'picture',
            'path' => $filename
        ]);

        return response()->json(["photo" => $photo]);
    }

    public function deletePhoto(Request $request)
    {
        $input = $request->input("photo");

        $photo = Photo::findOrFail($input['id']);
        $destinationPath = public_path() . "/images/photos/";

        File::delete($destinationPath . $photo->path);
        $photo->delete();

        return response()->json(["code" => 200]);
    }

    public function deletePhotoById(Request $request)
    {
        $id = $request->input("id");

        $photo = Photo::findOrFail($id);
        $destinationPath = public_path() . "/images/photos/";

        File::delete($destinationPath . $photo->path);
        $photo->delete();

        return response()->json(["code" => 200]);
    }

    public function updatePhoto(Request $request)
    {
        $id = $request->input("id");

        $photo = Photo::findOrFail($id);
        $photo->description = $request->input("description");
        $photo->save();

        return response()->json(["code" => 200]);
    }
}
