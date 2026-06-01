<?php

namespace App\Controller;

use App\Entity\Product; 
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    #[Route('/', name: 'app_catalog')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryCode = $request->query->get('category');
        
        
        $filters = $request->query->all();
        unset($filters['category']); 
        
        $filters = array_filter($filters);

        return $this->render('catalog/index.html.twig', [
            'products' => $productRepository->findByFilters($categoryCode, $filters),
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $categoryCode,
            'currentFilters' => $filters,
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
}