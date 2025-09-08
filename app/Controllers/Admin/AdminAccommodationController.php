<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\IAccommodationService;
use App\Models\User;
use App\Utilities\IAuthenticator;
use App\Utilities\IRequestValidator;

class AdminAccommodationController extends BaseController
{
    private $accommodationService;

    public function __construct(
        \Twig\Environment $twig,
        IAccommodationService $accommodationService,
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->accommodationService = $accommodationService;
    }

    public function showAddForm(): void
    {
        $this->render('admin/accommodation/add-simple.twig', [
            'pageTitle' => 'Agregar Alojamiento - Alojamientos'
        ]);
    }

    public function index(): void
    {
        $accommodations = $this->accommodationService->getAllAccommodations();
        $this->render('admin/accommodation/index-simple.twig', [
            'pageTitle' => 'Gestionar Alojamientos - Alojamientos',
            'accommodations' => $accommodations
        ]);
    }

    public function add(): void
    {
        $data = $_POST;
        
        // Validar los datos de entrada
        $validation = $this->validateAccommodationData($data);
        if (!$validation['isValid']) {
            $_SESSION['error'] = $validation['message'];
            header('Location: /admin/accommodation/add');
            exit();
        }

        try {
            $userId = $_SESSION['user_id'];
            $this->accommodationService->createAccommodation($data, $userId);
            $_SESSION['success'] = 'Alojamiento agregado exitosamente.';
            header('Location: /admin/accommodations');
            exit();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al agregar el alojamiento: ' . $e->getMessage();
            header('Location: /admin/accommodation/add');
            exit();
        }
    }

    private function validateAccommodationData(array $data): array
    {
        $requiredFields = ['name', 'description', 'location', 'price'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return [
                    'isValid' => false,
                    'message' => "El campo {$field} es requerido."
                ];
            }
        }

        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            return [
                'isValid' => false,
                'message' => 'El precio debe ser un número positivo.'
            ];
        }

        return ['isValid' => true];
    }
}
