<?php

namespace App\Controllers;

use App\Services\IUserService;
use App\Services\IRoleService;
use App\Services\IReservationService;
use App\Services\IAccommodationService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class UserController
 * Controlador para gestión de usuarios
 */
class UserController extends BaseController
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * @var IReservationService
     */
    private $reservationService;

    /**
     * @var IAccommodationService
     */
    private $accommodationService;


    /**
     * @param \Twig\Environment $twig
     * @param IUserService $userService
     * @param IRoleService $roleService
     * @param IReservationService $reservationService
     * @param IAccommodationService $accommodationService
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     */
    public function __construct(
        \Twig\Environment $twig,
        IUserService $userService, 
        IRoleService $roleService,
        IReservationService $reservationService,
        IAccommodationService $accommodationService,
        IRequestValidator $validator,
        IAuthenticator $authenticator
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->reservationService = $reservationService;
        $this->accommodationService = $accommodationService;
    }

    /**
     * Muestra el perfil del usuario autenticado
     */
    public function profile(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para ver tu perfil');
            header('Location: /auth/login');
            exit;
        }

        $userId = $this->authenticator->getUserId();
        $user = $this->userService->getUser($userId);

        if (!$user) {
            $this->flash('error', 'Usuario no encontrado');
            header('Location: /');
            exit;
        }

        $this->render('user/profile.twig', [
            'pageTitle' => 'Mi Perfil',
            'user' => $user
        ]);
    }

    /**
     * Muestra el formulario para editar el perfil del usuario
     */
    public function editProfile(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para editar tu perfil');
            header('Location: /auth/login');
            exit;
        }

        $userId = $this->authenticator->getUserId();
        $user = $this->userService->getUser($userId);

        if (!$user) {
            $this->flash('error', 'Usuario no encontrado');
            header('Location: /');
            exit;
        }

        $this->render('user/edit-profile.twig', [
            'pageTitle' => 'Editar Perfil',
            'user' => $user
        ]);
    }

    /**
     * Procesa la actualización del perfil del usuario autenticado
     */
    public function updateProfile(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /user/profile/edit');
            exit;
        }

        $data = $_POST;
        
        // Reglas de validación para actualización de perfil
        $rules = [
            'name' => ['type' => 'string', 'min' => 2],
            'email' => ['type' => 'string', 'email' => true],
            'current_password' => ['type' => 'string', 'min' => 6],
            'new_password' => ['type' => 'string', 'min' => 6]
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->flash('error', 'Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            header('Location: /user/profile/edit');
            exit;
        }

        $data = $this->validator->sanitize($data);
        $userId = $this->authenticator->getUserId();

        try {
            $user = $this->userService->updateUser($userId, $data);
            
            if (!$user) {
                $this->flash('error', 'Error al actualizar el perfil');
                header('Location: /user/profile/edit');
                exit;
            }

            $this->flash('success', 'Perfil actualizado exitosamente');
            header('Location: /user/profile');
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /user/profile/edit');
            exit;
        }
    }

    /**
     * Muestra el formulario para asignar roles (solo admin)
     */
    public function assignRoleForm(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        // Verificar permiso de administración
        if (!$this->userService->hasPermission($this->authenticator->getUserId(), 'manage_users')) {
            $this->flash('error', 'No tienes permisos para realizar esta acción');
            header('Location: /');
            exit;
        }

        // For now, we'll need to implement these methods or use repositories directly
        // This is a placeholder - you may need to add these methods to the services
        $users = []; // $this->userService->getAllUsers();
        $roles = []; // $this->roleService->getAllRoles();

        $this->render('user/assign-role.twig', [
            'pageTitle' => 'Asignar Roles',
            'users' => $users,
            'roles' => $roles
        ]);
    }

    /**
     * Procesa la asignación de un rol a un usuario (solo admin)
     */
    public function assignRole(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /user/assign-role');
            exit;
        }

        // Verificar permiso de administración
        if (!$this->userService->hasPermission($this->authenticator->getUserId(), 'manage_users')) {
            $this->flash('error', 'No tienes permisos para realizar esta acción');
            header('Location: /');
            exit;
        }

        $data = $_POST;
        
        // Reglas de validación para asignación de rol
        $rules = [
            'user_id' => ['required' => true, 'type' => 'integer'],
            'role_id' => ['required' => true, 'type' => 'integer']
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->flash('error', 'Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            header('Location: /user/assign-role');
            exit;
        }

        try {
            $success = $this->userService->assignRole($data['user_id'], $data['role_id']);
            
            if (!$success) {
                $this->flash('error', 'Error al asignar el rol');
                header('Location: /user/assign-role');
                exit;
            }

            $this->flash('success', 'Rol asignado exitosamente');
            header('Location: /user/assign-role');
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /user/assign-role');
            exit;
        }
    }

    /**
     * Muestra las reservas del usuario autenticado
     */
    public function reservations(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para ver tus reservas');
            header('Location: /auth/login');
            exit;
        }

        $userId = $this->authenticator->getUserId();
        $reservations = $this->reservationService->getReservationsByUser($userId);
        $stats = $this->reservationService->getReservationStats($userId);

        $this->render('user/reservations.twig', [
            'pageTitle' => 'Mis Reservas',
            'reservations' => $reservations,
            'stats' => $stats
        ]);
    }

    /**
     * Muestra las reservas activas del usuario
     */
    public function activeReservations(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para ver tus reservas activas');
            header('Location: /auth/login');
            exit;
        }

        $userId = $this->authenticator->getUserId();
        $reservations = $this->reservationService->getActiveReservationsByUser($userId);

        $this->render('user/active-reservations.twig', [
            'pageTitle' => 'Reservas Activas',
            'reservations' => $reservations
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva reserva
     */
    public function createReservationForm(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para hacer una reserva');
            header('Location: /auth/login');
            exit;
        }

        $accommodationId = $_GET['accommodation_id'] ?? null;
        $accommodation = null;

        if ($accommodationId) {
            $accommodation = $this->accommodationService->getAccommodation($accommodationId);
            if (!$accommodation) {
                $this->flash('error', 'Alojamiento no encontrado');
                header('Location: /');
                exit;
            }
        }

        $this->render('user/create-reservation.twig', [
            'pageTitle' => 'Crear Reserva',
            'accommodation' => $accommodation
        ]);
    }

    /**
     * Procesa la creación de una nueva reserva
     */
    public function createReservation(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /user/reservations/create');
            exit;
        }

        $data = $_POST;
        $data['user_id'] = $this->authenticator->getUserId();

        // Reglas de validación para reserva
        $rules = [
            'accommodation_id' => ['required' => true, 'type' => 'integer'],
            'check_in_date' => ['required' => true, 'type' => 'string'],
            'check_out_date' => ['required' => true, 'type' => 'string'],
            'guests' => ['type' => 'integer', 'min' => 1, 'max' => 20]
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->flash('error', 'Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            header('Location: /user/reservations/create?accommodation_id=' . ($data['accommodation_id'] ?? ''));
            exit;
        }

        $data = $this->validator->sanitize($data);

        try {
            $reservation = $this->reservationService->createReservation($data);
            
            if (!$reservation) {
                $this->flash('error', 'Error al crear la reserva. Verifica que el alojamiento esté disponible para las fechas seleccionadas.');
                header('Location: /user/reservations/create?accommodation_id=' . $data['accommodation_id']);
                exit;
            }

            $this->flash('success', 'Reserva creada exitosamente');
            header('Location: /user/reservations');
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /user/reservations/create?accommodation_id=' . $data['accommodation_id']);
            exit;
        }
    }

    /**
     * Cancela una reserva existente
     */
    public function cancelReservation(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /user/reservations');
            exit;
        }

        $reservationId = $_POST['reservation_id'] ?? null;
        $userId = $this->authenticator->getUserId();

        if (!$reservationId) {
            $this->flash('error', 'ID de reserva requerido');
            header('Location: /user/reservations');
            exit;
        }

        try {
            $success = $this->reservationService->cancelReservation((int)$reservationId, $userId);
            
            if (!$success) {
                $this->flash('error', 'Error al cancelar la reserva. Verifica que la reserva exista y esté activa.');
                header('Location: /user/reservations');
                exit;
            }

            $this->flash('success', 'Reserva cancelada exitosamente');
            header('Location: /user/reservations');
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /user/reservations');
            exit;
        }
    }

    /**
     * Muestra los detalles de una reserva específica
     */
    public function reservationDetails(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para ver los detalles de la reserva');
            header('Location: /auth/login');
            exit;
        }

        $reservationId = $_GET['id'] ?? null;
        $userId = $this->authenticator->getUserId();

        if (!$reservationId) {
            $this->flash('error', 'ID de reserva requerido');
            header('Location: /user/reservations');
            exit;
        }

        $reservation = $this->reservationService->getReservationById((int)$reservationId, $userId);

        if (!$reservation) {
            $this->flash('error', 'Reserva no encontrada o no tienes permisos para verla');
            header('Location: /user/reservations');
            exit;
        }

        $this->render('user/reservation-details.twig', [
            'pageTitle' => 'Detalles de Reserva',
            'reservation' => $reservation
        ]);
    }
}
