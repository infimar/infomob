<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Category;
use JavaScript;
use Flash;
use DB;

class CategoriesController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::roots()->get();

        JavaScript::put(['activeLink' => 'categories_index']);
        return view('categories.admin.index', compact("categories"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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

        JavaScript::put(['activeLink' => 'categories_create']);
        return view('categories.admin.create', compact("icons"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $name = $request->input("name");

        if (empty($name))
        {
            Flash::error('Ошибка: укажите название категории');
            return redirect()->back();
        }

        // check for slug
        $slug = $this->sluggify($name);
        $existingCategory = Category::where("slug", $slug)->first();
        
        if ($existingCategory !== null)
        {
            Flash::error('Ошибка: такая категория уже существует');
            return redirect()->back();
        }

        $category = Category::create([
            'name' => $name,
            'slug' => $this->sluggify($name),
            'status' => $input["status"],
            'icon' => $input["icon"]
        ]);

        $category->parent_id = null;
        $category->save();

        flash()->success("Категория добавлена");
        return redirect()->action('CategoriesController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try
        {
            $category = Category::findOrFail($id);

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

            JavaScript::put(['activeLink' => 'categories_edit']);
            return view('categories.admin.edit', compact("category", "icons"));
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
        $input = $request->all();
        $name = $request->input("name");

        if (empty($name))
        {
            Flash::error('Ошибка: укажите название категории');
            return redirect()->back();
        }

        try
        {
            $category = Category::findOrFail($id);

            // check for slug
            $slug = $this->sluggify($name);
            $existingCategory = Category::where("slug", $slug)->first();
            
            if ($existingCategory !== null && $existingCategory->id !== $category->id)
            {
                Flash::error('Ошибка: такая категория уже сущетсвует');
                return redirect()->back();
            }

            $category->name = $name;
            $category->slug = $slug;
            $category->status = $input['status'];
            $category->icon = $input['icon'];
            $category->save();

            flash()->success("Категория обновлена");
            return redirect()->action('CategoriesController@index');
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка обновления: ' . $e->getMessage());
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
            $category = Category::findOrFail($id);

            $children = $category->descendants()->limitDepth(1)->get();
            foreach ($children as $child)
            {
                $child->delete();
            }

            $category->delete();

            flash()->info("Категория удалена");
            return redirect()->back();
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function children($id)
    {
        try
        {
            $category = Category::findOrFail($id);
            $children = $category->descendants()->limitDepth(1)->get();
            $count = [];

            foreach ($children as $child)
            {
                $branchIds = DB::table('branch_category')->where('category_id', $child->id)->lists("branch_id");
                $count[$child->id] = count($branchIds);
            }

            JavaScript::put(['activeLink' => 'categories_children']);
            return view('categories.admin.children', compact("category", "children", "count"));
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка: категория не сущетсвует');
            return redirect()->back();
        }
    }

    public function createChild($parentId)
    {
        try
        {
            $category = Category::findOrFail($parentId);

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

            JavaScript::put(['activeLink' => 'categories_createchild']);
            return view('categories.admin.createchild', compact("category", "icons"));
        }
        catch (Exception $e)
        {
            flash()->error('Категории не сущетсвует: ' . $e->getMessage());
            return redirect()->action('CategoriesController@index');
        }        
    }

    public function storeChild(Request $request, $parentId)
    {
        $input = $request->all();
        $name = $request->input("name");

        if (empty($name))
        {
            Flash::error('Ошибка: укажите название подкатегории');
            return redirect()->back();
        }

        // check for slug
        $slug = $this->sluggify($name);
        $existingCategory = Category::where("slug", $slug)->first();
        
        if ($existingCategory !== null)
        {
            Flash::error('Ошибка: такая категория уже сущетсвует');
            return redirect()->back();
        }

        try
        {
            $category = Category::create([
                'name' => $name,
                'slug' => $this->sluggify($name),
                'status' => $input["status"],
                'icon' => $input["icon"]
            ]);

            $category->parent_id = $parentId;
            $category->save();

            flash()->success("Подкатегория добавлена");
            return redirect()->action('CategoriesController@children', ['id' => $parentId]);
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка создания подкатегории: ' . $e->getMessage());
            return redirect()->back();
        }   
    }

    public function editChild($id)
    {
        try
        {
            $category = Category::findOrFail($id);
            $parent = $category->parent()->first();

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

            JavaScript::put(['activeLink' => 'categories_editchild']);
            return view('categories.admin.editchild', compact("category", "parent", "icons"));
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка редактирования: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function updateChild(Request $request, $id)
    {
        $input = $request->all();
        $name = $request->input("name");

        if (empty($name))
        {
            Flash::error('Ошибка: укажите название подкатегории');
            return redirect()->back();
        }

        try
        {
            $category = Category::findOrFail($id);

            // check for slug
            $slug = $this->sluggify($name);
            $existingCategory = Category::where("slug", $slug)->first();
            
            if ($existingCategory !== null && $existingCategory->id !== $category->id)
            {
                Flash::error('Ошибка: такая подкатегория уже сущетсвует');
                return redirect()->back();
            }

            $category->name = $name;
            $category->slug = $slug;
            $category->status = $input['status'];
            $category->icon = $input['icon'];
            $category->save();

            flash()->success("Подкатегория обновлена");
            return redirect()->action('CategoriesController@children', ['id' => $category->parent_id]);
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка обновления: ' . $e->getMessage());
            return redirect()->back();
        }

        // check for slug
        $slug = $this->sluggify($name);
        $existingCategory = Category::where("slug", $slug)->first();
        
        if ($existingCategory !== null)
        {
            Flash::error('Ошибка: такая категория уже сущетсвует');
            return redirect()->back();
        }
    }

    public function destroyChild(Request $request, $id)
    {
        try 
        {
            $category = Category::findOrFail($id);
            $category->delete();

            flash()->info("Категория удалена");
            return redirect()->back();
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    private function sluggify($string, $gost = false)
    {
        if ($gost)
        {
            $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
                "Е"=>"E","е"=>"e","Ё"=>"E","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
                "Й"=>"I","й"=>"i","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n","О"=>"O","о"=>"o",
                "П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t","У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f",
                "Х"=>"Kh","х"=>"kh","Ц"=>"Tc","ц"=>"tc","Ч"=>"Ch","ч"=>"ch","Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch",
                "Ы"=>"Y","ы"=>"y","Э"=>"E","э"=>"e","Ю"=>"Iu","ю"=>"iu","Я"=>"Ia","я"=>"ia","ъ"=>"","ь"=>"");
        }
        else
        {
            $arStrES = array("ае","уе","ое","ые","ие","эе","яе","юе","ёе","ее","ье","ъе","ый","ий");
            $arStrOS = array("аё","уё","оё","ыё","иё","эё","яё","юё","ёё","её","ьё","ъё","ый","ий");        
            $arStrRS = array("а$","у$","о$","ы$","и$","э$","я$","ю$","ё$","е$","ь$","ъ$","@","@");
                        
            $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
                "Е"=>"Ye","е"=>"e","Ё"=>"Ye","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
                "Й"=>"Y","й"=>"y","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n",
                "О"=>"O","о"=>"o","П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t",
                "У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f","Х"=>"Kh","х"=>"kh","Ц"=>"Ts","ц"=>"ts","Ч"=>"Ch","ч"=>"ch",
                "Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch","Ъ"=>"","ъ"=>"","Ы"=>"Y","ы"=>"y","Ь"=>"","ь"=>"",
                "Э"=>"E","э"=>"e","Ю"=>"Yu","ю"=>"yu","Я"=>"Ya","я"=>"ya","@"=>"y","$"=>"ye");
                    
            $string = str_replace($arStrES, $arStrRS, $string);
            $string = str_replace($arStrOS, $arStrRS, $string);
        }
        
        $translated = iconv("UTF-8","UTF-8//IGNORE", strtr($string,$replace));
        $translated = strtolower($translated);
        $translated = str_replace(" ", "-", $translated);
        return $translated;
    }
}
