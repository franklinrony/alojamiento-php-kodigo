<?php

namespace Tests\Functional;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\IUserService;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class AccommodationManagementTest extends TestCase
{
    private $client;
    private $cookieJar;
    private $userService;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080',
            'http_errors' => false
        ]);
        $this->cookieJar = new CookieJar();
        $this->userService = $this->getContainer()->get(IUserService::class);
    }

    public function testNonAdminCannotAccessAddAccommodationPage()
    {
        // Crear usuario regular
        $user = $this->createUser(false);
        
        // Iniciar sesión
        $this->login($user->email, 'password');

        // Intentar acceder a la página de agregar alojamiento
        $response = $this->client->get('/admin/accommodation/add', [
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAdminCanAccessAddAccommodationPage()
    {
        // Crear usuario admin
        $user = $this->createUser(true);
        
        // Iniciar sesión
        $this->login($user->email, 'password');

        // Acceder a la página de agregar alojamiento
        $response = $this->client->get('/admin/accommodation/add', [
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Agregar Alojamiento', (string) $response->getBody());
    }

    public function testAdminCanAddAccommodation()
    {
        // Crear usuario admin
        $user = $this->createUser(true);
        
        // Iniciar sesión
        $this->login($user->email, 'password');

        // Agregar un alojamiento
        $response = $this->client->post('/admin/accommodation/add', [
            'cookies' => $this->cookieJar,
            'form_params' => [
                'name' => 'Test Accommodation',
                'description' => 'Test Description',
                'location' => 'Test Location',
                'price' => '100'
            ]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/admin/accommodations', $response->getHeader('Location')[0]);
    }

    private function createUser(bool $isAdmin): User
    {
        $role = new Role();
        $role->name = $isAdmin ? 'admin' : 'user';
        
        if ($isAdmin) {
            $permission = new Permission();
            $permission->name = 'manage-accommodations';
            $role->permissions = [$permission];
        }

        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test' . uniqid() . '@example.com';
        $user->password = password_hash('password', PASSWORD_DEFAULT);
        $user->role = $role;

        return $this->userService->createUser($user);
    }

    private function login(string $email, string $password): void
    {
        $this->client->post('/auth/login', [
            'cookies' => $this->cookieJar,
            'form_params' => [
                'email' => $email,
                'password' => $password
            ]
        ]);
    }

    private function getContainer()
    {
        require __DIR__ . '/../../bootstrap/container.php';
        return $container;
    }
}
