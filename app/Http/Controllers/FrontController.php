<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FrontController extends Controller
{
    public function index(){

        $featuredProducts = Product::where('is_featured','Yes')->orderBy('id','DESC')->where('status',1)->take(8)->get();
        $data['featuredProducts'] = $featuredProducts;


        $latestProducts = Product::orderBy('id','DESC')->where('status',1)->take(8)->get();
        $data['latestProducts'] = $latestProducts;

        return view('front.home',$data);
    }

    public function addToWishlist(Request $request){
        if(Auth::check() == false){

            session(['url.intended' => url()->previous()]);

            return response()->json([
                'status' => false,

            ]);
        }

        $product = Product::where('id', $request->id)->first();

        if($product == null){
            return response()->json([
                'status' => true,
                'message' => '<div class="alert alert-danger">Product not found.</div>'
            ]);
        }

        Wishlist::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->id
            ],
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->id
            ]
        );

        // $wishlist = new Wishlist();
        // $wishlist->user_id = Auth::user()->id;
        // $wishlist->product_id = $request->id;
        // $wishlist->save();


        return response()->json([
            'status' => true,
            'message' => '<div class="alert alert-success"><string>"'.$product->title.'"</string> added to your wishlist.</div>'
        ]);

    }

    public function page($slug){
        $page = Page::where('slug', $slug)->first();

        if($page == null){
            abort(404);
        }
        // dd($page);

        return view('front.page',[
            'page' => $page
        ]);
    }

    public function sendContactEmail(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required|min:10',
        ]);

        if($validator->passes()){

            // Send email here

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

}
