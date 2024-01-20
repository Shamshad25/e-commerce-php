<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

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
                $message = $product->title.' added in cart.';
            }else{
                $status = false;
                $message = $product->title.' already exists in cart.';
            }



        }else{
            // Cart is empty
        Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message = $product->title.' added in cart.';
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
        Cart::update($rowId, $qty);

        session()->flash('success', 'Cart updated successfully');
        return response()->json([
            'status' => true,
            'message' => 'Cart updated successfully.'
        ]);
    }
}
