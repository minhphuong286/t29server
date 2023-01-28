<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','refresh','logout']]);
        $this->guard = "api";
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $credentials = [
            "phone" => $request->phone,
            "password" => $request->password
        ];;

        $token = auth()->attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return $this->successResponse([
                'type' => 'bearer',
                'access_token' => $token,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'expires_in' => 60 * 365 * 60,
                'user' => $user,
            ]);

    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'avatar' => 'mimes:jpeg,jpg,png,webp,svg',
            'big_image' => 'mimes:jpeg,jpg,png,webp,svg'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        if($request->hasFile('avatar')){
            $image_avatar = $request->file('avatar');
            $name_image_avatar = time() .'_'.$image_avatar->getClientOriginalName();
            $path_avatar = 'image_user/avatars/'.$name_image_avatar;
            $destinationPathAvatar = public_path().'/image_user/avatars';
            $image_avatar->move($destinationPathAvatar,$name_image_avatar);
        }
        if($request->hasFile('big_image')){
            $image = $request->file('big_image');
            $name_image = time() .'_'.$image->getClientOriginalName();
            $path_big_image = 'image_user/big_images/'.$name_image;
            $destinationPath = public_path().'/image_user/big_images';
            $image->move($destinationPath,$name_image);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => isset($path_avatar) ? $path_avatar : null,
            'big_image' => isset($path_big_image) ? $path_big_image : null,
        ]);
       

       
        $token = Auth::login($user);
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'expires_in' => 60 *365 * 60,
            'user' => auth()->user()
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return $this->successResponse(['message' => 'User signed out successfully']);
    }

    public function refresh()
    {
        $token = auth()->refresh();
        $user = auth()->setToken($token)->user();
        
        
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'expires_in' => 60 * 365 * 60,
            'user' => $user
        ]);
    }


}

