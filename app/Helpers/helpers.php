<?php

if (! function_exists('getColorClass')) {
    function getColorClass($permissionName) {
        return match (true) {
            str_contains($permissionName, 'create') => 'success',
            str_contains($permissionName, 'read') => 'info',
            str_contains($permissionName, 'update') => 'warning',
            str_contains($permissionName, 'delete') => 'danger',
            default => 'primary',
        };
    }
}

if (! function_exists('getPermissionKey')) {
    function getPermissionKey($permissionName) {
        return str_replace(['create ', 'read ', 'update ', 'delete '], '', $permissionName);
    }
}
