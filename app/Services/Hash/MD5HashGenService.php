<?php

namespace App\Services\Hash;


class MD5HashGenService
{
    /**
     * @param $sum
     * @param $id
     * @param $pass
     * @return string
     */
    public function md5HashGen($sum, $id, $pass)
    {
        return strtoupper(md5("$sum:$id:$pass"));
    }
}
