<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $customerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();

        session()->forget('url.intended');

        $countries = Country::orderBy('name','ASC')->get();

        // Calculate Shipping here
        $userCountry = $customerAddress->country_id;
        $shippingInfo = ShippingCharge::where('country_id',$userCountry)->first();

        // dd($shippingInfo);

        $totalQty = 0;
        $totalShippingCharge = 0;
        $grandTotal = 0;
        foreach(Cart::content() as $item){
            $totalQty += $item->qty;
        }

        $totalShippingCharge = $totalQty * $shippingInfo->amount;

        $grandTotal = Cart::subtotal(2,'.','')+$totalShippingCharge;

        return view('front.checkout',[
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'grandTotal' => $grandTotal
        ]);
    }

    public function processCheckout(Request $request){
        // Step 1 Validation

        $validator = Validator::make($request->all(),[
            'first_name' => 'required|min:5',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:30',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'please fix the errors',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // dd($request->first_name);

        // Step 2 save user address
        // $customerAddress = CustomerAddress::find();

        $user = Auth::user()->id;
        CustomerAddress::updateOrCreate(

            ['user_id' => $user],
            [
                'user_id' => $user,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'appartment' => $request->appartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        // Step 3 Store data in orders table

        if($request->payment_method == 'cod'){

            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2,'.','');
            $grandTotal = $subTotal+$shipping;

            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->user_id = $user;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->address = $request->address;
            $order->appartment = $request->appartment;
            $order->state = $request->state;
            $order->city = $request->city;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->country_id = $request->country;
            $order->save();

        // Step 4 Store order items in order item table

        foreach (Cart::content() as $item){
            $orderItem = new OrderItem;
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->name = $item->name;
            $orderItem->qty = $item->qty;
            $orderItem->price = $item->price;
            $orderItem->total = $item->price*$item->qty;
            $orderItem->save();
        }

        session()->flash('success', 'You have successfully placed your order.');

        Cart::destroy();

        return response()->json([
            'message' => 'Orders saved successfully',
            'orderId' =>  $order->id,
            'status' => true,
        ]);


        }else{
            //
        }

    }

    public function thankyou($id){
        return view('front.thanks',[
            'id' => $id
        ]);
    }

    public function getOrderSummery(){
        
    }
}
