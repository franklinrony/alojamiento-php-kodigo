<?php

namespace App\Controllers;

use App\Utilities\IAuthenticator;
use App\Utilities\IRequestValidator;

/**
 * Class HomeController
 * Controlador para la página principal
 */
class HomeController extends BaseController
{
    /**
     * Constructor del controlador
     *
     * @param \Twig\Environment $twig
     * @param IRequestValidator|null $validator
     * @param IAuthenticator|null $authenticator
     */
    public function __construct(
        \Twig\Environment $twig,
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null
    ) {
        parent::__construct($twig, $validator, $authenticator);
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

        $this->render('home/index.twig', [
            'pageTitle' => 'Inicio',
            'user' => $user
        ]);
    }
}
