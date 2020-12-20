<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserToken;

class UserTokenController extends Controller
{
    public function logOut(Request $request)
    {
        $idExists = UserToken::where('user_id', $request->user_id)->exists();
        if ($idExists) {
            $result = UserToken::where('user_id', $request->user_id)->delete();
            if ($result) {
                return json_encode(['status' => true]);
                } else {
                return json_encode(['status' => false]);
                }
        } else {
            return json_encode(['status' => false]);
        }
    }
}
