<?php

namespace App\Helpers;

use Carbon\Carbon;
use File;

class CustomHelper
{

    public static function parseDate($date, $time = false)
    {
        if ($time) {
            return Carbon::parse($date)->translatedFormat('l, d F Y H:i:s');
        }

        $result = Carbon::parse($date)->translatedFormat('l, d F Y');
        return $result;
    }

    public static function numberToRoman($num)
    {
        // Be sure to convert the given parameter into an integer
        $n = intval($num);
        $result = '';

        // Declare a lookup array that we will use to traverse the number:
        $lookup = array(
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        );

        foreach ($lookup as $roman => $value) {
            // Look for number of matches
            $matches = intval($n / $value);

            // Concatenate characters
            $result .= str_repeat($roman, $matches);

            // Substract that from the number
            $n = $n % $value;
        }

        return $result;
    }

    public static function getHariSeninSampaiSabtu()
    {
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    }

    public static function productURL($placeholder = 'default')
    {
        $productImage = 'https://image.dmlt-mob-online.com/dsol-grosir/product-images/' . $placeholder;

        $defaultImage = 'http://www.tea-tron.com/antorodriguez/blog/wp-content/uploads/2016/04/image-not-found-4a963b95bf081c3ea02923dceaeb3f8085e1a654fc54840aac61a57a60903fef.png';

        if ($placeholder == 'default') {
            return $defaultImage;
        }

        return $productImage;
    }
}
