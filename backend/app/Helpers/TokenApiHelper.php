<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Exception;

use App\Models\User;
use App\Models\AccessToken;

use App\Exceptions\CustomException;
use App\Http\Resources\MaterialResource;

class TokenApiHelper
{
    public static function login($request)
    {
        try
        {
            $user = self::CheckUser($request->email, $request->password);
            return self::GenerateToken($user);
        }
        catch (Exception $e)
        {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
 
    protected static function CheckUser($email, $password)
    {
        try
        {
            $user = User::select('id', 'name', 'username', 'email', 'password')
                ->where('email', $email)
            ->firstOrFail();

            if (!(\Hash::check($password, $user->password))) throw new CustomException('Username or passoword invalid');

            return $user;
        }
        catch (ModelNotFoundException $th)
        {
            throw new CustomException('User not found');
        }
        catch (CustomException $th)
        {
            throw new CustomException($th->getMessage());
        }
    }

    protected static function GenerateToken($user)
    {
        try
        {
            $userData = [
                'userId'   => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'username' => $user->username
            ];
            $decode  = base64_encode(json_encode($userData));
            $encrypt = \Str::random(10);

            $accessToken              = new AccessToken;
            $accessToken->token       = encrypt("{$decode}.$encrypt");
            $accessToken->expired_at  = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') .' +1 day'));
            $accessToken->is_active   = true;
            $accessToken->created_at  = now();
            $accessToken->save();
            
            return response()->json([
                'status'    => true,
                'token'     => $accessToken->token,
                'expiredAt' => $accessToken->expired_at,
                'data'      => $userData
            ]);
        }
        catch (Exception $e)
        {
            throw new CustomException('Invalid generate token', 500);
        }
    }

    public static function VerifyToken($token)
    {
        try
        {
            $accessToken = AccessToken::select('id', 'token', 'expired_at')
                ->activeToken($token)
            ->firstOrFail();

            if ($accessToken) return true;
            else return false;
        }
        catch (ModelNotFoundException $th)
        {
            throw new CustomException('Token invalid or expired', 401);
        }
        catch (CustomException $th)
        {
            throw new CustomException('Invalid token check', 500);
        }
    }

    public function DecodeToken($key)
    {
        try
        {
            $decodeKey = decrypt($key);
            $arrDecodeKey = explode('.', $decodeKey);
            return json_decode(base64_decode($arrDecodeKey[0]));
        }
        catch (CustomException $e)
        {
            throw new CustomException('Invalid decode token', 500);
        }
    }

    protected static function ApiResponse($response)
    {
        header('Content-Type: application/json');
        die(json_encode($response));
    }
}