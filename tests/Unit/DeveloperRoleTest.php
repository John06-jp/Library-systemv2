<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureDeveloper;
use App\Models\User;
use App\Services\Auth\ModuleAccessService;
use Illuminate\Http\Request;
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
