<?php

namespace App\Http\Middleware;

use App\Support\AdminShell;
use App\Support\PerPage;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shellProps = AdminShell::pageProps($request);

        return [
            ...parent::share($request),
            'branding' => [
                'cssPath' => config('branding.css_path'),
                'tokens' => [
                    'blue' => '#1f4ea7',
                    'green' => '#2e7d32',
                    'gold' => '#ffd700',
                    'maroon' => '#932c27',
                ],
            ],
            'routeName' => fn () => $shellProps['routeName'],
            'auth' => fn () => $shellProps['auth'],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'adminActivity' => fn () => $shellProps['adminActivity'],
            'perPageOptions' => PerPage::options(),
        ];
    }
}
