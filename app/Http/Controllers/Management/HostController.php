<?php

/*
 * Taken from
 * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Auth/Console/stubs/make/controllers/HomeController.stub
 */

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Request\ParamRequest;
use App\Http\Requests;
use App\Keychest\Utils\DomainTools;
use App\Rules\HostSpecObjRule;
use Illuminate\Support\Facades\Input;


/**
 * Class HostController
 * @package App\Http\Controllers\Management
 */
class HostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @param ParamRequest $request
     * @return Response
     */
    public function addHost(ParamRequest $request)
    {
        $hostSpecStr = Input::get('host_addr');

        $hostSpec = DomainTools::tryHostSpecParse($hostSpecStr);
        $request->addExtraParam('host_spec_obj', $hostSpec);

        $this->validate($request, [
            'host_addr' => 'required',
            'host_spec_obj' => new HostSpecObjRule()
        ], [], [
            'host_addr' => 'Host Address',
            'host_spec_obj' => 'Host Address']
        );

        return response()->json([
                'state' => 'success'
            ], 200);
    }



}