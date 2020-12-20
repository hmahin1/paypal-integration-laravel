<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserToken;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json(['status' => false,'code' => 400,'error'=>$validator->errors()]); 
        }
        
        $user = new User();     
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = md5($request->password);
        $user->user_type = 'user';
        $user->status = 'active';
        $user->save();
        
        if ($user) {
            $user_tok = new UserToken();
            $token=$this->getToken(50);        
            $user_tok->user_id = $user->id; //id will be get
            $user_tok->user_token = $token;            
            $user_tok->revoke_or_status = 'active';
            $user_tok->save();

            $user['token'] = $token;
            return response()->json(['status' => true,'code'=>200,'data'=>$user]);
        } else {
            return response()->json(['status' => false,'code'=>400]);
        }
    }

    public function logIn(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['status' => false,'code' => 400,'error'=>$validator->errors()]); 
        }
        
        if ($user = User::where('email',$request->email)->where('password', md5($request->password))->first()) 
        {
            $user_tok = new UserToken();
            $token=$this->getToken(50);        
            $user_tok->user_id = $user->id; //id will be get
            $user_tok->user_token = $token;            
            $user_tok->revoke_or_status = 'active';
            $user_tok->save();

            $user['token'] = $token;
            if ($user) {
                return response()->json(['status' => true,'code'=>200,'data'=>$user]);
            } else {
                return response()->json(['status' => false,'code'=>400]);
            }
        } else {
            return response()->json(['status' => false,'code'=>400,'error'=>'Email or password does not match']);
        }
    }
    
    public function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max-1)];
        }
        if(UserToken::where('user_token',$token)->first()){
            $this->getToken(50);
        }else{
            return $token;
        }
    }
     
   public function userList(Request $request){
      
    $limit;
    if(isset($request->limit) > env('M_LIMIT')){
       $limit = env('M_LIMIT');
    }else{
       $limit = $request->limit;
    }
    
    $users = User::paginate($limit);
    
    $users->appends($request->query())->links();
    return response()->json(['status' => true,'code' => 200,'data'=>$users]); 

}
public function getUser(Request $request){
    $user = User::where('id',$request->user->id)->first(); 
    return response()->json(['status' => true,'code' => 200,'data'=>$user]); 
}
}
