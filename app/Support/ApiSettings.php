<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ApiSettings
{
    public static function defaults(): array
    {
        return [
            'auth_mode' => 'basic',
            'basic_username' => '',
            'basic_password_hash' => '',
            'updated_at' => null,
        ];
    }

    public static function current(): array
    {
        return array_merge(static::defaults(), static::read());
    }

    public static function save(array $attributes): array
    {
        $current = static::current();
        $mode = static::normalizeMode((string) ($attributes['auth_mode'] ?? $current['auth_mode']));
        $username = trim((string) ($attributes['basic_username'] ?? $current['basic_username']));
        $password = (string) ($attributes['basic_password'] ?? '');

        $payload = [
            'auth_mode' => $mode,
            'basic_username' => $username,
            'basic_password_hash' => $current['basic_password_hash'] ?? '',
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($password !== '') {
            $payload['basic_password_hash'] = Hash::make($password);
        }

        if ($username === '') {
            $payload['basic_password_hash'] = '';
        }

        static::write($payload);

        return static::current();
    }

    public static function normalizeMode(string $mode): string
    {
        return in_array($mode, ['basic', 'token'], true) ? $mode : 'basic';
    }

    public static function hasStaticBasicCredential(array $settings): bool
    {
        return trim((string) ($settings['basic_username'] ?? '')) !== ''
            && trim((string) ($settings['basic_password_hash'] ?? '')) !== '';
    }

    public static function staticBasicCredentialMatches(array $settings, string $username, string $password): bool
    {
        return hash_equals(trim((string) ($settings['basic_username'] ?? '')), trim($username))
            && Hash::check($password, (string) ($settings['basic_password_hash'] ?? ''));
    }

    private static function read(): array
    {
        $path = static::path();

        if (!File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function write(array $payload): void
    {
        File::ensureDirectoryExists(dirname(static::path()));
        File::put(static::path(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private static function path(): string
    {
        return storage_path('app/settings/api.json');
    }
}
