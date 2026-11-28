<?php

namespace App\Services\Homepage;

final class HomepageSectionRegistry
{
    /**
     * Daftar section homepage yang diizinkan oleh aplikasi.
     *
     * Urutan menggunakan kelipatan 10 agar penambahan section
     * di antara section yang sudah ada tetap memungkinkan.
     *
     * @var array<string, array{
     *     key: string,
     *     default_enabled: bool,
     *     default_sort_order: int,
     *     config_version: int,
     *     default_settings: array<string, mixed>
     * }>
     */
    private const SECTIONS = [
        'hero' => [
            'key' => 'hero',
            'default_enabled' => true,
            'default_sort_order' => 10,
            'config_version' => 1,
            'default_settings' => [],
        ],
        'services' => [
            'key' => 'services',
            'default_enabled' => true,
            'default_sort_order' => 20,
            'config_version' => 1,
            'default_settings' => [],
        ],
        'news' => [
            'key' => 'news',
            'default_enabled' => true,
            'default_sort_order' => 30,
            'config_version' => 1,
            'default_settings' => [],
        ],
        'agenda' => [
            'key' => 'agenda',
            'default_enabled' => true,
            'default_sort_order' => 40,
            'config_version' => 1,
            'default_settings' => [],
        ],
        'publications' => [
            'key' => 'publications',
            'default_enabled' => true,
            'default_sort_order' => 50,
            'config_version' => 1,
            'default_settings' => [],
        ],
        'profile' => [
            'key' => 'profile',
            'default_enabled' => true,
            'default_sort_order' => 60,
            'config_version' => 1,
            'default_settings' => [],
        ],
    ];

    /**
     * Mengambil seluruh definisi section yang didukung.
     *
     * @return array<string, array{
     *     key: string,
     *     default_enabled: bool,
     *     default_sort_order: int,
     *     config_version: int,
     *     default_settings: array<string, mixed>
     * }>
     */
    public function all(): array
    {
        return self::SECTIONS;
    }

    /**
     * Mengambil definisi section berdasarkan key.
     *
     * @return array{
     *     key: string,
     *     default_enabled: bool,
     *     default_sort_order: int,
     *     config_version: int,
     *     default_settings: array<string, mixed>
     * }|null
     */
    public function get(string $key): ?array
    {
        return self::SECTIONS[$key] ?? null;
    }

    /**
     * Memeriksa apakah key merupakan section yang didukung.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, self::SECTIONS);
    }
}
