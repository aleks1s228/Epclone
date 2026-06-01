<?php

namespace App\Controller\Admin;

// Импортируем только контроллеры, как у тебя и было изначально
use App\Controller\Admin\ProductCrudController;
use App\Controller\Admin\CategoryCrudController;
use App\Controller\Admin\ReviewCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function index(): Response
    {
        // Генерируем ссылку на список товаров с явным указанием экшена index
        $url = $this->adminUrlGenerator
            ->setController(ProductCrudController::class)
            ->setAction('index') 
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Epclone - Administrācijas Panelis');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Galvenais panelis', 'fa fa-home');

        yield MenuItem::section('E-komercija');
        
        // Генерируем полноценные CRUD ссылки через встроенный генератор урлов,
        // чтобы шаблон меню не ломался, а кнопки добавления появились.
        
        $productUrl = $this->adminUrlGenerator
            ->setController(ProductCrudController::class)
            ->setAction('index')
            ->generateUrl();
            
        $categoryUrl = $this->adminUrlGenerator
            ->setController(CategoryCrudController::class)
            ->setAction('index')
            ->generateUrl();
            
        $reviewUrl = $this->adminUrlGenerator
            ->setController(ReviewCrudController::class)
            ->setAction('index')
            ->generateUrl();

        yield MenuItem::linkToUrl('Preces (Products)', 'fas fa-shopping-cart', $productUrl);
        yield MenuItem::linkToUrl('Kategorijas (Categories)', 'fas fa-tags', $categoryUrl);
        yield MenuItem::linkToUrl('Atsauksmes (Reviews)', 'fas fa-comments', $reviewUrl);
    }
}