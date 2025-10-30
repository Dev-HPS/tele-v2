<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseService
{
    public function callStoredProcedure(string $procedureName, array $params = [], $sbu = null)
    {
        $sbuCode = auth()->user()->sbu_code ?? 'MAB';

        if ($sbu) {
            $sbuCode = $sbu;
        }

        if (!in_array($sbuCode, ['HKJ', 'HLJ', 'MAB', 'NPA'])) {
            return [
                'status' => false,
                'data' => 'Kode SBU tidak valid atau belum didukung.',
            ];
        }

        $connection = strtolower($sbuCode);

        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $query = "CALL $procedureName($placeholders)";
            $result = DB::connection($connection)->select($query, $params);

            return [
                'status' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('SP ERROR: ' . $e->getMessage());

            return [
                'status' => false,
                'data' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ];
        }
    }
}
