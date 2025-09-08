<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\IAccommodationService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

class AccommodationController extends BaseController
{
    private $accommodationService;

    public function __construct(
        \Twig\Environment $twig,
        IRequestValidator $validator,
        IAuthenticator $authenticator,
        IAccommodationService $accommodationService
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->accommodationService = $accommodationService;
    }

    /**
     * Muestra el formulario para crear un nuevo alojamiento
     */
    public function create()
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->hasRole('admin')) {
            $this->error('No tienes permisos para acceder a esta sección', 403);
            return;
        }

        echo $this->twig->render('admin/accommodations/create.twig');
    }

    /**
     * Procesa la creación de un nuevo alojamiento
     */
    public function store()
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->hasRole('admin')) {
            $this->error('No tienes permisos para acceder a esta sección', 403);
            return;
        }

        // Validar campos requeridos
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['string'],
            'location' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0']
        ];

        $data = $this->validator->validate($_POST, $rules);
        if (!$data) {
            $this->error('Datos inválidos', 422);
            return;
        }

        try {
            // Agregar el ID del usuario que crea el alojamiento
            $data['created_by'] = $this->authenticator->getUserId();
            
            // Crear el alojamiento
            $accommodation = $this->accommodationService->create($data);

            // Guardar mensaje de éxito en la sesión
            $_SESSION['flash']['success'][] = 'Alojamiento creado exitosamente';

            // Redireccionar a la lista de alojamientos
            header('Location: /admin/accommodations');
            exit;
        } catch (\Exception $e) {
            $this->error('Error al crear el alojamiento: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la lista de todos los alojamientos
     */
    public function index()
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->hasRole('admin')) {
            $this->error('No tienes permisos para acceder a esta sección', 403);
            return;
        }

        try {
            $accommodations = $this->accommodationService->getAllAccommodations();
            echo $this->twig->render('admin/accommodations/index.twig', [
                'accommodations' => $accommodations
            ]);
        } catch (\Exception $e) {
            $this->error('Error al obtener los alojamientos: ' . $e->getMessage());
        }
    }
}
