<?php

namespace App\Controller;

use App\Form\CheckAuthenticatorCodeType;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/enable2fa', name: 'app_enable_2fa')]
    #[IsGranted('ROLE_USER')]
    public function enable2fa(Request $request, EntityManagerInterface $entityManager, GoogleAuthenticatorInterface $googleAuthenticator): Response
    {
        $connectedUser = $this->getUser();

        if ($connectedUser->isGoogleAuthenticatorEnabled()) {
            return $this->redirectToRoute('app_login');
        }

        //check if QR Code in session
        $secret = $request->getSession()->get('2fa_secret');
        if (!$secret) {
            $secret = $googleAuthenticator->generateSecret();
            $request->getSession()->set('2fa_secret', $secret);
        }
        $connectedUser->setGoogleAuthenticatorSecret($secret);
        $qrCodeContent = $googleAuthenticator->getQRContent($connectedUser);

        $form = $this->createForm(CheckAuthenticatorCodeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $authenticatorCode = $form->get('authenticatorCode')->getData();

            if ($googleAuthenticator->checkCode($connectedUser, $authenticatorCode)) {
                //enable 2fa for user
                $entityManager->persist($connectedUser);
                $entityManager->flush();

                $this->addFlash('success', 'L\'authentification à deux facteurs a été activée avec succès.');

                //remove QR code from session
                $request->getSession()->remove('2fa_secret');

                return $this->redirectToRoute('app_login');

            } else {
                $this->addFlash('error', 'Le code saisi est invalide. Veuillez réessayer.');
            }
        }


        return $this->render('security/enable2fa.html.twig', [
            'qrCodeContent' => $qrCodeContent,
            'form' => $form,
        ]);


    }
}
