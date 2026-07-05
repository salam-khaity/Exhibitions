<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Exhibitor;
use App\Models\Organizer;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'role' => 'required|in:visitor,exhibitor,organizer',
    ]);

    return match($request->role) {
        'visitor'   => $this->visitorRegister($request),
        'exhibitor' => $this->exhibitorRegister($request),
        'organizer' => $this->organizerRegister($request),
    };
}
    public function organizerRegister(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|regex:/^[\pL\s]+$/u',
            'email'=> 'required|email|unique:users,email',
            'password'            => [
            'required',
                'confirmed',
                Password::min(6)
                ->mixedCase()
                ->numbers()
                ->symbols(),
            ],
            'company_name' => 'required|string|max:150',
            'company_type' => 'required|in:government,private,nonprofit',
            'phone' => 'required|regex:/^09\d{8}$/',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'commercial_register' => 'required|digits:10',
            'website' => 'nullable|url',
            'bio' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
]);

        if ($validator->fails()) {
            return response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first()
        ], 422);
        }
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role'      => 'organizer',

        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = ImageHelper::uploadAvatar($request->file('avatar'), 'organizers');
        }

        Organizer::create([
            'user_id'              => $user->id,
            'company_name'         => $request->company_name,
            'company_type'         => $request->company_type,
            'phone'                => $request->phone,
            'city'                 => $request->city,
            'country'              => $request->country,
            'commercial_register'  => $request->commercial_register,
            'website'              => $request->website,
            'bio'                  => $request->bio,
            'avatar'              => $avatarPath,
        ]);

        $token  = $user->createToken('MyApp')->plainTextToken;
        return  response()->json([
            'status' =>'success',
            'token'=>$token
        ]);
    }
    public function exhibitorRegister(Request $request){

        $validator = Validator::make($request->all(), [
        'name'      => 'required|string|max:100',
        'email'     => 'required|email|unique:users,email',
        'password' => [
        'required',
        'confirmed',
        Password::min(6)
        ->mixedCase()
        ->numbers()
        ->symbols(),
        ],
        'brand_name'    => 'required|string|max:150',
        'industry'      => 'required|string|max:100',
        'phone'         => 'required|regex:/^09\d{8}$/',
        'city'          => 'required|string|max:100',
        'country'       => 'required|string|max:100',
        'description'   => 'required|string',
        'logo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'avatar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first()
        ], 422);
        }
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role'      => 'exhibitor',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = ImageHelper::uploadAvatar($request->file('logo'), 'logos');
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = ImageHelper::uploadAvatar($request->file('avatar'), 'exhibitors');
        }

        Exhibitor::create([
        'user_id'       => $user->id,
        'brand_name'    => $request->brand_name,
        'industry'      => $request->industry,
        'phone'         => $request->phone,
        'city'          => $request->city,
        'country'       => $request->country,
        'description'   => $request->description,
        'logo'          => $logoPath,
        'avatar'        => $avatarPath,
        ]);

        $token = $user->createToken('MyApp')->plainTextToken;
        return  response()->json([
            'status' =>'success',
            'token'=>$token
        ]);
    }
    public function visitorRegister(Request $request){

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password' => [
            'required',
            'confirmed',
            Password::min(6)
            ->mixedCase()
            ->numbers()
            ->symbols(),
        ],
        'phone' => 'nullable|regex:/^09\d{8}$/',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'status'  => 'error',
            'message' => $validator->errors()->first()
        ], 422);
        }
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role'      => 'visitor',

        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = ImageHelper::uploadAvatar($request->file('avatar'), 'visitors');
        }

        Visitor::create([
            'user_id'       => $user->id,
            'phone'         => $request->phone,
            'avatar'        => $avatarPath,
        ]);
        $token  = $user->createToken('MyApp')->plainTextToken;
        return  response()->json([
            'status' =>'success',
            'token'=>$token
        ]);
    }

    public function login (Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);
        $user= User::where('email', $request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password))
        {
            return response()->json(['message'=>'Invalid Credential'],401) ;
        }
        $token = $user->createToken($user->role . '-token')->plainTextToken;
        return  response()->json(['token'=>$token,'role'=>$user->role]);
    }

    public function logout(Request $request)
    {
        auth('sanctum')->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
