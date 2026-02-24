<?php

namespace App\Support;

use Native\Laravel\Facades\SecureStorage;

class AuthStorage
{
    public static function set(string $key, mixed $value): void
    {
        if (self::isNative()) {
            SecureStorage::set($key, $value);
        } else {
            session([$key => $value]);
        }
    }

    public static function get(string $key): mixed
    {
        return self::isNative()
            ? SecureStorage::get($key)
            : session($key);
    }

    public static function forget(string $key): void
    {
        if (self::isNative()) {
            SecureStorage::forget($key);
        } else {
            session()->forget($key);
        }
    }

    protected static function isNative(): bool
    {
        return class_exists(SecureStorage::class);
    }

    public static function isLoggedIn(): bool
    {
        return !is_null(self::get('auth_token'));
    }
}
