<?php

namespace App\Controllers;

use Twig\Environment;

class RedirectController extends BaseController
{
    public function __construct(
        \Twig\Environment $twig,
        ?\App\Utilities\IRequestValidator $validator = null,
        ?\App\Utilities\IAuthenticator $authenticator = null
    ) {
        parent::__construct($twig, $validator, $authenticator);
    }

    public function redirectToAddAccommodation()
    {
        header('Location: /admin/accommodation/add', true, 301);
        exit();
    }

    public function accessDenied()
    {
        echo $this->twig->render('errors/403.twig', [
            'title' => 'Acceso Denegado'
        ]);
    }
}
