<?php

namespace App\Controller\Admin;

use App\Entity\Holiday;
use App\Entity\Master;
use App\Entity\Member;
use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(MemberCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Админ-панель')
            ->renderContentMaximized()
            ->disableUrlSignatures();
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(100);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Участники', 'fas fa-user-alt', Member::class);
        yield MenuItem::linkToCrud('Ведущие', 'fas fa-user', Master::class);
        yield MenuItem::linkToCrud('Сообщения', 'fas fa-comment', Message::class);
        yield MenuItem::linkToCrud('Выходные', 'fas fa-calendar', Holiday::class);
    }
}
