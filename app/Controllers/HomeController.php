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

        // Obtener alojamientos para mostrar en la landing page
        $accommodations = $this->accommodationService->getAllAccommodations();
        
        // Limitar a 8 alojamientos para la sección principal
        $featuredAccommodations = array_slice($accommodations, 0, 8);
        
        // Obtener alojamientos para ofertas de fin de semana (simular descuentos)
        $weekendOffers = array_slice($accommodations, 0, 4);

        $this->render('home/simple.twig', [
            'pageTitle' => 'Alojamientos - Encuentra tu próximo alojamiento',
            'user' => $user,
            'accommodations' => $accommodations
        ]);
    }
}
