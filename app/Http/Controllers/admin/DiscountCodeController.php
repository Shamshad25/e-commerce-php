<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCodeController extends Controller
{
    public function index(){

    }

    public function create(){

        return view('admin.coupon.create');

    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->passes()){

            // Starting date must be greated than current date
            if(!empty($request->starts_at)){
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                // let = less than equal to
                if($startAt->lte($now) == true){
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Start date cannot be less than current time.'],
                    ]);
                }
            }


            // Expiry date must be greated than start date
            if(!empty($request->starts_at) && !empty($request->expires_at)){
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                // let = less than equal to
                if($expiresAt->gt($startAt) == false){
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expiry date must be greater than start date.'],
                    ]);
                }
            }

            $discountCode = new DiscountCoupon();
            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->type = $request->type;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->status = $request->status;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            session()->flash('success','Discount coupon added successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Discount coupon added successfully.',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function update(){

    }

    public function edit(){

    }

    public function destroy(){

    }
}
