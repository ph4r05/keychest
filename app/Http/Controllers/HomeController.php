<?php

/*
 * Taken from
 * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Auth/Console/stubs/make/controllers/HomeController.stub
 */

namespace App\Http\Controllers;

use App\Http\Requests;


/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
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
     * @return Response
     */
    public function index()
    {
        if (session('start_watching')){
            return redirect(route('servers'));
        }

        return view('adminlte::home');
    }

    /**
     * Dashboard CFO
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboardCfo(){
        return view('adminlte::dashboard_cfo');
    }

    /**
     * Dashboard CIO
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboardCio(){
        return view('adminlte::dashboard_cio');
    }

    /**
     * Dashboard CISO
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboardCiso(){
        return view('adminlte::dashboard_ciso');
    }

    /**
     * Dashboard Management
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboardManagement(){
        return view('adminlte::dashboard_management');
    }

    /**
     * Simple user guide view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userGuide(){
        return view('userguide');
    }

    /**
     * Simple API doc view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function apiDoc(){
        return view('apidoc');
    }

    /**
     * Simple enterprise view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function enterprise(){
        return view('enterprise');
    }

}