<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function create()
    {
        $countries = Country::get();

        $data['countries'] = $countries;

        return view('admin.shipping.create', $data);
    }
}
