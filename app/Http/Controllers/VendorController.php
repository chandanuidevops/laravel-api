<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;


use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Exceptions\CustomJsonResponse;
use App\Http\Resources\VendorResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
       
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:60'],
            'email' => [
                'required', 'regex:/\S+@\S+\.\S+/',
                function ($attributes, $value, $fail) {
                    $user = Vendor::where('email', '=', $value)->whereNull('deleted_at')->first();
                    if ($user !== null) {
                        $fail($attributes . ' is not unique.');
                    }
                },
            ],
            'mobile' => [
                'required',  'string', 'max:10',
                function ($attributes, $value, $fail) {
                    $user = Vendor::where('mobile', '=', $value)->whereNull('deleted_at')->first();
                    if ($user !== null) {
                        $fail($attributes . ' is not unique.');
                    }
                },
            ],
            'password' => [
                'required',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{6,})/',
            ],
        ]);
        if ($validator->fails()) {
            return CustomJsonResponse::validatorResponse(
                Response::HTTP_BAD_REQUEST,
                null,
                $validator->errors()
            );
        }
        $validated_data = $validator->validated();
        if (array_key_exists('password', $validated_data)) {
            $validated_data['password'] = bcrypt($validated_data['password']);
        }
        $validated_data['isActive'] = true;
        $user = Vendor::create($validated_data);
        return CustomJsonResponse::response(
            Response::HTTP_OK,
            new VendorResource($user),
            null,
            1
        );
    }
    public function show($id)
    {
        $user = Vendor::getWithUUID($id);
        if ($user === null) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                "No record was found with that 'id'",
                0
            );
        }
        return CustomJsonResponse::response(
            Response::HTTP_OK,
            new VendorResource($user),
            null,
            1
        );
    }
}
