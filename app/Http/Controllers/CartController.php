<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Carbon\Carbon;
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

        $discount = 0;

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

        $subTotal = Cart::subtotal(2,'.','');

        //Apply Discount here
        if(session()->has('code')){
            $code = session()->get('code');

            if($code->type == 'percent'){
                $discount = ($code->discount_amount/100)*$subTotal;
            }else{
                $discount = $code->discount_amount;
            }
        }

        // Calculate Shipping here
        if($customerAddress != ''){
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id',$userCountry)->first();

            // dd($shippingInfo);

            $totalQty = 0;
            $totalShippingCharge = 0;
            $grandTotal = 0;
            foreach(Cart::content() as $item){
                $totalQty += $item->qty;
            }

            if($shippingInfo == null ){
                $shippingInfo =  ShippingCharge::where('country_id', 243)->first();
                $totalShippingCharge = $totalQty*$shippingInfo->amount;
            }


            $totalShippingCharge = $totalQty * $shippingInfo->amount;
            $grandTotal = ($subTotal-$discount)+$totalShippingCharge;

        }else{
            $grandTotal = ($subTotal-$discount);
            $totalShippingCharge = 0;
        }

        return view('front.checkout',[
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'discount'=> $discount,
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

            $discountCodeId = NULL;
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2,'.','');
            $grandTotal = $subTotal+$shipping;

            //Apply Discount here
            if(session()->has('code')){
                $code = session()->get('code');

                if($code->type == 'percent'){
                    $discount = ($code->discount_amount/100)*$subTotal;
                }else{
                    $discount = $code->discount_amount;
                }

                $discountCodeId = $code->id;
                $promoCode = $code->code;
            }


            // Calculate Shipping
            $shippingInfo =  ShippingCharge::where('country_id', $request->country)->first();

            $totalQty = 0;
            foreach(Cart::content() as $item){
                $totalQty += $item->qty;
            }

            if($shippingInfo != null){
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shipping;

            }else{
                $shippingInfo =  ShippingCharge::where('country_id', 243)->first();
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shipping;
            }


            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->discount = $discount;
            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code = $promoCode;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
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

            // Update product stock
            $productData = Product::find($item->id);
            if($productData->track_qty == 'Yes'){
                $currentQty = $productData->qty;
                $updatedQty = $currentQty - $item->qty;
                $productData->qty = $updatedQty;
                $productData->save();
            }
        }



        // Send Order Email
        orderEmail($order->id, 'customer');

        session()->flash('success', 'You have successfully placed your order.');

        Cart::destroy();

        session()->forget('code');

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

    public function getOrderSummery(Request $request){

        $subTotal = Cart::subtotal(2,'.','');
        $discount = 0;
        $discountString = '';

        //Apply Discount here
        if(session()->has('code')){
            $code = session()->get('code');

            if($code->type == 'percent'){
                $discount = ($code->discount_amount/100)*$subTotal;
            }else{
                $discount = $code->discount_amount;
            }

            $discountString = '<div class="mt-4" id="discount-response"><strong>'.session()->get('code')->code.' </strong><a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a></div>';
        }


        if($request->country_id > 0){

            $shippingInfo =  ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;
            foreach(Cart::content() as $item){
                $totalQty += $item->qty;
            }

            if($shippingInfo != null){

                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => number_format($discount,2),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge,2)
                ]);

            }else{
                $shippingInfo =  ShippingCharge::where('country_id', 243)->first();
                // dd($shippingInfo);
                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => number_format($discount,2),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge,2)
                ]);
            }

        }else{

            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal-$discount),2),
                'discount' => number_format($discount,2),
                'discountString' => $discountString,
                'shippingCharge' => number_format(0,2)
            ]);

        }
    }

    public function applyDiscount(Request $request){
        // dd($request->code);

        $code = DiscountCoupon::where('code', $request->code)->first();

        if($code == null){
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon.',
            ]);
        }


        // Check if coupon start date is valid or not

        $now = Carbon::now();

        // echo $now->format('Y-m-d H:i:s');

        if($code->starts_at != ""){
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s',$code->starts_at);

            if($now->lt($startDate)){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon.',
                ]);
            }
        }

        if($code->expires_at != ""){
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s',$code->expires_at);

            if($now->gt($endDate)){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon.',
                ]);
            }
        }

        // Max Uses Check

        if($code->max_uses > 0){
            $couponUsed = Order::where('coupon_code_id',$code->id)->count();

            if($couponUsed >= $code->max_uses){
                return response()->json([
                    'status' => false,
                    'message' => 'This coupon code cannot be used again.',
                ]);
            }
        }




        // Max Uses User Check

        if($code->max_uses_user > 0){
            $couponUsedByUser = Order::where(['coupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();

            if($couponUsedByUser >= $code->max_uses_user){
                return response()->json([
                    'status' => false,
                    'message' => 'The coupon code have already been used.',
                ]);
            }
        }


        $subTotal = Cart::subtotal(2,'.','');

        // Min amount condition check
        if($code->min_amount > 0){
            if($subTotal < $code->min_amount){
                return response()->json([
                    'status' => false,
                    'message' => 'Your min amount must be $'.$code->min_amount.'.',
                ]);
            }
        }


        session()->put('code', $code);

        return $this->getOrderSummery($request);

    }

    public function removeCoupon(Request $request){

        session()->forget('code');

        return $this->getOrderSummery($request);

    }
}
