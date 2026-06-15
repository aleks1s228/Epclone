<?php

namespace App\Controller;

use App\Entity\Product; 
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;


class CatalogController extends AbstractController
{
    #[Route('/', name: 'app_catalog')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository, PaginatorInterface $paginator): Response
    {
        $categoryCode = $request->query->get('category');
        $searchQuery = $request->query->get('q', ''); 
        
        
        $filters = $request->query->all();
        unset($filters['category']); 
        
        $filters = array_filter($filters);


        if (!empty($searchQuery)) {
            $productsData = $productRepository->searchByQuery($searchQuery);
        } else {
            // Если поиска нет, отдаем обычный список по категориям и фильтрам
            $productsData = $productRepository->findByFilters($categoryCode, $filters);
        }
        // pagination
        $pagination=$paginator->paginate(
            $productsData,
            $request->query->getInt('page', 1),
            12

        );

        return $this->render('catalog/index.html.twig', [
            'products' => $pagination, 
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $categoryCode,
            'currentFilters' => $filters,
            'searchQuery' => $searchQuery, 
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produkts netika atrasts.');
        }

        return $this->render('catalog/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/kontakti', name: 'app_contacts')]
    public function contacts(): Response
    {
        return $this->render('catalog/contacts.html.twig');
    }
    public function createAdmin(
    EntityManagerInterface $em, 
    UserPasswordHasherInterface $passwordHasher
): Response {
    // Проверяем, может админ уже есть, чтобы не дублировать
    $existingUser = $em->getRepository(User::class)->findOneBy(['email' => 'alex.krastins2006@gmail.com']);
    
    if ($existingUser) {
        return new Response('Пользователь с email admin@epclone.com уже существует!');
    }

    $user = new User();
    $user->setEmail('alex.krastins2006@gmail.com'); // Твой логин для админки
    $user->setFirstName('Alex');
    $user->setLastName('Admin');
    $user->setPhone('+37120525125');
    $user->setVerified(true); // или is_verified в зависимости от твоего геттера/сеттера

    // Хешируем пароль "admin123"
    $hashedPassword = $passwordHasher->hashPassword($user, 'SmArtHX7QrWbf6a6'); // Твой пароль
    $user->setPassword($hashedPassword);

    // Самое главное — даем права суперпользователя
    $user->setRoles(['ROLE_ADMIN']);

    $em->persist($user);
    $em->flush();

    return new Response('Админ успешно создан!Срочно удали этот роут из кода после проверки!');
}
}