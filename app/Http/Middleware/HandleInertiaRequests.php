<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Ambil semua data setting dari database sekaligus
        $settings = Setting::pluck('value', 'key');

        return [
            ...parent::share($request),

            // Data User Login (Jangan dihapus)
            'auth' => [
                'user' => $request->user(),
            ],

            // Data Sistem Baru
            'system' => [
                'name' => $settings['site_name'] ?? 'Admin Panel',
                'address' => $settings['site_address'] ?? '-',
                'phone' => $settings['site_phone'] ?? '-',
                'logo' => isset($settings['site_logo'])
                             ? asset('storage/'.$settings['site_logo'])
                             : '/images/Lazismu.png', // Ganti dengan path logo default kamu
            ],
        ];
    }
}
