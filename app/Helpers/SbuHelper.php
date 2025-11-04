<?php

namespace App\Helpers;

class SbuHelper
{
    public static function getSbuData()
    {
        return collect([
            [
                'sbu_code' => 'MAB',
                'sbu_name' => 'PT. Mitra Abadi Bahari',
            ],
            [
                'sbu_code' => 'NPA',
                'sbu_name' => 'PT. Niaga Permai Abadi',
            ],
            [
                'sbu_code' => 'HLJ',
                'sbu_name' => 'PT. Hasta Lestari Jaya',
            ],
        ]);
    }
}
