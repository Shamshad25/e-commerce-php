<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);

        if($product == null){
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }

        if(Cart::count() > 0){
            // echo 'Product already in cart.';
            // Product already in cart
            // Check if product already exists in cart
            // Return a message that product already exixts in cart
            // If product does not exist in cart, then add to cart

            $cartContent = Cart::content();
            $productAlreadyExists = false;

            foreach($cartContent as $item){
                if($item->id == $product->id){
                $productAlreadyExists = true;
                }
            }

            if($productAlreadyExists == false){
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
                session()->flash('success', $message);

            }else{
                $status = false;
                $message = $product->title.' already exists in cart.';
            }



        }else{
            // Cart is empty
        Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';

            session()->flash('success', $message);

    }

    return response()->json([
        'status' => $status,
        'message' => $message
    ]);
        // Cart::add('293ad', 'Product 1', 1, 9.99);
    }

    public function cart()
    {
        $cartContent = Cart::content();

        $data['cartContent'] = $cartContent;
        // dd(Cart::content());
        return view('front.cart', $data);
    }

    public function updateCart(Request $request){
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);
        // Check quantity available in stock

        if($product->track_qty == 'Yes'){
            if( $qty <= $product->qty){
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            }else{
                $message = 'Requested qty('.$qty.') not available in stock.';
                $status = false;
                session()->flash('error', $message);

            }
        }else{
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);

        }

        // Cart::update($rowId, $qty);

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request){

        $itemInfo = Cart::get($request->rowId);

        if($itemInfo == null){
            session()->flash('error', 'Item not found in cart.');

            return response()->json([
                'status' => false,
                'message' => 'Item not found in cart.'
            ]);
        }

        Cart::remove($request->rowId);

        session()->flash('success', 'Item removed from cart successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Item removed from cart successfully.'
            ]);
    }

    public function checkout(){

        // If cart is empty redirect to cart
        if(Cart::count() == 0){
            return redirect()->route('front.cart');
        }

        // If user is not logged in then redirect to login page
        if(Auth::check() == false){
            if(!session()->has('url.intended')){
                session(['url.intended' => url()->current()]);
            }
            
            return redirect()->route('account.login');
        }

        return view('front.checkout');
    }
}
