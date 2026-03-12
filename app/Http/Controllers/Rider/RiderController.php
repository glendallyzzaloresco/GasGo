<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    public function dashboard()
    {
        return view('rider.dashboard');
    }

    public function delivery()
    {
        return view('rider.delivery');
    }

    public function history()
    {
        return view('rider.history');
    }

    public function profile()
    {
        return view('rider.profile');
    }
}
