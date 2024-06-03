<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index(){
        $categories = Category::orderBy('name', 'ASC')
        ->with(['sub_category' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->orderBy('id','DESC')
        ->get();
        $featuredProducts = Product::where('is_featured','Yes')->where('status',1)->orderBy('id','DESC')->get();
        $latestProducts = Product::orderBy('id','DESC')->where('status',1)->take(8)->get();
        return view('front.home',compact('categories','featuredProducts','latestProducts'));
    }
}
