<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Subscription;
use App\Branch;
use Table;
use JavaScript;
use Carbon\Carbon;
use Exception;
use Flash;

class SubscriptionsController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Subscription::with('organization');

        // sort
        if ($request->has('sort')) 
            $query->sorted();
        else 
            $query->sorted('id', 'DESC');
            
        $subscriptions = $query->paginate();
        $table = Table::create($subscriptions, ['organization_id', 'type', 'expires_in']);

        // prepare index table
        foreach ($table->getColumns() as $column) {
            // $model = $column->getModel();

            switch ($column->getField()) 
            {
                case 'organization_id':
                    $column->setLabel('Организация');
                    $column->setRenderer(function($model) {
                        return '<a href="' . route('admin.subscriptions.edit', ['id' => $model->id]) . '">' . $model->organization->name . '</a>';
                    });
                    break;
                
                case 'type':
                    $column->setLabel('Тип');
                    $column->setRenderer(function($model) {
                        return Subscription::types($model->type);
                    });
                    break;

                case 'expires_in':
                    $column->setLabel('Осталось дней');
                    $column->setRenderer(function($model) {
                        $cDate = Carbon::parse($model->expires_in);
                        return $cDate->diffInDays();
                    });
                    break;

                default:
                    # code...
                    break;
            }
        }

        // delete btns
        $table->addColumn('action', '', function($model) {
            return '<form action="' . route('admin.subscriptions.destroy', ['id' => $model->id]) . '" method="POST">' . csrf_field() . method_field('DELETE') . '<input type="submit" class="sure btn btn-small btn-danger" value="Удалить"></form>';
        });

        JavaScript::put(['activeLink' => 'subscriptions_index']);
        return view('subscriptions.admin.index', compact("subscriptions", 'table'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $date = Carbon::now();
        $thisYear = $date->year;

        JavaScript::put(['activeLink' => 'subscriptions_create']);
        return view('subscriptions.admin.create', compact('thisYear'));
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
        $nextYear = new Carbon('next year');

        try
        {
            $subscription = Subscription::create([
                'organization_id' => $input['organization_id'],
                'type' => $input['type'],
                'year' => $input['year'],
                'expires_in' => $nextYear
            ]);

            // update branches
            Branch::where('organization_id', $input['organization_id'])->update([
                'subscription' => $input['type']
            ]);

            Flash::success('Новая подписка успешно создана');
            return redirect()->action('SubscriptionsController@index');
        }
        catch (Exception $e)
        {
            Flash::error("Error: " . $e->getMessage());
            return redirect()->action('SubscriptionsController@index');
        }
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
            $subscription = Subscription::with('organization')->findOrFail($id);

            JavaScript::put(['activeLink' => 'subscriptions_edit']);
            return view('subscriptions.admin.edit', compact('subscription'));
        }
        catch (Exception $e)
        {
            Flash::error("Error: " . $e->getMessage());
            return redirect()->action('SubscriptionsController@index');
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

        try
        {
            $subscription = Subscription::findOrFail($id);

            $subscription->organization_id = $input['organization_id'];
            $subscription->type = $input['type'];
            $subscription->year = $input['year'];
            $subscription->save();

            // update branches
            Branch::where('organization_id', $input['organization_id'])->update([
                'subscription' => $input['type']
            ]);


            Flash::success('Подписка успешно обновлена');
            return redirect()->action('SubscriptionsController@index');
        }
        catch (Exception $e)
        {
            Flash::error("Error: " . $e->getMessage());
            return redirect()->action('SubscriptionsController@index');
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
            $subscription = Subscription::findOrFail($id);

            // update branches
            Branch::where('organization_id', $subscription->organization_id)->update([
                'subscription' => 'none'
            ]);

            // delete subscription
            $subscription->delete();

            Flash::info('Подписка удалена');
            return redirect()->action('SubscriptionsController@index');
        } 
        catch (Exception $e) 
        {
            Flash::error("Error: " . $e->getMessage());
            return redirect()->action('SubscriptionsController@index');
        }
    }
}
