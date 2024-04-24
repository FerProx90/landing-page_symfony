<?php

namespace App\Controller;

use App\Entity\Destiny;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscribeController extends AbstractController
{

    private $en;

    public function __construct(EntityManagerInterface $en){
        $this->en = $en;
    }

    #[Route('/{id}', name: 'app_subscribe', defaults: ['id' => null])]
    public function index(Request $request, $id): Response
    {
        $userRepository = $this->en->getRepository(User::class);
        $user = new User();
        $form = $this->createForm(UserType::class, $user, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $alreadyExist = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if($alreadyExist){
                $form->addError(new FormError('email already used'));
            } else {            
                $this->en->persist($user);
                $this->en->flush();
                $userId = $user->getId();
                return $this->redirectToRoute('app_subscribe', ['id' => $userId]);
            }
        }

        $destinies = $this->en->getRepository(Destiny::class)->findAll();
        if ($id === null){
            return $this->render('subscribe/index.html.twig', [
                'form' => $form->createView(),
                'destinies' => $destinies
            ]);
        } else {
            $registeredUser = $userRepository->findUser($id);
            return $this->render('subscribe/index.html.twig', [
                'registeredUser' => $registeredUser,
                'destinies' => $destinies
            ]);
        }
    }

    #[Route('/insert', name: 'app_inser')]
    public function insert(): Response
    {
       $user = new User('fer@as.as', '3221233223');
       $this->en->persist($user);
       $this->en->flush();
       return new JsonResponse(['success' => true]);
    }
}
