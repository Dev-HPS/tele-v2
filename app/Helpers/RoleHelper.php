<?php

use Illuminate\Support\Arr;

if (!function_exists('user_has_role')) {
    function user_has_role(string|array $roles): bool
    {
        $ids = Arr::wrap($roles);
        $map = config('roles');
        $targetIds = array_map(fn($k) => $map[$k] ?? $k, $ids);
        return auth()->check() && in_array(auth()->user()->role->id, $targetIds, true);
    }
}
