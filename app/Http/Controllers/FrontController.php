<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index(){

        $featuredProducts = Product::where('is_featured','Yes')->orderBy('id','DESC')->where('status',1)->take(8)->get();
        $data['featuredProducts'] = $featuredProducts;


        $latestProducts = Product::orderBy('id','DESC')->where('status',1)->take(8)->get();
        $data['latestProducts'] = $latestProducts;

        return view('front.home',$data);
    }
}
