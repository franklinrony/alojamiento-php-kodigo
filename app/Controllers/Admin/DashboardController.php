<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\IAccommodationService;
use App\Services\IUserService;
use App\Utilities\IAuthenticator;
use App\Utilities\IRequestValidator;

class DashboardController extends BaseController
{
    private $accommodationService;
    private $userService;

    public function __construct(
        \Twig\Environment $twig,
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null,
        ?IAccommodationService $accommodationService = null,
        ?IUserService $userService = null
    ) {
        parent::__construct($twig, $validator, $authenticator);
        $this->accommodationService = $accommodationService;
        $this->userService = $userService;
    }

    public function index(): void
    {
        // Verificar que el usuario es administrador
        if (!$this->authenticator->isAuthenticated() || !$this->userService->hasPermission($this->authenticator->getUserId(), 'manage-accommodations')) {
            $this->flash('error', 'No tienes permisos para acceder a esta sección');
            header('Location: /');
            exit;
        }

        try {
            $accommodations = $this->accommodationService->getAllAccommodations();
            
            // Calcular estadísticas
            $totalAccommodations = count($accommodations);
            $averagePrice = 0;
            
            if (!empty($accommodations)) {
                $totalPrice = array_sum(array_map(function($acc) { return $acc->getPrice(); }, $accommodations));
                $averagePrice = $totalPrice / count($accommodations);
            }

            $this->render('admin/dashboard-simple.twig', [
                'pageTitle' => 'Panel de Administración - Alojamientos',
                'accommodations' => $accommodations,
                'totalAccommodations' => $totalAccommodations,
                'averagePrice' => $averagePrice
            ]);
        } catch (\Exception $e) {
            $this->flash('error', 'Error al obtener los datos: ' . $e->getMessage());
            $this->render('admin/dashboard-simple.twig', [
                'pageTitle' => 'Panel de Administración - Alojamientos',
                'accommodations' => [],
                'totalAccommodations' => 0,
                'averagePrice' => 0
            ]);
        }
    }
}
