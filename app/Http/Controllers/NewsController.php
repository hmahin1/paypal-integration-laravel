<?php

namespace App\Http\Controllers;

use App\Models\News;
use Validator;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function news(Request $request){

        $validator = Validator::make($request->all(),[
            'details' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json(['status' => false,'code' => 400,'error'=>$validator->errors()]); 
        }
       
        if($request->id){
            if(!$news = News::where('id',$request->id)->first()){
                return response()->json(['status' => false,'code' => 400,'error'=>'News not found']); 
            }
        }else{
            $news = new News();
            $news->user_id = 1; // will be get from middleware of authentication
            $news->status = 'active';
        }

        $news->details = $request->details;
        $news->save();

        return response()->json(['status' => false,'code' => 200,'data'=>$news]); 

    }

    
    public function show(Request $request){
        $news = News::where('status','active')->get();
        return response()->json(['status' => false,'code' => 200,'data'=>$news]); 
    }
}
