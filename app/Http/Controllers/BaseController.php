<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
  public function sendSuccessResponse($result, $message, $token = '')
  {
    $settings_arr = array();
    $settings_arr['success'] = 1;
    $settings_arr['message'] = $message;
    if ($token) {
      $settings_arr['access_token'] = $token;
    }
    $response = [
      'settings' => $settings_arr,
      'data'    => $result,
    ];
    return response()->json($response, 200);
  }

  public function sendError($error, $code = 400)
  {
    $response = [
      'settings' => ['success' => 0, 'message' => $error],
      'data'    => [],
    ];
    return response()->json($response, $code);
  }
}
