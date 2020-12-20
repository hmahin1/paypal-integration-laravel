<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Order;
use Validator;
use Illuminate\Http\Request;

class OrderController extends Controller
{
   public function placeOrder(Request $request){
/* 
      $validator = Validator::make($request->all(),[
         'product_id' => 'nullable|exist:products,id',
         'search' => 'nullable|string'
     ]);

     
      if($validator->fails()){
         return response()->json(['status' => false,'code' => 400,'error'=>$validator->errors()]); 
      } */

      if(empty($request->product_id) && empty($request->search)){
         return response()->json(['status' => false,'code' => 400,'error'=>'Kindly Select One Coins']);
      }

      
     $order  = new Order();

      if(isset($request->product_id)){
         if(!$prod = Product::where('status','active')->where('id',$request->product_id)->first()){
            return response()->json(['status' => false,'code' => 400,'error'=>'Product Not Found']);
         }
            
      $prod = Product::where('status','active')->where('id',$request->product_id)->first();
      $price = $prod->price;
      $coin = $prod->coins;
      $order->product_id = $request->product_id;
      }
     
     if($request->search){
      $serach = $request->search.'000';
      $price  = (int)$serach * env('COIN_PRICE');
      $coin = $request->search;
     }

     $order->price = $price;
     $order->coins = $coin;
     $order->user_id = $request->user->id;
     $order->status = 'confirmed';
     $order->save();

     return response()->json(['status' => true,'code' => 200,'data'=>$order]);
   }
   
   public function orderList(Request $request){
      
      $limit;
      if(isset($request->limit) > env('M_LIMIT')){
         $limit = env('M_LIMIT');
      }else{
         $limit = $request->limit;
      }
      
      if($request->user->user_type == 'admin'){
         $orders = Order::paginate($limit);
      }else{
         $orders = Order::where('user_id',$request->user->id)->paginate($limit);
      }
      
      $orders->appends($request->query())->links();
      return response()->json(['status' => true,'code' => 200,'data'=>$orders]); 
   }
  
   public function adminOrderList(Request $request){
      $products = Product::where('status','active')->get();
      return response()->json(['status' => true,'code' => 200,'data'=>$products]); 
   }
}
