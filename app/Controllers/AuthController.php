<?php

namespace App\Controllers;

use App\Services\IUserService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class AuthController
 * Controlador para manejo de autenticación
 */
class AuthController extends BaseController
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @param IUserService $userService
     * @param \Twig\Environment $twig
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     */
    public function __construct(
        IUserService $userService,
        \Twig\Environment $twig,
        IRequestValidator $validator,
        IAuthenticator $authenticator
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->userService = $userService;
    }

    /**
     * Muestra la página de inicio de sesión
     */
    public function loginPage(): void
    {
        if ($this->authenticator && $this->authenticator->isAuthenticated()) {
            header('Location: /');
            exit;
        }
        
        $this->render('auth/login.twig', [
            'pageTitle' => 'Iniciar Sesión'
        ]);
    }

    /**
     * Muestra la página de registro
     */
    public function registerPage(): void
    {
        if ($this->authenticator && $this->authenticator->isAuthenticated()) {
            header('Location: /');
            exit;
        }
        
        $this->render('auth/register.twig', [
            'pageTitle' => 'Registrarse'
        ]);
    }

    /**
     * Maneja el registro de usuarios
     */
    public function register(): void
    {
        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /auth/register');
            exit;
        }

        try {
            $rules = [
                'email' => ['required' => true, 'type' => 'string', 'email' => true],
                'password' => ['required' => true, 'type' => 'string', 'min' => 6],
                'passwordConfirm' => ['required' => true, 'type' => 'string'],
                'name' => ['required' => true, 'type' => 'string', 'min' => 2]
            ];

            $data = $_POST;

            if (!$this->validator->validate($data, $rules)) {
                $this->flash('error', implode(', ', $this->validator->getErrors()));
                header('Location: /auth/register');
                exit;
            }

            // Validar que las contraseñas coincidan
            if ($data['password'] !== $data['passwordConfirm']) {
                $this->flash('error', 'Las contraseñas no coinciden');
                header('Location: /auth/register');
                exit;
            }

            // Eliminar el campo de confirmación antes de procesar el registro
            unset($data['passwordConfirm']);

            $user = $this->userService->register($data);
            
            // Autenticar al usuario después del registro si está activo
            if ($user->isActive()) {
                $this->authenticator->authenticate($data['email'], $data['password']);
            }
            
            // Preparar respuesta sin datos sensibles
            $userData = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName()
            ];
            
            $this->flash('success', '¡Registro exitoso! Ya puedes iniciar sesión.');
            header('Location: /auth/login');
            exit;

        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /auth/register');
            exit;
        } catch (\Exception $e) {
            $this->flash('error', 'Ocurrió un error al procesar tu registro. Por favor, intenta nuevamente.');
            header('Location: /auth/register');
            exit;
        }
    }

    /**
     * Maneja el inicio de sesión
     */
    public function login(): void
    {
        if (!$this->isMethod('POST')) {
            if ($this->isApiRequest()) {
                $this->error('Método no permitido', 405);
            } else {
                $this->render('auth/login.twig', [
                    'error' => 'Método no permitido'
                ]);
            }
            return;
        }

        $isApi = $this->isApiRequest();
        
        // Intentar obtener datos de diferentes fuentes
        if ($isApi) {
            $data = $this->getJsonRequest();
        } else {
            $data = $_POST;
            
            // Sanitizar datos del formulario
            $data = array_map('trim', $data);
        }
        
        // Reglas de validación para login
        $rules = [
            'email' => ['required' => true, 'type' => 'string', 'email' => true],
            'password' => ['required' => true, 'type' => 'string', 'min' => 6]
        ];

        if (!$this->validator->validate($data, $rules)) {
            $errors = $this->validator->getErrors();
            if ($isApi) {
                $this->error('Errores de validación: ' . implode(', ', $errors));
            } else {
                $this->flash('error', implode(', ', $errors));
                header('Location: /auth/login');
                exit;
            }
            return;
        }

        try {
            if (empty($data['email']) || empty($data['password'])) {
                if ($isApi) {
                    $this->error('Email y contraseña son requeridos', 400);
                } else {
                    $this->flash('error', 'Por favor ingrese su email y contraseña');
                    header('Location: /auth/login');
                    exit;
                }
                return;
            }

            // Log para depuración
            error_log("Intento de login para email: " . $data['email']);
            
            if (!$this->authenticator->authenticate($data['email'], $data['password'])) {
                if ($isApi) {
                    $this->error('Credenciales inválidas', 401);
                } else {
                    $this->flash('error', 'Email o contraseña incorrectos');
                    header('Location: /auth/login');
                    exit;
                }
                return;
            }

            $userData = $this->authenticator->getAuthenticatedUser();
            
            if ($isApi) {
                $this->success($userData, 'Inicio de sesión exitoso', 200);
            } else {
                $this->flash('success', '¡Bienvenido de nuevo!');
                header('Location: /');
                exit;
            }
            
        } catch (\RuntimeException $e) {
            if ($isApi) {
                $this->error($e->getMessage(), 401);
            } else {
                $this->render('auth/login.twig', [
                    'pageTitle' => 'Iniciar Sesión',
                    'error' => $e->getMessage(),
                    'oldInput' => ['email' => $data['email']]
                ]);
            }
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void
    {
        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /');
            exit;
        }

        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'No hay sesión activa');
            header('Location: /');
            exit;
        }

        $this->authenticator->logout();
        $this->flash('success', '¡Hasta pronto!');
        header('Location: /');
        exit;
    }
}
