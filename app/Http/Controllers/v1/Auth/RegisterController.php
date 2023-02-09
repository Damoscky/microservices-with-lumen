<?php

namespace App\Http\Controllers\v1\Auth;


use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Responser\JsonResponser;
use Illuminate\Support\Facades\DB;
use App\Interfaces\RoleInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Customer Sign up
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // return "123";
        /**
         * Validate Data
         */
        return $validate = $this->validateRegister($request);
        /**
         * if validation fails
         */
        if ($validate->fails()) {
            return JsonResponser::send(true, "Validation Failed", $validate->errors()->all());
        }

        $data = $request->only('firstname', 'lastname', 'phoneno', 'email', 'password');
        $data["password"] =  Hash::make($request->password);

        // try {
            DB::beginTransaction();

            $user = User::create($data);
            if (isset($request->userRole)) {
                $userRole = $request->userRole;
            } else {
                $userRole = RoleInterface::CUSTOMER;
            }

            if ($userRole){
                $user->attachRole($userRole);
            }

            $verification_code = Str::random(30); //Generate verification code
            DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

            $maildata = [
                'email' => $data['email'],
                'name' => $data["firstname"],
                'verification_code' => $verification_code,
                'subject' => "Please verify your email address.",
                "vendor" => false
            ];

            // $dataToLog = [
            //     'causer_id' => $user->id,
            //     'action_id' => $user->id,
            //     'action_type' => "Models\User",
            //     'log_name' => "User account created successfully",
            //     'description' => "{$user->firstname} {$user->lastname} account created successfully",
            // ];

            // ProcessAuditLog::storeAuditLog($dataToLog);

            // Mail::to($data['email'])->send(new VerifyEmail($maildata));
            DB::commit();
            return JsonResponser::send(false, "Thanks for signing up! Please check your email to complete your registration.", null, 201);
        // } catch (\Throwable $error) {
        //     DB::rollback();
        //     return $error->getMessage();
        //     logger($error);
        //     return JsonResponser::send(true, "Internal server error", null, 500);
        // }
    }


    /**
     * Resend Email Token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendCode(Request $request)
    {
        /**
         * Validate Data
         */
        $validate = $this->validateResendCode($request);
        /**
         * if validation fails
         */
        if ($validate->fails()) {
            return JsonResponser::send(true, "Validation Failed", $validate->errors()->all());
        }

        $email = $request->email;
        $user = User::where("email", $email)->first();
        if (!$user) {
            return JsonResponser::send(true, "User not found", null, 404);
        }

        if ($user->is_verified) {
            return JsonResponser::send(true, "Account already verified", null, 400);
        }

        $verification_code = Str::random(30); //Generate verification code
        DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

        $maildata = [
            'email' => $email,
            'name' => $user->firstname,
            'verification_code' => $verification_code,
            'subject' => "Please verify your email address.",
            "vendor" => $user->hasRole("merchant")
        ];

        Mail::to($email)->send(new VerifyEmail($maildata));
        return JsonResponser::send(false, "Verification link sent successfully.", null);
    }

    /**
     * Validate register request
     */
    protected function validateRegister($request)
    {
        $rules =  [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phoneno' => 'required|max:12|unique:users',
            'password' => 'required|min:6',
            'confirmPassword' => 'same:password'
        ];

        $validatedData = Validator::make($request->all(), $rules);
        return $validatedData;
    }

 

    /**
     * Validate resend code request
     */
    protected function validateResendCode($request)
    {
        $rules =  [
            'email' => 'required|email|max:255',
        ];

        $validatedData = Validator::make($request->all(), $rules);
        return $validatedData;
    }
}
