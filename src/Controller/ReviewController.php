<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/product/{id}/review', name: 'app_product_review_add', methods: ['POST'])]
    public function add(int $id, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
  
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produkts netika atrasts.');
        }

        $rating = (int)$request->request->get('vertejums');
        $description = trim($request->request->get('apraksts'));

        if ($rating < 1 || $rating > 5 || empty($description)) {
            $this->addFlash('danger', 'Lūdzu, aizpildiet visus laukus korekti!');
            return $this->redirectToRoute('app_catalog'); 
        }

        $review = new Review();
                $review->setProduct($product);
                $review->setUser($this->getUser());
                
    
                $review->setRating($rating); 
                $review->setDescription($description); 
                $review->setStatuss('new');
                $review->setCreatedAt(new \DateTimeImmutable()); 

                $em->persist($review);
                $em->flush();

        $this->addFlash('success', 'Paldies! Jūsu atsauksme ir saņemta un tiks publicēta pēc pārbaudes.');
        return $this->redirectToRoute('app_catalog');
    }
}