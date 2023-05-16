<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesforceAccountConnectRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Home/Index');
    }

    public function connectSFDCAccount(SalesforceAccountConnectRequest $salesforceAccountConnectRequest)
    {

    }
}
