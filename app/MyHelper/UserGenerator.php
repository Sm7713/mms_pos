<?php

namespace App\MyHelper;

use Illuminate\Support\Str;
class UserGenerator
{
    private static $randomString = '';

    // ...

    private static function generateRandomString($length, $characters)
    {
        self::$randomString=null;
        if (empty(self::$randomString)) {
            self::$randomString = substr(str_shuffle(str_repeat($characters,$length)),0,$length);
        }

        return self::$randomString;
    }

    public static function randLC($length)
    {
        return self::generateRandomString($length, 'abcdefghijklmnopqrstuvwxyz');
    }

    public static function randUC($length)
    {
        return self::generateRandomString($length, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public static function randULC($length)
    {
        return self::generateRandomString($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public static function randNLC($length)
    {
        return self::generateRandomString($length, '0123456789abcdefghijklmnopqrstuvwxyz');
    }

    public static function randNUC($length)
    {
        return self::generateRandomString($length, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public static function randNULC($length)
    {
        return self::generateRandomString($length, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public static function randN($length)
    {
        return self::generateRandomString($length, '0123456789');
    }

    // ...
}