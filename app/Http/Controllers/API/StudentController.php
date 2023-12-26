<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;

use App\Models\Student;
use Illuminate\Http\Request;
use Exception;
use App\Libraries\General;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class StudentController extends BaseController
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    try {
      $inputParams = $request->all();
      $dataList = Student::limit($inputParams['limit'])->offset($inputParams['offset'])
        ->where('first_name', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orWhere('last_name', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orWhere('email', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orWhere('mobile', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orderBy($inputParams['sort'], $inputParams['order'])->get();
      $dataCount = Student::where('first_name', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orWhere('last_name', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orWhere('email', 'LIKE', '%' . $inputParams['search'] . '%')
        ->orWhere('mobile', 'LIKE', '%' . $inputParams['search'] . '%')->get();
      $data['status'] = General::getEnumValues('students', 'status');
      $data['count'] = $dataCount->count();
      $data['data'] = $dataList;

      $message = (count($dataCount) > 0) ?  'Data retrieved successfully' : 'data Not Found';

      return $this->sendSuccessResponse($data,  $message);
    } catch (Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    try {
      $inputParams = $request->all();
      $validator = Validator::make($request->all(), [
        "first_name" => "required",
        "last_name" => "required",
        "email" => "required",
        "profile" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
        "address" => "required",
        "mobile" => "required",
        "course" => "required",
        "status" => "required",
      ]);
      if ($validator->fails()) {
        return $this->sendError($validator->errors()->first());
      }
      $imageName = '';
      $extensionArr = ['jpg', 'png', 'jpeg', 'gif'];
      if ($request->has('profile') && !empty($request->file('profile')->getClientOriginalName())) {
        $imageExt = $request->file('profile')->extension();
        if (in_array($imageExt, $extensionArr)) {
          $imageName = 'students' . time() . '.' . $imageExt;
          $request->profile->move(public_path('upload/students/'), $imageName);
        } else {
          return $this->sendError('Please Select Valid Image Extension ex-.jpeg,.png,.jpg,.gif');
        }
      }
      $student = new Student();
      $student->first_name = $inputParams['first_name'];
      $student->last_name = $inputParams['last_name'];
      $student->email = $inputParams['email'];
      $student->mobile = $inputParams['mobile'];
      $student->address = $inputParams['first_name'];
      $student->course = $inputParams['course'];
      $student->status = $inputParams['status'];
      $student->profile = $imageName;
      $student->save();
      $insertId = $student->id;
      if (!$insertId) {
        throw new Exception('Student action failure in adding record');
      }
      return $this->sendSuccessResponse($student, 'Student action record added successfully');
    } catch (Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request, string $id)
  {
    try {
      $resultData = Student::find($id);
      if (!$resultData) {
        throw new Exception('No record found');
      }
      $data['studentData'] = $resultData;
      return $this->sendSuccessResponse($data, 'Data fetched successfully');
    } catch (Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }


  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request)
  {
    try {
      $inputParams = $request->all();
      $validator = Validator::make($request->all(), [
        'id' => 'required|exists:students,id',
        "first_name" => "required",
        "last_name" => "required",
        "email" => "required",
        "profile" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
        "address" => "required",
        "mobile" => "required",
        "course" => "required",
        "status" => "required",
      ]);

      if ($validator->fails()) {
        return $this->sendError($validator->errors()->first());
      }
      $imageName = '';
      $extensionArr = ['jpg', 'png', 'jpeg', 'gif'];
      if ($request->has('profile') && !empty($request->file('profile')->getClientOriginalName())) {
        $imageExt = $request->file('profile')->extension();
        if (in_array($imageExt, $extensionArr)) {
          $imageName = 'students' . time() . '.' . $imageExt;
          $request->profile->move(public_path('upload/students/'), $imageName);
          if (!empty($request->old_image_name)) {
            $image_path = public_path('upload/students/' . $request->old_image_name);
            if (File::exists($image_path)) {
              File::delete($image_path);
            }
          }
        } else {
          throw new Exception('Invalid Image Extension');
        }
      }
      $student = Student::where('id', $inputParams['id'])->first();
      if (empty($student)) {
        throw new Exception('No record found');
      }
      $student->first_name = $inputParams['first_name'];
      $student->last_name = $inputParams['last_name'];
      $student->email = $inputParams['email'];
      $student->mobile = $inputParams['mobile'];
      $student->address = $inputParams['first_name'];
      $student->course = $inputParams['course'];
      $student->status = $inputParams['status'];
      $student->profile = $imageName;
      $student->save();
      $insertId = $student->id;
      if (!$insertId) {
        throw new Exception('Student action failure in updating record');
      }
      return $this->sendSuccessResponse($student, 'Student action record updated successfully');
    } catch (Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function updateStatus(Request $request)
  {
    try {
      $paramsArr = $request->all();
      $validator = Validator::make($paramsArr, [
        'id' => 'required|exists:students,id',
        'status' => 'required'
      ]);
      if ($validator->fails()) {
        return $this->sendError($validator->errors()->first());
      }
      $values = array('status' => $paramsArr['status']);
      $affectRow =  Student::whereIn('id', $paramsArr['id'])->update($values);
      if (!$affectRow) {
        throw new Exception('Student action failure in updating record');
      }
      return $this->sendSuccessResponse([], 'Student action record updated successfully');
    } catch (Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }
  /**
   * Remove the specified resource from storage.
   */
  public function deleteAll(Request $request)
  {
    try {
      $paramsArr = $request->all();
      $validator = Validator::make($paramsArr, [
        'id' => 'required|exists:students,id',
      ]);
      if ($validator->fails()) {
        return $this->sendError($validator->errors()->first());
      }
      $affectRow =  Student::whereIn('id', $paramsArr['id'])->delete();
      if (!$affectRow) {
        throw new Exception('Student action failure in deletion record');
      }
      return $this->sendSuccessResponse([], 'Student action record deleted successfully');
    } catch (Exception $e) {
      return $this->sendError($e->getMessage());
    }
  }
}
