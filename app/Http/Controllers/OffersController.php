<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use JavaScript;
use App\Branch;
use App\Offer;
use App\City;
use Flash;
use File;
use Table;
use DB;
use Exception;
use Carbon\Carbon;

class OffersController extends AdminController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        // cities for select2 dropdown
        $cities = [];
        foreach(City::correct()->orderBy('order')->get(['id', 'name']) as $city)
        {
            $cities[] = ['id' => $city->id, 'text' => $city->name];
        }

        JavaScript::put(['cities' => $cities]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $today = Carbon::now();

        $query = Offer::with(['organization', 'cities' => function($q) { $q->orderBy('order'); }])
            ->where('date_end', '>=', $today->format('Y-m-d'));

        // sort
        if ($request->has('sort')) 
            $query->sorted();
        else 
            $query->sorted('date_start', 'ASC');
            
        $offers = $query->paginate();
        $table = Table::create($offers, ['organization_id', 'cities', 'date_start', 'date_end']);

        // prepare index table
        foreach ($table->getColumns() as $column) {
            // $model = $column->getModel();

            switch ($column->getField()) 
            {
                case 'organization_id':
                    $column->setLabel('Организация');
                    $column->setRenderer(function($model) {
                        return '<a href="' . route('admin.offers.edit', ['id' => $model->id]) . '">' . $model->organization_id . '</a>';
                    });
                    break;
                
                case 'cities':
                    $column->setLabel('Город(-а)');
                    $column->setRenderer(function($model) {
                        return $model->cities->implode('name', ', ');
                    });
                    break;

                case 'date_start':
                    $column->setLabel('Дата начала');
                    $column->setRenderer(function($model) {
                        return $model->date_start->formatLocalized('%d %B %Y');
                    });
                    break;

                case 'date_end':
                    $column->setLabel('Дата завершения');
                    $column->setRenderer(function($model) {
                        return $model->date_end->formatLocalized('%d %B %Y');
                    });
                    break;

                default:
                    # code...
                    break;
            }
        }

        // delete btns
        $table->addColumn('action', '', function($model) {
            return '<form action="' . route('admin.offers.destroy', ['id' => $model->id]) . '" method="POST">' . csrf_field() . method_field('DELETE') . '<input type="submit" class="sure btn btn-small btn-danger" value="Удалить"></form>';
        });

        JavaScript::put(['activeLink' => 'offers_index']);
        return view('offers.admin.index', compact("offers", 'table'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        JavaScript::put(['activeLink' => 'offers_create']);
        return view('offers.admin.create');
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
        // dd($input);

        // TODO: validation
        // TODO: date start and end validation!

        // upload image
        $image = $request->file('image');
        if (!$image || !$image->isValid()) {
            Flash::error('No image');
            return redirect()->back();
        }

        $destinationPath = public_path() . '/images/offers';
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move($destinationPath, $filename);

        // save new offer
        DB::beginTransaction();

        // get primary branch
        $branch = Branch::where('organization_id', $input['organization_id'])
            ->where('type', 'main')
            ->orderBy('id', 'ASC')->first();

        $offer = Offer::create([
            'organization_id' => $input['organization_id'],
            'branch_id' => $branch->id,
            'image' => $filename,
            'description' => $input['description'],
            'type' => $input['type'],
            'no_time' => $input['no_time'],
            'date_start' => $input['date_start'],
            'date_end' => $input['date_end']
        ]);

        // attach cities
        foreach ($input['cities'] as $cityId) 
        {
            $offer->cities()->attach($cityId);
        }

        DB::commit();

        Flash::success('Новая акция успешно добавлена');
        return redirect()->action('OffersController@index');
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
            $offer = Offer::with('organization', 'cities')->findOrFail($id);
            
            // prepare form data in select2 format
            $cities = [];
            foreach ($offer->cities as $city) { $cities[] = [$city->id]; }

            JavaScript::put([
                'organization' => ['id' => $offer->organization->id, 'name' => $offer->organization->name],
                'chosenCities' => $cities,
                'activeLink' => 'admin.offers.edit',
            ]);

            return view('offers.admin.edit', compact('offer'));
        }
        catch (Exception $e)
        {
            Flash::error('Акция не найдена');
            return redirect()->action('OffersController@index');
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
        // dd($input);

        try
        {
            $offer = Offer::with('cities')->findOrFail($id);

            // TODO: validation
            
            // image is changed?
            $imageWasChanged = false;
            $newImage = null;
            $image = $request->file('image');

            if ($image && $image->isValid()) 
            {   
                // remove old
                $oldFile = public_path() . '/images/offers/' . $offer->image;
                if (File::exists($oldFile)) { File::delete($oldFile); }

                // upload new
                $destinationPath = public_path() . '/images/offers';
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $filename);

                $imageWasChanged = true;
                $newImage = $filename;
            }

            // update offer
            DB::beginTransaction();

            $offer->organization_id = $input['organization_id'];
            $offer->description = $input['description'];
            $offer->date_start = $input['date_start'];
            $offer->date_end = $input['date_end'];
            $offer->type = $input['type'];
            $offer->no_time = $input['no_time'];

            if ($imageWasChanged) { $offer->image = $newImage; }
            
            $offer->save();

            // cities
            $offer->cities()->detach();
            foreach ($input['cities'] as $cityId)
            {
                $offer->cities()->attach($cityId);
            }
             
            // commit changes
            DB::commit();

            Flash::success('Акция успешно изменена');
            return redirect()->action('OffersController@index');
        }
        catch (Exception $e)
        {
            Flash::error('Акция не найдена: ' . $e->getMessage());
            return redirect()->action('OffersController@index');
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
            $offer = Offer::with('cities')->findOrFail($id);
            $offer->cities()->detach();
            $offer->delete();

            $image = public_path() . '/images/offers/' . $offer->image;
            File::delete($image);

            Flash::info('Акция удалена');
            return redirect()->action('OffersController@index');
        }
        catch (Exception $e)
        {
            Flash::error('Акция не найдена: ' . $e->getMessage());
            return redirect()->action('OffersController@index');
        }
    }
}
