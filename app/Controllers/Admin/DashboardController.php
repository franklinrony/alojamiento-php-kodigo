<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Utilities\IAuthenticator;
use App\Utilities\IRequestValidator;

class DashboardController extends BaseController
{
    public function __construct(
        \Twig\Environment $twig,
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null
    ) {
        parent::__construct($twig, $validator, $authenticator);
    }

    public function index(): void
    {
        $this->render('admin/dashboard-simple.twig', [
            'pageTitle' => 'Panel de Administración - Alojamientos'
        ]);
    }
}
