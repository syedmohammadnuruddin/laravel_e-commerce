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
        ->latest('sub_categories.id')
        ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

        if(!empty($request->keyword)){
            $subCategories = $subCategories->where('sub_categories.name', 'like', '%' .$request->get('keyword') .'%');
            $subCategories = $subCategories->orWhere('categories.name', 'like', '%' .$request->get('keyword') .'%');
        }
        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.list', compact('subCategories'));
    }
    public function create(){
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('admin.sub_category.create', compact('categories'));
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category'=> 'required',
            'status' => 'required'

        ]);

        if($validator->passes()){
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success', 'Subcategory Added Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Subcategory Added Successfully'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit(Request $request, $id){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error', 'Record not found');
            return redirect()->route('sub-categories.index');
        }
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('admin.sub_category.edit', compact('subCategory','categories'));
    }
    public function update(Request $request, $id){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error', 'Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'category'=> 'required',
            'status' => 'required'

        ]);

        if($validator->passes()){
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success', 'Subcategory Updated Successfully');

            return response([
                'status' => true,
                'message' => 'Subcategory Updated Successfully'
            ]);
        }else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function destroy($id){
        $subCategory = SubCategory::find($id);
    
        $subCategory->delete();

        return redirect()->back()->with('success','Sub Category deleted successfully');


    }
}
