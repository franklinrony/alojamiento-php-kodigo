<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\IAccommodationService;
use App\Services\IUserService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

class AccommodationController extends BaseController
{
    private $accommodationService;
    private $userService;

    public function __construct(
        \Twig\Environment $twig,
        IRequestValidator $validator,
        IAuthenticator $authenticator,
        IAccommodationService $accommodationService,
        IUserService $userService
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->accommodationService = $accommodationService;
        $this->userService = $userService;
    }

    /**
     * Muestra el formulario para crear un nuevo alojamiento
     */
    public function create()
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->isAuthenticated() || !$this->userService->hasPermission($this->authenticator->getUserId(), 'manage-accommodations')) {
            $this->flash('error', 'No tienes permisos para acceder a esta sección');
            header('Location: /');
            exit;
        }

        $this->render('admin/accommodations/create.twig', [
            'pageTitle' => 'Crear Alojamiento - Admin'
        ]);
    }

    /**
     * Procesa la creación de un nuevo alojamiento
     */
    public function store()
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->isAuthenticated() || !$this->userService->hasPermission($this->authenticator->getUserId(), 'manage-accommodations')) {
            $this->flash('error', 'No tienes permisos para acceder a esta sección');
            header('Location: /');
            exit;
        }

        // Validar campos requeridos
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['string'],
            'location' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0']
        ];

        if (!$this->validator->validate($_POST, $rules)) {
            $this->flash('error', 'Datos inválidos');
            header('Location: /admin/accommodations/create');
            exit;
        }

        try {
            $data = $_POST;
            // Agregar el ID del usuario que crea el alojamiento
            $data['created_by'] = $this->authenticator->getUserId();
            
            // Crear el alojamiento
            $accommodation = $this->accommodationService->createAccommodation($data, $this->authenticator->getUserId());

            $this->flash('success', 'Alojamiento creado exitosamente');
            header('Location: /admin/accommodations');
            exit;
        } catch (\Exception $e) {
            $this->flash('error', 'Error al crear el alojamiento: ' . $e->getMessage());
            header('Location: /admin/accommodations/create');
            exit;
        }
    }

    /**
     * Muestra la lista de todos los alojamientos
     */
    public function index()
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->isAuthenticated() || !$this->userService->hasPermission($this->authenticator->getUserId(), 'manage-accommodations')) {
            $this->flash('error', 'No tienes permisos para acceder a esta sección');
            header('Location: /');
            exit;
        }

        try {
            $accommodations = $this->accommodationService->getAllAccommodations();
            $this->render('admin/accommodations/index.twig', [
                'pageTitle' => 'Gestionar Alojamientos - Admin',
                'accommodations' => $accommodations
            ]);
        } catch (\Exception $e) {
            $this->flash('error', 'Error al obtener los alojamientos: ' . $e->getMessage());
            header('Location: /admin');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un alojamiento
     */
    public function edit(int $id)
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->isAuthenticated() || !$this->userService->hasPermission($this->authenticator->getUserId(), 'manage-accommodations')) {
            $this->flash('error', 'No tienes permisos para acceder a esta sección');
            header('Location: /');
            exit;
        }

        try {
            $accommodation = $this->accommodationService->getAccommodation($id);
            
            if (!$accommodation) {
                $this->flash('error', 'Alojamiento no encontrado');
                header('Location: /admin/accommodations');
                exit;
            }

            $this->render('admin/accommodations/edit.twig', [
                'pageTitle' => 'Editar Alojamiento - Admin',
                'accommodation' => $accommodation
            ]);
        } catch (\Exception $e) {
            $this->flash('error', 'Error al obtener el alojamiento: ' . $e->getMessage());
            header('Location: /admin/accommodations');
            exit;
        }
    }

    /**
     * Procesa la actualización de un alojamiento existente
     */
    public function update(int $id)
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->isAuthenticated() || !$this->userService->hasPermission($this->authenticator->getUserId(), 'manage-accommodations')) {
            $this->flash('error', 'No tienes permisos para acceder a esta sección');
            header('Location: /');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /admin/accommodations/' . $id . '/edit');
            exit;
        }

        // Validar campos requeridos
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['string'],
            'location' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0']
        ];

        if (!$this->validator->validate($_POST, $rules)) {
            $this->flash('error', 'Datos inválidos');
            header('Location: /admin/accommodations/' . $id . '/edit');
            exit;
        }

        try {
            $data = $_POST;
            
            // Actualizar el alojamiento
            $accommodation = $this->accommodationService->updateAccommodation($id, $data, $this->authenticator->getUserId());

            if (!$accommodation) {
                $this->flash('error', 'Alojamiento no encontrado');
                header('Location: /admin/accommodations');
                exit;
            }

            $this->flash('success', 'Alojamiento actualizado exitosamente');
            header('Location: /admin/accommodations');
            exit;
        } catch (\Exception $e) {
            $this->flash('error', 'Error al actualizar el alojamiento: ' . $e->getMessage());
            header('Location: /admin/accommodations/' . $id . '/edit');
            exit;
        }
    }
}
