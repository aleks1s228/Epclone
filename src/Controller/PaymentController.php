<?php

namespace App\Controller;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment/gateway/{id}', name: 'app_payment_gateway', methods: ['GET'])]
    public function gateway(Order $order): Response
    {

        if ($order->getStatus() === OrderStatus::Paid) {
            return $this->redirectToRoute('app_payment_success', ['id' => $order->getId()]);
        }


        return $this->render('payment/stripe_mock.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/payment/process/{id}', name: 'app_payment_process', methods: ['POST'])]
    public function process(Order $order, EntityManagerInterface $entityManager): Response
    {

        $order->setStatus(OrderStatus::Paid);
        $entityManager->flush();

        return $this->redirectToRoute('app_payment_success', ['id' => $order->getId()]);
    }

    #[Route('/payment/success/{id}', name: 'app_payment_success', methods: ['GET'])]
    public function success(Order $order): Response
    {
        return $this->render('payment/success.html.twig', [
            'order' => $order
        ]);
    }
}