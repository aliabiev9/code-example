<?php

namespace App\Http\Transformers\Auth;

use Illuminate\Http\Request;

/**
 * Class AuthUserTransformer
 * @package App\Http\Transformers\Auth
 */
class AuthUserTransformer
{
    public function isEmail($request)
    {
        return strpos($request->login, '@') !== false;
    }

    public function getPhoneCredentials($request)
    {
        return [
            'phone' => preg_replace('/\D+/', '', $request->login),
            'password' => $request->password,
        ];
    }

    public function getEmailCredentials($request)
    {
        return [
            'email' => trim(strtolower($request->login)),
            'password' => $request->password,
        ];
    }

    public function getCredentials(Request $request)
    {
        if ($this->isEmail($request)) {
            return $this->getEmailCredentials($request);
        } else {
            return $this->getPhoneCredentials($request);
        }
    }

}
