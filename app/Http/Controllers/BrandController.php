<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(Request $request){
        $brands = Brand::latest('id');

        if(!empty($request->keyword)){
            $brands = $brands->where('name', 'like', '%' .$request->get('keyword') .'%');
        }
        $brands = $brands->paginate(10);
        return view('admin.brands.list', compact('brands'));
    }
    public function create(){
        return view('admin.brands.create');
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands'
        ]); 
    
        if ($validator->passes()) {
            $brands = new Brand();
            $brands->name = $request->name;
            $brands->slug = $request->slug;
            $brands->status = $request->status;
            $brands->save();

            $request->session()->flash('success', 'Brands Added Successfully');
    
            return response()->json([
                'status' => true,
                'message' => 'Brands Added Successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    
}
