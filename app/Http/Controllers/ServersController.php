<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class ServersController
 * @package App\Http\Controllers
 */
class ServersController extends Controller
{
    /**
     * Create a new controller instance.
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
        return view('servers');
    }

    /**
     * Adds a new server
     *
     * @return Response
     */
    public function add()
    {
        if (rand(1, 10) == 2) {
            return response()->json(['status' => 'success'], 200);
        } else {
            return response()->json(['status' => 'fail'], 422);
        }
    }
}
