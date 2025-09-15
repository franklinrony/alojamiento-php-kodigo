<?php

namespace App\Controllers;

use App\Services\IAccommodationService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class AccommodationController
 * Controlador para gestión de alojamientos
 */
class AccommodationController extends BaseController
{
    /**
     * @var IAccommodationService
     */
    private $accommodationService;

    /**
     * @param \Twig\Environment $twig
     * @param IAccommodationService $accommodationService
     * @param IRequestValidator|null $validator
     * @param IAuthenticator|null $authenticator
     */
    public function __construct(
        \Twig\Environment $twig,
        IAccommodationService $accommodationService,
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->accommodationService = $accommodationService;
    }

    /**
     * Lista todos los alojamientos con filtros opcionales y paginación
     */
    public function index(): void
    {
        $criteria = [];

        // Aplicar filtros si existen
        if ($location = $this->getParam('location')) {
            $criteria['location'] = $location;
        }

        if ($minPrice = $this->getParam('min_price')) {
            $criteria['minPrice'] = (float) $minPrice;
        }

        if ($maxPrice = $this->getParam('max_price')) {
            $criteria['maxPrice'] = (float) $maxPrice;
        }

        // Paginación
        $page = (int) ($this->getParam('page') ?? 1);
        $limit = 12; // 12 alojamientos por página
        $offset = ($page - 1) * $limit;

        $criteria['limit'] = $limit;
        $criteria['offset'] = $offset;

        $accommodations = $this->accommodationService->searchAccommodations($criteria);
        
        // Obtener total para paginación
        $totalAccommodations = $this->accommodationService->countAccommodations($criteria);
        $totalPages = ceil($totalAccommodations / $limit);
        
        $this->render('accommodations/index.twig', [
            'pageTitle' => 'Alojamientos Disponibles',
            'accommodations' => $accommodations,
            'filters' => $criteria,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalAccommodations,
                'items_per_page' => $limit,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo alojamiento
     */
    public function create(): void
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'Debes iniciar sesión para crear un alojamiento');
            header('Location: /auth/login');
            exit;
        }

        $this->render('accommodations/create.twig', [
            'pageTitle' => 'Crear Nuevo Alojamiento'
        ]);
    }

    /**
     * Procesa la creación de un nuevo alojamiento
     */
    public function store(): void
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /accommodations/create');
            exit;
        }

        $data = $_POST;
        
        if (!isset($data['name']) || !isset($data['location']) || !isset($data['price'])) {
            $this->flash('error', 'Datos incompletos');
            header('Location: /accommodations/create');
            exit;
        }

        try {
            $accommodation = $this->accommodationService->createAccommodation(
                $data,
                $this->getAuthUserId()
            );

            $this->flash('success', 'Alojamiento creado exitosamente');
            header('Location: /accommodations/' . $accommodation->getId());
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /accommodations/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un alojamiento
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        $accommodation = $this->accommodationService->getAccommodation($id);

        if (!$accommodation) {
            $this->flash('error', 'Alojamiento no encontrado');
            header('Location: /accommodations');
            exit;
        }

        // Verificar que el usuario es el propietario
        if ($accommodation->getCreatedBy() !== $this->getAuthUserId()) {
            $this->flash('error', 'No tienes permisos para editar este alojamiento');
            header('Location: /accommodations');
            exit;
        }

        $this->render('accommodations/edit.twig', [
            'pageTitle' => 'Editar Alojamiento',
            'accommodation' => $accommodation
        ]);
    }

    /**
     * Procesa la actualización de un alojamiento existente
     *
     * @param int $id
     */
    public function update(int $id): void
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'No autorizado');
            header('Location: /auth/login');
            exit;
        }

        if (!$this->isMethod('POST')) {
            $this->flash('error', 'Método no permitido');
            header('Location: /accommodations/' . $id . '/edit');
            exit;
        }

        try {
            $data = $_POST;
            $accommodation = $this->accommodationService->updateAccommodation(
                $id,
                $data,
                $this->getAuthUserId()
            );

            if (!$accommodation) {
                $this->flash('error', 'Alojamiento no encontrado');
                header('Location: /accommodations');
                exit;
            }

            $this->flash('success', 'Alojamiento actualizado exitosamente');
            header('Location: /accommodations/' . $id);
            exit;
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            header('Location: /accommodations/' . $id . '/edit');
            exit;
        }
    }


    /**
     * Muestra un alojamiento específico
     *
     * @param int $id
     */
    public function show(int $id): void
    {
        $accommodation = $this->accommodationService->getAccommodation($id);

        if (!$accommodation) {
            $this->flash('error', 'Alojamiento no encontrado');
            header('Location: /accommodations');
            exit;
        }

        $this->render('accommodations/show.twig', [
            'pageTitle' => $accommodation->getName(),
            'accommodation' => $accommodation
        ]);
    }
}
