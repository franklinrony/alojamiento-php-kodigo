<?php

namespace App\Utilities;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtensions extends AbstractExtension
{
    private $authenticator;

    public function __construct(?IAuthenticator $authenticator = null)
    {
        $this->authenticator = $authenticator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('flash', [$this, 'getFlashMessages']),
            new TwigFunction('url', [$this, 'generateUrl']),
            new TwigFunction('path_for', [$this, 'generateUrl']), // Alias para path_for
            new TwigFunction('auth', [$this, 'getAuth']),
        ];
    }

    public function getFlashMessages()
    {
        $messages = [];
        
        if (isset($_SESSION['flash'])) {
            $messages = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        
        return $messages;
    }

    public function generateUrl(string $path): string
    {
        // Asegurarse de que la ruta comience con /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Obtener el esquema (http/https)
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        
        // Obtener el host
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
        
        // Si estamos en desarrollo y no hay host, usar localhost
        if (empty($host) && ($_ENV['APP_ENV'] ?? 'development') === 'development') {
            $host = 'localhost';
        }
        
        // Construir la URL base
        $baseUrl = $scheme . '://' . $host;
        
        // Si hay un subdirectorio en la instalación, agregarlo
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        if ($baseDir !== '/' && $baseDir !== '\\') {
            $baseUrl .= $baseDir;
        }
        
        // Combinar con la ruta solicitada
        return rtrim($baseUrl, '/') . $path;
    }

    public function getAuth(): array
    {
        if ($this->authenticator === null) {
            return [
                'isAuthenticated' => false,
                'user' => null,
                'hasPermission' => function($permission) { return false; }
            ];
        }
        
        return [
            'isAuthenticated' => $this->authenticator->isAuthenticated(),
            'user' => $this->getUser(),
            'hasPermission' => [$this, 'hasPermission']
        ];
    }

    private function getUser(): ?array
    {
        if ($this->authenticator === null || !$this->authenticator->isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role_id' => $_SESSION['user_role'] ?? null,
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->authenticator === null || !$this->authenticator->isAuthenticated() || empty($_SESSION['permissions'])) {
            return false;
        }

        return in_array($permission, $_SESSION['permissions'], true);
    }
}
