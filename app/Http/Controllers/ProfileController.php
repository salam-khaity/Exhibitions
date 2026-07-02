<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function organizerShow()
    {

        $user    = User::find(Auth::id());
        $profile = $user->organizer;

        return response()->json([
                'status' => 'success',
                'data'   => [
                'name'                => $user->name,
                'email'               => $user->email,
                'company_name'        => $profile->company_name,
                'company_type'        => $profile->company_type,
                'phone'               => $profile->phone,
                'city'                => $profile->city,
                'country'             => $profile->country,
                'commercial_register' => $profile->commercial_register,
                'website'             => $profile->website,
                'bio'                 => $profile->bio,
            ]
        ]);

    }

    public function organizerUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'name'                => 'sometimes|string|max:100',
        'email'               => 'sometimes|email|unique:users,email,' . Auth::id(),
        'company_name'        => 'sometimes|string|max:150',
        'company_type'        => 'sometimes|in:government,private,nonprofit',
        'phone'               => 'sometimes|string|max:20',
        'city'                => 'sometimes|string|max:100',
        'country'             => 'sometimes|string|max:100',
        'commercial_register' => 'sometimes|string|max:50',
        'website'             => 'nullable|url',
        'bio'                 => 'sometimes|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
                'status'  => 'error',
            'message' => $validator->errors()->first()
        ], 422);
    }

    $user = Auth::user();

    if ($request->hasAny(['name', 'email'])) {
        $user->update(
            $request->only(['name', 'email'])
        );
    }

    $companyData = $request->only([
            'company_name',
            'company_type',
            'phone',
            'city',
            'country',
            'commercial_register',
            'website',
            'bio',
        ]);

    if (!empty($companyData)) {
        $user->organizer->update($companyData);
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'Profile Updated Successfully',
    ]);
    }
}
