<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'password' => 'required|min:5',
            'email' => 'required|email|unique:users',
            'phone' => 'required'
        ]);

        if($validator->passes()){

            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->save();

            session()->flash('success', 'User created successfully.');

            return response()->json([
                'status' => true,
                'message' => 'User created successfully.',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit(Request $request, $id){

        $user = User::find($id);

        if($user == null){
            session()->flash('error', 'User not found.');
            return redirect()->route('users.index');
        }

        return view('admin.users.edit',[
            'user' => $user
        ]);
    }

}
