<?php

namespace Tests\Controllers\Admin;

use App\Controllers\Admin\AdminAccommodationController;
use App\Models\Accommodation;
use App\Models\User;
use App\Services\IAccommodationService;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminAccommodationControllerTest extends TestCase
{
    private $accommodationService;
    private $flash;
    private $view;
    private $controller;
    private $request;
    private $response;

    protected function setUp(): void
    {
        $this->accommodationService = $this->createMock(IAccommodationService::class);
        $this->flash = $this->createMock(Messages::class);
        $this->view = $this->createMock(Twig::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->controller = new AdminAccommodationController(
            $this->accommodationService,
            $this->flash,
            $this->view
        );
    }

    public function testShowAddFormRendersCorrectTemplate()
    {
        $this->view->expects($this->once())
            ->method('render')
            ->with(
                $this->response,
                'admin/accommodation/add.twig',
                ['title' => 'Agregar Alojamiento']
            );

        $this->controller->showAddForm($this->request, $this->response);
    }

    public function testAddValidatesRequiredFields()
    {
        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'name' => '',
                'description' => 'Test description',
                'location' => 'Test location',
                'price' => '100'
            ]);

        $this->flash->expects($this->once())
            ->method('addMessage')
            ->with('error', $this->stringContains('name'));

        $this->response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/admin/accommodation/add')
            ->willReturnSelf();

        $this->controller->add($this->request, $this->response);
    }

    public function testAddValidatesPositivePrice()
    {
        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'name' => 'Test Name',
                'description' => 'Test description',
                'location' => 'Test location',
                'price' => '-100'
            ]);

        $this->flash->expects($this->once())
            ->method('addMessage')
            ->with('error', $this->stringContains('precio'));

        $this->response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/admin/accommodation/add')
            ->willReturnSelf();

        $this->controller->add($this->request, $this->response);
    }

    public function testAddSuccessfullyCreatesAccommodation()
    {
        $userId = 1;
        $user = new User();
        $user->id = $userId;

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'name' => 'Test Name',
                'description' => 'Test description',
                'location' => 'Test location',
                'price' => '100'
            ]);

        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with('user')
            ->willReturn($user);

        $accommodation = new Accommodation();
        $this->accommodationService->expects($this->once())
            ->method('createAccommodation')
            ->willReturn($accommodation);

        $this->flash->expects($this->once())
            ->method('addMessage')
            ->with('success', $this->stringContains('exitosamente'));

        $this->response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/admin/accommodations')
            ->willReturnSelf();

        $this->controller->add($this->request, $this->response);
    }
}
