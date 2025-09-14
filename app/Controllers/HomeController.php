<?php

namespace App\Controllers;

use App\Services\IAccommodationService;
use App\Utilities\IAuthenticator;
use App\Utilities\IRequestValidator;

/**
 * Class HomeController
 * Controlador para la página principal
 */
class HomeController extends BaseController
{
    /**
     * @var IAccommodationService
     */
    private $accommodationService;

    /**
     * Constructor del controlador
     *
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
     * Muestra la página principal
     */
    public function index(): void
    {
        $user = null;
        if ($this->authenticator && $this->authenticator->isAuthenticated()) {
            $user = $this->authenticator->getUser();
        }

        // Obtener solo los alojamientos necesarios para la landing page (optimizado)
        $criteria = ['limit' => 8, 'offset' => 0];
        $featuredAccommodations = $this->accommodationService->searchAccommodations($criteria);
        
        // Obtener alojamientos para ofertas de fin de semana (simular descuentos)
        $criteria = ['limit' => 4, 'offset' => 0];
        $weekendOffers = $this->accommodationService->searchAccommodations($criteria);

        $this->render('home/simple.twig', [
            'pageTitle' => 'Alojamientos - Encuentra tu próximo alojamiento',
            'user' => $user,
            'featuredAccommodations' => $featuredAccommodations,
            'weekendOffers' => $weekendOffers
        ]);
    }
}
