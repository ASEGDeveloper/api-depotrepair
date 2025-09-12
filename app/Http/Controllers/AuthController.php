<?php

namespace App\Http\Controllers;

use App\Models\Employee; // or User if you modified the User model
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
 use App\Traits\ApiResponse;


class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $request->validate([
            'EmployeeEmail' => 'required|email',
            'EmployeePassword' => 'required',
        ]);


        $employee = Employee::where('EmployeeEmail', $request->EmployeeEmail)->first();

        
        // // Compare MD5 password
        if (strtolower(trim($employee->EmployeePassword)) !== md5($request->EmployeePassword)) {
            return response()->json(['error' => 'Invalid user name or password'], 401);
        }

        // Delete old tokens (optional)
        $employee->tokens()->delete();

        $accessToken = $employee->createToken('API Token', ['*'], now()->addMinutes(15));

        // Create refresh token (long-lived, 7 days)
        $refreshToken = Str::random(64);
        $employee->refreshTokens()->create([
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7)
        ]); 

        $data = ['accessToken'=>$accessToken->plainTextToken,'refreshToken'=>$refreshToken,'employee'=>$employee];

        return $this->successResponse($data, 'logged in successfully!');

 
    }


    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);

        $hashedToken = hash('sha256', $request->refresh_token);
        $refreshToken = RefreshToken::where('token', $hashedToken)->first();


        if (!$refreshToken || $refreshToken->expires_at->isPast()) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }


        $employee = $refreshToken->employee;

        // Delete old access tokens
        $employee->tokens()->delete();

        // Create new access token
        $accessToken = $employee->createToken('API Token', ['*'], now()->addMinutes(15)); 

        $data = ['accessToken'=>$accessToken->plainTextToken];
        return $this->successResponse($data, 'New access token has been issued successfully.!'); 
    }



    public function logout(Request $request)
    {
        $employee = $request->user();
        // Delete all access tokens
        $employee->tokens()->delete();
        // Delete all refresh tokens
        $employee->refreshTokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getEmployees(Request $request)
    {
       return Employee::select('*')->first(); // this is for testing purpose
       
    }
}
