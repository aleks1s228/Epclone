<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(
        Request $request, 
        EntityManagerInterface $entityManager, 
        ProductRepository $productRepository
    ): Response {
        
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Jūsu grozs ir tukšs!');
            return $this->redirectToRoute('app_cart');
        }


        $customerName = trim($request->request->get('name', ''));
        $customerEmail = trim($request->request->get('email', ''));
        $deliveryPhone = trim($request->request->get('phone', ''));
        $deliveryMethod = $request->request->get('delivery_method');
        $deliveryAddress = $request->request->get('delivery_address');

        if (empty($customerName) || empty($deliveryPhone) || empty($deliveryMethod) || empty($deliveryAddress)) {
            $this->addFlash('error', 'Visi piegādes un kontaktinformācijas lauki ir obligāti!');
            return $this->redirectToRoute('app_cart');
        }


        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setStatus(OrderStatus::New); // Первичный статус до оплаты
        $order->setCustomerEmail($customerEmail);
        $order->setDeliveryPhone($deliveryPhone);
        $order->setDeliveryMethod($deliveryMethod);
        $order->setDeliveryAddress($deliveryAddress);

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if ($user) {
            $order->setUser($user);
        }

        $totalPrice = 0.0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if (!$product) continue;


            $currentStock = $product->getStock();


            if ($currentStock < $quantity) {
                $this->addFlash('error', sprintf('Prece "%s" vairs nav pieejama pietiekamā daudzumā! Pieejami tikai %d gab.', $product->getName(), $currentStock));
                return $this->redirectToRoute('app_cart');
            }


            $product->setStock($currentStock - $quantity);
            $entityManager->persist($product);


            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $price = $product->getPrice(); 
            $orderItem->setPrice($price);

            $totalPrice += (float)$price * $quantity;
            $order->addOrderItem($orderItem);
            $entityManager->persist($orderItem);
        }

        $order->setTotalPrice((string)$totalPrice);

        $entityManager->persist($order);
        

        $entityManager->flush();


        $session->remove('cart');


        return $this->redirectToRoute('app_payment_gateway', ['id' => $order->getId()]);
    }
}