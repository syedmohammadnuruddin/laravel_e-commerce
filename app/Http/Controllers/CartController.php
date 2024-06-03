<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
   
        public function addToCart(Request $request) {
            $product = Product::with('product_images')->find($request->id);

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ]);
            }

            $productAlreadyExist = Cart::content()->contains('id', $product->id);
            if ($productAlreadyExist) {
                return response()->json([
                    'status' => false,
                    'message' => $product->title . ' already added in cart'
                ]);
            }

            $image = $product->product_images->isNotEmpty() ? $product->product_images->first()->image : '';

            Cart::add(
                $product->id, 
                $product->title, 
                1, 
                $product->price, 
                ['product_images' => $image]
            );

            $message = '<strong>'.$product->title .'</strong> product added in cart successfully';
            session()->flash('success', $message);

            return response()->json([
                'status' => true,
                'message' => $product->title . ' added in cart',
                'image' => $image // Debugging info
            ]);
}


    


    
    public function cart() {
        $categories = Category::orderBy('name', 'ASC')
            ->with(['sub_category' => function ($query) {
                $query->where('status', 1);
            }])
            ->where('showHome', 'Yes')
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();
    
        $cartContent = Cart::content();
        // Uncomment the line below to debug cart content
        // dd($cartContent);
    
        return view('front.cart', compact('categories', 'cartContent'));
    }

    public function updateCart(Request $request){
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);

        if($product->track_qty == 'Yes'){
            if($qty <= $product->qty){
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            }else{
                $message = 'Requested qty('.$qty.') not available in stock';
                $status = false;
                session()->flash('error', $message);
            }
        }else{
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }
    public function deleteItem(Request $request){
        $itemInfo = Cart::get($request->rowId);

        if($itemInfo == null){
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);

            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($request->rowId);
        $message = 'Item removed from cart successfully';
        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
    
}

