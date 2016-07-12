<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class AdminController extends InfomobController
{
    public function __construct(Request $request)
    {
    	parent::__construct($request);
    	$this->middleware('auth');

    	$this->initFilter();
    }

    public function index()
    {
    	return view('layouts.admin.index');
    }

    public function initFilter()
    {

    }
}
