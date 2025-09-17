<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class MicrosoftController extends Controller
{
    public function redirectToMicrosoft()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function handleMicrosoftCallback()
    {
        $microsoftUser = Socialite::driver('microsoft')->user();

        // Example: find or create user in your DB
        $user = User::updateOrCreate(
            ['email' => $microsoftUser->getEmail()],
            [
                'name' => $microsoftUser->getName(),
                'microsoft_id' => $microsoftUser->getId(),
            ]
        );

        // Generate API token for Next.js frontend
        $token = $user->createToken('api-token')->plainTextToken;

        // Redirect or return token to frontend
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
