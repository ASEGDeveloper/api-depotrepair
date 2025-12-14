<?php

namespace App\Http\Controllers;

use App\Models\Employee; // or User if you modified the User model
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
 use App\Traits\ApiResponse;
 use Illuminate\Support\Facades\Cache;


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

      //return "login sucessfully";

        // Delete old tokens (optional)
        $employee->tokens()->delete();

        $accessToken = $employee->createToken('API Token', ['*'], now()->addMinutes(120));

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

    // public function getEmployees(Request $request)
    // {
    //    return Employee::select('*')->limit(5)->get(); // this is for testing purpose
       
    // }

 

public function getEmployees(Request $request)
{
    $page   = $request->input('page', 1);
    $limit  = $request->input('limit', 10);
    $search = $request->input('search', '');
    $status = $request->input('status', '');

    // create unique cache key based on filters
    $cacheKey = "employees_page_{$page}_limit_{$limit}_search_{$search}_status_{$status}";

    // cache for 10 minutes (600 seconds)
    return Cache::remember($cacheKey, 600, function () use ($page, $limit, $search, $status) {
        $query = Employee::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('EmployeeName', 'like', "%{$search}%")
                  ->orWhere('EmployeeEmail', 'like', "%{$search}%");
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $total = $query->count();

        $employees = $query->offset(($page - 1) * $limit)
                           ->limit($limit)
                           ->get();

        return [
            'data'     => $employees,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $limit
        ];
    });
}



}
