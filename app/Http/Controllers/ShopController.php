<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null){
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];
    
        $categories = Category::orderBy('name', 'ASC')
                    ->with(['sub_category' => function ($query) {
                        $query->where('status', 1);
                    }])
                    ->where('showHome', 'Yes')
                    ->where('status', 1)
                    ->orderBy('id','DESC')
                    ->get();
    
        $sideCategories = Category::orderBy('name', 'ASC')
                    ->with(['sub_category' => function ($query) {
                        $query->where('status', 1);
                    }])
                    ->where('status', 1)
                    ->orderBy('name','ASC')
                    ->get();
    
        $brands = Brand::orderBy('name','ASC')->where('status',1)->get();
    
        $products = Product::where('status',1);
    
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            $products = $products->where('category_id', $category->id);
            $categorySelected = $category->id;
        }
    
        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            $products = $products->where('sub_category_id', $subCategory->id);
            $subCategorySelected = $subCategory->id;
        }
    
        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }
    
        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            $priceMin = intval($request->get('price_min'));
            $priceMax = intval($request->get('price_max'));
            
            if ($priceMax == 1000) {
                $products = $products->whereBetween('price', [$priceMin, 1000000]);
            } else {
                $products = $products->whereBetween('price', [$priceMin, $priceMax]);
            }
        } else {
            $priceMin = 0; // default min price
            $priceMax = 1000; // default max price
        }

        if ($request->get('sort')) {
            switch ($request->get('sort')) {
                case 'latest':
                    $products = $products->orderBy('created_at', 'DESC');
                    break;
                case 'price_high':
                    $products = $products->orderBy('price', 'DESC');
                    break;
                case 'price_low':
                    $products = $products->orderBy('price', 'ASC');
                    break;
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }
    
        $products = $products->orderBy('id', 'DESC')->paginate(6);
    
        return view('front.shop', compact('categories', 'brands', 'products', 'sideCategories', 'categorySelected', 'subCategorySelected', 'brandsArray', 'priceMax', 'priceMin'));
    }
    public function product($slug){
        $product = Product::where('slug', $slug)->with('product_images')->first();
        // dd($product);
        if($product == null){
            abort(404);
        }

        $relatedProducts = [];
        if($product->related_products != ''){
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->with('product_images')->get();
        }

        $categories = Category::orderBy('name', 'ASC')
                    ->with(['sub_category' => function ($query) {
                        $query->where('status', 1);
                    }])
                    ->where('showHome', 'Yes')
                    ->where('status', 1)
                    ->orderBy('id','DESC')
                    ->get();

        return view('front.product',compact('product','categories','relatedProducts'));
    }
    
}
