<?php

namespace App\Http\Transformers\Auth;

/**
 * Class RegisterUserTransformer
 * @package App\Http\Transformers\Auth
 */
class RegisterUserTransformer
{
    public function isEmail($request) {
       return filter_var($request->login, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isPhone($request) {
        return preg_match("/^[0-9]{10,12}$/", $request->login) !== 0;
    }

    public function phoneRegistration($request)
    {
        return [
            'name' =>$request->name,
            'address' => $request->address,
            'phone' => $request->login,
            'password' =>$request->password,
        ];
    }

    public function emailRegistration($request)
    {
        return [
            'name' =>$request->name,
            'address' => $request->address,
            'email' => trim(strtolower($request->login)),
            'password' =>$request->password,
        ];
    }

}
