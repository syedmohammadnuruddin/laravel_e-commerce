<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    public function index(Request $request){
        $products = Product::latest('id')->with('product_images');
        if($request->get('keyword') != ""){
            $products = $products->where('title','like','%'.$request->keyword.'%');
        }
        $products = $products->paginate();
        return view('admin.products.list',compact('products'));
    }
    
    public function create(){
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.create', compact('categories','brands'));
    }
    public function store(Request $request){
        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];
        if(!empty($request->track_qty) && $request->track_qty == 'Yes'){
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if($validator->passes()){
            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();

            if(!empty($request->image_array)){
                foreach($request->image_array as $temp_image_id){
                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.',$tempImageInfo->name);
                    $ext = last($extArray);

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // Generate Product Thumbnails
                    // Large Image
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destPath = public_path() . '/uploads/product/large/'.$imageName;
                    $manager = new ImageManager(new Driver());
                    $img = $manager->read($sourcePath);
                    $img = $img->resize(1400,900);
                    $img->save($destPath);
                    
                    // Small Image
                    $destPath = public_path().'/uploads/product/small/'.$imageName;
                    $manager = new ImageManager(new Driver());
                    $img = $manager->read($sourcePath);
                    $img = $img->resize(300,300);
                    $img->save($destPath);
                }
            }

            $request->session()->flash('success', 'Product Added Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product Added Successfully'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit($id){
        $product = Product::find($id);
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $subcategories = SubCategory::where('category_id', $product->category_id)->get();

        $relatedProducts = [];
        if($product->related_products != ''){
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->get();
        }
        $productImages = ProductImage::where('product_id', $product->id)->get();

        return view('admin.products.edit',compact('product','categories','brands','subcategories','productImages','relatedProducts'));
    }
    public function update(Request $request, $id){
            $product = Product::find($id);

            $rules = [
                'title' => 'required',
                'slug' => 'required|unique:products,slug,'.$product->id.',id',
                'price' => 'required|numeric',
                'sku' => 'required|unique:products,sku,'.$product->id.',id',
                'track_qty' => 'required|in:Yes,No',
                'category' => 'required|numeric',
                'is_featured' => 'required|in:Yes,No',
            ];
            if(!empty($request->track_qty) && $request->track_qty == 'Yes'){
                $rules['qty'] = 'required|numeric';
            }
    
            $validator = Validator::make($request->all(), $rules);

            if($validator->passes()){
                $product->title = $request->title;
                $product->slug = $request->slug;
                $product->description = $request->description;
                $product->short_description = $request->short_description;
                $product->shipping_returns = $request->shipping_returns;
                $product->price = $request->price;
                $product->compare_price = $request->compare_price;
                $product->sku = $request->sku;
                $product->barcode = $request->barcode;
                $product->track_qty = $request->track_qty;
                $product->qty = $request->qty;
                $product->status = $request->status;
                $product->category_id = $request->category;
                $product->sub_category_id = $request->sub_category;
                $product->brand_id = $request->brand;
                $product->is_featured = $request->is_featured;
                $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
                $product->save();

                return redirect()->route('products.index')->with('success','Product Updated Successfully');

                
            }else{
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }    
    }

    public function destroy(Request $request, $id) {
        $product = Product::find($id);
        if (empty($product)) {
            $request->session()->flash('error', 'Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
    
        $productImages = ProductImage::where('product_id', $id)->get();
        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/product/large/' . $productImage->image));
                File::delete(public_path('uploads/product/small/' . $productImage->image));
            }
            ProductImage::where('product_id', $id)->delete();
        }
    
        $product->delete();
        $request->session()->flash('success', 'Product deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
    
    public function getProducts(Request $request){
        $tempProduct = [];
        if($request->term != ""){
            $products = Product::where('title','like','%'.$request->term.'%')->get();

            if($products != null){
                foreach($products as $product){
                    $tempProduct[] = array('id'=>$product->id,'text'=>$product->title);
                }
            }
        }
       return response()->json([
        'tags' => $tempProduct,
        'status' => true
       ]);
    }
}
