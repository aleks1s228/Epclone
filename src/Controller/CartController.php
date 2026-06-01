<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $subtotal = (float)$product->getPrice() * $quantity;
                $total += $subtotal;
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cartData,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (!isset($cart[$id])) {
            $cart[$id] = 0;
        }
        $cart[$id]++;

        $session->set('cart', $cart);

        $this->addFlash('success', 'Prece pievienota grozam!');
        return $this->redirectToRoute('app_catalog');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(Request $request, SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            $this->addFlash('danger', 'Grozs ir tukšs!');
            return $this->redirectToRoute('app_cart');
        }

        $clientName = $request->request->get('name');
        $clientEmail = $request->request->get('email');
        $clientPhone = $request->request->get('phone');

        if (!$clientName || !$clientEmail || !$clientPhone) {
            $this->addFlash('danger', 'Lūdzu, aizpildiet visus laukus!');
            return $this->redirectToRoute('app_cart');
        }

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setStatus('new');
        $order->setCustomerName($clientName);
        $order->setCustomerEmail($clientEmail);
        $order->setCustomerPhone($clientPhone);
        if ($this->getUser()) {
            if (method_exists($order, 'setUser')) {
                $order->setUser($this->getUser());
            }
        }

        $totalPrice = 0;
        $em->persist($order);

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                
                if (method_exists($orderItem, 'setOrderRef')) {
                    $orderItem->setOrderRef($order);
                } else {
                    $orderItem->setOrder($order);
                }

                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($product->getPrice());
                
                $totalPrice += (float)$product->getPrice() * $quantity;
                $em->persist($orderItem);

                $product->setStock($product->getStock() - $quantity);
            }
        }

        $order->setTotalPrice($totalPrice);
        $em->flush();

        $session->remove('cart');

        $this->addFlash('success', 'Paldies! Pasūtījums veiksmīgi noformēts.');
        return $this->redirectToRoute('app_catalog');
    }
}