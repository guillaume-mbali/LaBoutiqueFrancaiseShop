<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Order;
use App\Classe\Cart;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart,$stripeSessionId)
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }
        if($order->getState() == 0){
            //Vider la session cart
            $cart->remove();
            // Modifier le statut isPaid de la commande en statut 1 (0=non payer; 1=payer)
            $order->setState(1);
            $this->entityManager->flush();

            $mail = new Mail();
            $content="Bonjour ".$order->getUser()->getFirstname().",<br/>Merci pour votre commande<br><br/>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, aperiam aspernatur consequatur distinctio eos ipsam labore perspiciatis repellat tempora? Autem doloribus obcaecati quisquam recusandae. Dolor dolore incidunt minima non totam!" ;
            $mail-> send($order->getUser()->getEmail(), $order->getUser()->getFirstname(),'Confirmation de commande', $content);

        }


        return $this->render('order_success/index.html.twig',[
            'order'=>$order
        ]);
    }
}
