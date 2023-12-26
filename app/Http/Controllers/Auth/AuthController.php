<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{

  public function login(Request $request)
  {
    try {
      $input = $request->all();
      $validator = Validator::make($input, [
        'username' => 'required',
        'password' => 'required',
      ]);
      if ($validator->fails()) {
        return $this->sendError($validator->errors()->first());
      }
      $credentials = $request->only('username', 'password');
      if (Auth::attempt($credentials)) {
        /** @var \App\Models\User $user **/
        $user = Auth::user();
        $token = $user->createToken("my-token")->accessToken;
        $user->group;
        return $this->sendSuccessResponse($user, "Login Success", $token);
      } else {
        return $this->sendError("Login Failed", 401);
      }
    } catch (\Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }
  public function logout(Request $request)
  {
    if (Auth::check()) {
      /** @var \App\Models\User $user **/
      $user = Auth::user();
      $user->token()->revoke();
      return $this->sendSuccessResponse([], "Logout Success");
    } else {
      return $this->sendError('Not Authenticated');
    }
  }
  public function changePassword(Request $request)
  {
    try {
      $input = $request->all();
      $validator = Validator::make($input, [
        'current_password' => 'required',
        'new_password' => 'required',
        'new_confirm_password' => 'same:new_password',
      ]);
      if ($validator->fails()) {
        return $this->sendError($validator->errors()->first());
      }
      if (!Hash::check($request->current_password, auth()->user()->password)) {
        return $this->sendError("Password mismatch");
      }
      User::find(auth()->user()->id)->update(['password' => Hash::make($request->new_password)]);
      User::where('id', auth()->user()->id)->update(['password_changed_at' => Carbon::now()]);
      return $this->sendSuccessResponse([], 'Password updated');
    } catch (\Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }
}
