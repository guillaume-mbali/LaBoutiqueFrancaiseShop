<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email){

                $password = $encoder->encodePassword($user,$user->getPassword());

                $user->setPassword($password);


                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content="Bonjour ".$user->getFirstname()."<br/>Bienvenue sur la première boutique dédiée au made in france<br><br/>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, aperiam aspernatur consequatur distinctio eos ipsam labore perspiciatis repellat tempora? Autem doloribus obcaecati quisquam recusandae. Dolor dolore incidunt minima non totam!" ;
                $mail-> send($user->getEmail(), $user->getFirstname(),'Bienvenue sur la Boutique Francaise', $content);

                $notification ="Votre inscription s'est correctement déroulée.";

            }else{

                $notification ="L'email que vous avez renseigné existe déjà";

            }


        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
