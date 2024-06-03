<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(){
        $categories = Category::orderBy('name', 'ASC')
        ->with(['sub_category' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->orderBy('id','DESC')
        ->get();
        return view('front.account.login',compact('categories'));
    }
    public function register(){
        $categories = Category::orderBy('name', 'ASC')
        ->with(['sub_category' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->orderBy('id','DESC')
        ->get();

        return view('front.account.register', compact('categories'));
    }
    // public function processRegister(Request $request){
    //     $validator = Validator::make($request->all(),[
    //         'name' => 'required|min:3',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required|min:5|confirmed'
    //     ]);
    //     if($validator->passes()){

    //     }else{
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors()
    //         ]);
    //     }
    // }

    public function processRegister(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|confirmed'
        ]);
    
        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have been registered successfully');

            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function authenticate(Request $request){
         $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
         ]);

         if($validator->passes()){
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))){
                return redirect()->route('account.profile');
            }else{
                return redirect()->route('account.login')
                ->withInput($request->only('email'))
                ->with('error', 'Either email/password is incorrect.');
            }
         }else{
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
         }
    }
    public function profile(){
        $categories = Category::orderBy('name', 'ASC')
        ->with(['sub_category' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->orderBy('id','DESC')
        ->get();
        return view('front.account.profile', compact('categories'));
    }
    public function logout(){
        Auth::logout();
        return redirect()->route('account.login')
        ->with('success', 'You have logged out successfully');
    }
    
}
