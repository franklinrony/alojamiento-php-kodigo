<?php

namespace App\Controllers;

use App\Services\IUserService;
use App\Services\IRoleService;
use App\Services\IReservationService;
use App\Services\IAccommodationService;
use App\Services\ILoggerService;
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
     * @var ILoggerService
     */
    private $logger;

    /**
     * @param \Twig\Environment $twig
     * @param IUserService $userService
     * @param IRoleService $roleService
     * @param IReservationService $reservationService
     * @param IAccommodationService $accommodationService
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     * @param ILoggerService $logger
     */
    public function __construct(
        \Twig\Environment $twig,
        IUserService $userService, 
        IRoleService $roleService,
        IReservationService $reservationService,
        IAccommodationService $accommodationService,
        IRequestValidator $validator,
        IAuthenticator $authenticator,
        ILoggerService $logger
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->reservationService = $reservationService;
        $this->accommodationService = $accommodationService;
        $this->logger = $logger;
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
            header('Location: /profile');
            exit;
        }

        $data = $_POST;
        
        // Reglas de validación para actualización de perfil
        $rules = [
            'name' => ['type' => 'string', 'min' => 2],
            'email' => ['type' => 'string', 'email' => true],
            'password' => ['type' => 'string', 'min' => 8, 'optional' => true],
            'passwordConfirm' => ['type' => 'string', 'optional' => true]
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->flash('error', 'Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            header('Location: /profile');
            exit;
        }

        // Validación adicional para contraseñas
        if (!empty($data['password']) || !empty($data['passwordConfirm'])) {
            if (empty($data['password']) || empty($data['passwordConfirm'])) {
                $this->flash('error', 'Debes completar ambos campos de contraseña');
                header('Location: /profile');
                exit;
            }
            
            if ($data['password'] !== $data['passwordConfirm']) {
                $this->flash('error', 'Las contraseñas no coinciden');
                header('Location: /profile');
                exit;
            }
        }

        $data = $this->validator->sanitize($data);
        $userId = $this->authenticator->getUserId();

        // Preparar datos para actualización
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email']
        ];

        // Si se proporciona una nueva contraseña, incluirla hasheada
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        try {
            $user = $this->userService->updateUser($userId, $updateData);
            
            if (!$user) {
                $this->flash('error', 'Error al actualizar el perfil');
                header('Location: /profile');
                exit;
            }

            $this->flash('success', 'Perfil actualizado exitosamente');
            header('Location: /profile');
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /profile');
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

        try {
            $userId = $this->authenticator->getUserId();
            
            // Obtener las reservas con manejo de errores
            try {
                $reservations = $this->reservationService->getReservationsByUser($userId);
            } catch (\Exception $e) {
                $this->logger->error("Error al obtener reservas: " . $e->getMessage());
                $reservations = [];
            }
            
            // Obtener las estadísticas con manejo de errores
            try {
                $stats = $this->reservationService->getReservationStats($userId);
            } catch (\Exception $e) {
                $this->logger->error("Error al obtener estadísticas: " . $e->getMessage());
                $stats = [
                    'total_reservations' => 0,
                    'active_reservations' => 0,
                    'cancelled_reservations' => 0,
                    'completed_reservations' => 0,
                    'total_spent_active' => 0,
                    'total_spent_all' => 0
                ];
            }

            $this->render('user/reservations.twig', [
                'pageTitle' => 'Mis Reservas',
                'reservations' => $reservations,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Error general en reservations: " . $e->getMessage());
            $this->flash('error', 'Ha ocurrido un error al cargar las reservas. Por favor, inténtelo de nuevo.');
            header('Location: /');
            exit;
        }
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

        // Obtener todos los alojamientos para el dropdown
        try {
            $allAccommodations = $this->accommodationService->getAllAccommodations();
            $this->logger->info("Accommodations loaded for reservation form", [
                'count' => count($allAccommodations),
                'accommodations' => array_map(function($acc) {
                    return ['id' => $acc->getId(), 'name' => $acc->getName(), 'location' => $acc->getLocation(), 'price' => $acc->getPrice()];
                }, $allAccommodations)
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Error al obtener alojamientos para formulario de reserva: " . $e->getMessage());
            $allAccommodations = [];
            $this->flash('error', 'Error al cargar los alojamientos. Por favor, inténtelo de nuevo.');
        }

        $this->render('user/create-reservation.twig', [
            'pageTitle' => 'Crear Reserva',
            'accommodation' => $accommodation,
            'allAccommodations' => $allAccommodations
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
                $this->flash('error', 'Error al crear la reserva. Verifica los datos ingresados e intenta nuevamente.');
                header('Location: /user/reservations/create?accommodation_id=' . $data['accommodation_id']);
                exit;
            }

            $this->flash('success', 'Reserva creada exitosamente');
            header('Location: /user/reservations');
            exit;
        } catch (\RuntimeException $e) {
            $this->logger->error("Error creating reservation: " . $e->getMessage(), [
                'user_id' => $data['user_id'],
                'accommodation_id' => $data['accommodation_id'],
                'data' => $data
            ]);
            $this->flash('error', 'Error al crear la reserva: ' . $e->getMessage());
            header('Location: /user/reservations/create?accommodation_id=' . $data['accommodation_id']);
            exit;
        }
    }

    /**
     * Verifica disponibilidad de un alojamiento (API endpoint)
     */
    public function checkAvailability(int $accommodationId): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $checkIn = $_GET['check_in'] ?? null;
        $checkOut = $_GET['check_out'] ?? null;

        if (!$checkIn || !$checkOut) {
            http_response_code(400);
            echo json_encode(['error' => 'Fechas de entrada y salida requeridas']);
            exit;
        }

        try {
            $available = $this->reservationService->isAccommodationAvailable($accommodationId, $checkIn, $checkOut);
            
            header('Content-Type: application/json');
            echo json_encode([
                'available' => $available,
                'accommodation_id' => $accommodationId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'message' => $available ? 'Disponible' : 'No disponible para las fechas seleccionadas'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Error checking availability: " . $e->getMessage(), [
                'accommodation_id' => $accommodationId,
                'check_in' => $checkIn,
                'check_out' => $checkOut
            ]);
            
            http_response_code(500);
            echo json_encode(['error' => 'Error al verificar disponibilidad']);
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
