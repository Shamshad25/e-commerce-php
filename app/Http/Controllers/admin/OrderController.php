<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request){


        $orders = Order::latest('orders.created_at')->select('orders.*','users.name', 'users.email');

        $orders = $orders->leftJoin('users', 'users.id', 'orders.user_id');


        // FOR SEARCHING
        if($request->get('keyword') != ""){
            $orders = $orders->where('users.name', 'like', '%'.$request->keyword.'%');
            $orders = $orders->orWhere('users.email', 'like', '%'.$request->keyword.'%');
            $orders = $orders->orWhere('orders.id', 'like', '%'.$request->keyword.'%');
        }

        $orders = $orders->paginate(10);

        return view('admin.orders.list',[
            'orders' => $orders
        ]);
    }

    public function derail(){

    }
}
