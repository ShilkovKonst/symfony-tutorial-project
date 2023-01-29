<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UserAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SendMailService $email,
        JWTService $jwt
    ): Response {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            //generating users JWT
            //creating Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            //creating Payload
            $payload = [
                'user_id' => $user->getId()
            ];
            //generating token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            //sending mail
            $email->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation votre compte sur le site e-commerce',
                'register',
                compact('user', 'token')
                // alt form for the last parameter:
                //['user' => $user]
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $userRepo, EntityManagerInterface $emi): Response
    {
        //check if token is valid and its format is good
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->checkSignature($token, $this->getParameter('app.jwtsecret'))) {
            //recovering payload
            $payload = $jwt->getPayload($token);
            //searching user ID in database
            $user = $userRepo->find($payload['user_id']);

            //check if user exist and not verified yet
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $emi->flush($user);
                $this->addFlash('success', "Votre compte est activé!");
                return $this->redirectToRoute('app_profile_index');
            }
        }
        //if token isn't valid or changed
        $this->addFlash('danger', "Le token est invalide ou a expiré");
        return $this->redirectToRoute('app_login');
    }

    #[Route('/resendverif', name: 'resend_verify')]
    public function resendVerif(JWTService $jwt, UsersRepository $userRepo, SendMailService $email): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', "Vous devez être connecté pour accéder à cette page!");
            return $this->redirectToRoute('app_login');
        }
        if ($user->getIsVerified()) {
            $this->addFlash('warning', "Votre compte est déjà activé.");
            return $this->redirectToRoute('app_profile_index');
        }

        //generating users JWT
        //creating Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        //creating Payload
        $payload = [
            'user_id' => $user->getId()
        ];
        //generating token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        //sending mail
        $email->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation votre compte sur le site e-commerce',
            'register',
            compact('user', 'token')
            // alt form for the last parameter:
            //['user' => $user]
        );

        $this->addFlash('success', "Email de vérification envoyé.");
        return $this->redirectToRoute('app_main');
    }
}
