<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureDeveloper;
use App\Models\User;
use App\Services\Auth\ModuleAccessService;
use App\Support\AdminShell;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DeveloperRoleTest extends TestCase
{
    public function test_user_role_helpers_and_name_accessor(): void
    {
        $user = new User([
            'fname' => 'Dev',
            'lname' => 'User',
            'role' => 'developer',
        ]);

        $this->assertTrue($user->hasRole('developer'));
        $this->assertTrue($user->hasAnyRole('admin', 'developer'));
        $this->assertTrue($user->hasAnyRole(['staff', 'developer']));
        $this->assertFalse($user->hasAnyRole(['admin', 'staff']));
        $this->assertSame('Dev User', $user->name);
    }

    public function test_developer_passes_middleware_and_active_module_is_set(): void
    {
        $request = $this->requestForRole('developer');
        $middleware = new EnsureDeveloper(new ModuleAccessService);

        $response = $middleware->handle($request, fn (): Response => new Response('ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('developer', $request->session()->get('active_module'));
    }

    #[DataProvider('nonDeveloperRoles')]
    public function test_non_developer_roles_receive_forbidden(?string $role): void
    {
        $request = $this->requestForRole($role);
        $middleware = new EnsureDeveloper(new ModuleAccessService);

        try {
            $middleware->handle($request, fn (): Response => new Response('should not run'));
            $this->fail('Expected the middleware to reject this role.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public static function nonDeveloperRoles(): array
    {
        return [
            'admin' => ['admin'],
            'staff' => ['staff'],
            'guest' => [null],
        ];
    }

    public function test_admin_shell_page_props_includes_developer_role_and_branding(): void
    {
        $user = new User([
            'fname' => 'Dev',
            'lname' => 'User',
            'role' => 'developer',
            'email' => 'developer@library.com',
        ]);

        $request = Request::create('/developer/dashboard', 'GET');
        $request->setUserResolver(fn (): User => $user);

        $route = (new Route('GET', '/developer/dashboard', fn () => null))->name('developer.dashboard');
        $request->setRouteResolver(fn (): Route => $route);

        $props = AdminShell::pageProps($request);

        $this->assertTrue($props['auth']['user']['isDeveloper']);
        $this->assertFalse($props['auth']['user']['isAdmin']);
        $this->assertSame('developer.dashboard', $props['routeName']);
        $this->assertArrayHasKey('shellBranding', $props);
        $this->assertArrayHasKey('sidebar_logo_url', $props['shellBranding']);
        $this->assertArrayHasKey('sidebar_brand_name', $props['shellBranding']);
        $this->assertArrayHasKey('sidebar_background_color', $props['shellBranding']);
    }

    private function requestForRole(?string $role): Request
    {
        $request = Request::create('/developer/example');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->setUserResolver(
            fn (): ?User => $role === null ? null : new User(['role' => $role]),
        );

        return $request;
    }
}
