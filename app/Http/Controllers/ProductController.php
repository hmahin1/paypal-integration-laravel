<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Validator;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function products(Request $request){

        $validator = Validator::make($request->all(),[
            'product_type' => 'required|string|in:ps4,xbox,pc',
            'coins' => 'required|integer',
            'price' => 'required'

        ]);

        if($validator->fails()){
            return response()->json(['status' => false,'code' => 400,'error'=>$validator->errors()]); 
        }
       
        if($request->id){
            if(!$news = Product::where('id',$request->id)->first()){
                return response()->json(['status' => false,'code' => 400,'error'=>'Product not found']); 
            }
        }else{
            $products = new Product();
            $products->user_id = 1; // will be get from middleware of authentication
            $products->status = 'active';
        }

        $products->product_type = $request->product_type;
        $products->coins = $request->coins;
        $products->price = $request->price;
        $products->per_coin_price = $request->price/$request->coins;
        $products->save();

        return response()->json(['status' => true,'code' => 200,'data'=>$products]); 

    }

    
    public function show(Request $request){
        $product_type = $request->product_type ;
        $products = Product::where('status','active')->where('product_type',$product_type)->get();
        return response()->json(['status' => true,'code' => 200,'data'=>$products]); 
    }
    public function getById(Request $request){
        $products = Product::where('status','active')->where('id',$request->id)->first();
        return response()->json(['status' => true,'code' => 200,'data'=>$products]); 
    }
}
