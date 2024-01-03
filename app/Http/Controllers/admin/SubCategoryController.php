<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{

    public function index(Request $request){
        $subCategories = SubCategory::select('sub_categories.*','categories.name as categoryName')
        ->latest('sub_categories.id')->
        leftJoin('categories','categories.id','sub_categories.category_id');

        // Search function
        if (!empty($request->get('keyword'))){
            $subCategories = $subCategories->where('sub_categories.name','like','%'.$request->get('keyword').'%');
            $subCategories = $subCategories->orWhere('categories.name','like','%'.$request->get('keyword').'%');
        }

        $subCategories = $subCategories->paginate(10);
        // ONE WAY
        // $data['categories'] = $categories;
        // return view('admin.category.list',$data);

        // SECOND WAY
        return view('admin.sub_category.list',compact('subCategories'));
    }

    public function create() {
        $categories = Category::orderBy('name','ASC')->get();
        return view('admin.sub_category.create',compact('categories'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if($validator->passes()){
            $subCategory = new SubCategory;
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success', 'Sub Category created successfully.');

            return response([
                'status' => true,
                'message' => 'Sub Category created successfully.'
            ]);

        }else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
