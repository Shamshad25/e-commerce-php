<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart()
    {

    }

    public function cart()
    {
        return view('front.cart');
    }
}
