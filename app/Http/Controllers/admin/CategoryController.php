<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();

        // Search function
        if (!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
        }

        $categories = $categories->paginate(10);
        // ONE WAY
        // $data['categories'] = $categories;
        // return view('admin.category.list',$data);

        // SECOND WAY
        return view('admin.category.list',compact('categories'));
    }

    public function create(){
        return view('admin.category.create');
    }

    public function edit(){

    }

    public function store(Request $request){

        // Validation Form Backend
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);



        if($validator-> passes()){

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            // Save Image
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->flash('success', 'Category added successfully');

            // return redirect()->route('categories.index')->with('success', 'Category added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully'
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function update(){

    }

    public function destroy(){

    }
}
