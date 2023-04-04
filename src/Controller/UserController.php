<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            #on set les propièter qui ne sont pas dans le formulaire ET obligatoire dans la BDD
            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());

            # on set les Roles des user. cette propriete est un array[]
            $user->setRoles(['ROLE_USER']);

            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );

            $repository->save($user, true);

            $this->addFlash('success', "votre inscription à bien été pris en compte !");
            return $this->redirectToRoute('show_home');
        }

        return $this->render('user/register_form.html.twig', [
            'form' => $form->createView()
        ]);
    } //end register()

}//end Class{}
