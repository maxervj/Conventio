<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $student = new Student();
        $form = $this->createForm(RegistrationFormType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setPassword(
                $userPasswordHasher->hashPassword(
                    $student,
                    $form->get('plainPassword')->getData()
                )
            );

            $student->setRoles(['ROLE_STUDENT']);
            $student->setIsVerified(false);

            // Générer un token de vérification
            $verificationToken = bin2hex(random_bytes(32));
            $student->setVerificationToken($verificationToken);

            $entityManager->persist($student);
            $entityManager->flush();

            // Envoyer l'email de confirmation
            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                ['token' => $verificationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new TemplatedEmail())
                ->from(new Address('noreply@conventio.fr', 'Conventio'))
                ->to($student->getEmail())
                ->subject('Confirmez votre adresse email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context([
                    'verificationUrl' => $verificationUrl,
                    'student' => $student,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Un email de confirmation vous a été envoyé. Veuillez cliquer sur le lien dans l\'email pour activer votre compte.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email/{token}', name: 'app_verify_email')]
    public function verifyEmail(
        string $token,
        EntityManagerInterface $entityManager
    ): Response {
        $student = $entityManager->getRepository(Student::class)->findOneBy([
            'verificationToken' => $token,
        ]);

        if (!$student) {
            $this->addFlash('error', 'Le lien de vérification est invalide ou a expiré.');
            return $this->redirectToRoute('app_login');
        }

        if ($student->isVerified()) {
            $this->addFlash('info', 'Votre compte est déjà vérifié.');
            return $this->redirectToRoute('app_login');
        }

        $student->setIsVerified(true);
        $student->setVerificationToken(null);

        $entityManager->flush();

        $this->addFlash('success', 'Votre adresse email a été vérifiée avec succès ! Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
