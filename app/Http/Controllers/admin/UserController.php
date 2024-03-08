<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request){

        $users = User::latest();

        if(!empty($request->get('keyword'))){
            $users = $users->where('name', 'like', '%'.$request->get('keyword').'%');
            $users = $users->orWhere('email', 'like', '%'.$request->get('keyword').'%');
        }

        $users = $users->paginate(10);

        return view('admin.users.list',[
            'users' => $users
        ]);
    }

    public function create(Request $request){
        return view('admin.users.create');
    }

}
