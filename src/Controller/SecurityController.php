<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgotpass', name: 'app_forgotpass')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //search user in db to whom belongs this email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // check if user exist
            if ($user) {
                //generating token for reinitialise password
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                //generating URL for reinitialise password
                $url = $this->generateUrl('app_resetpass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                //creating emails data
                $contex = compact('url', 'user');

                //send email
                $mail->send(
                    'no-reply@monsite.net',
                    $user->getEmail(),
                    'Réinitialisation de mot de passe',
                    'password_reset',
                    $contex
                );

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', "Cet e-mail n'est pas vérifié!");
                return $this->redirectToRoute('app_forgotpass');
            }
        }

        return $this->render('security/reset_password_request.html.twig', [
            "requestPassForm" => $form->createView()
        ]);
    }

    #[Route('/forgotpass/{token}', name: 'app_resetpass')]
    public function resetPassword(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        //check if user with this token exist
        $user = $usersRepository->findOneByResetToken($token);
        if ($user) {
            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setResetToken('');
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe est changé');
                return $this->redirectToRoute('app_login');
            }
            return $this->render('security/reset_password.html.twig', [
                "passForm" => $form->createView()
            ]);
        }
        $this->addFlash('danger', 'Ce jéton est invalide');
        return $this->redirectToRoute('app_forgotpass');
    }
}
