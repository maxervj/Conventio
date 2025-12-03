<?php

namespace App\Controller;

use App\Entity\Professor;
use App\Entity\User;
use App\Entity\Student;
use App\Form\ProfessorResgistrationType;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register/student', name: 'app_register_student')]
    public function registerStudent(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $student = new Student();
        $form = $this->createForm(RegistrationFormType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $student->setPassword($userPasswordHasher->hashPassword($student, $plainPassword));

            $entityManager->persist($student);
            $student->setRoles(['ROLE_STUDENT']);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $student,
                (new TemplatedEmail())
                    ->from(new Address('convention.validation@conventio.com', 'Conventio Validator'))
                    ->to((string) $student->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context(['user' => $student])
            );

            // do anything else you need here, like send an email

            return $security->login($student, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'userType' => 'student',
        ]);
    }

    #[Route('/register/professor', name: 'app_register_teacher')]
    public function registerProfessor(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $professor = new Professor();
        $form = $this->createForm(ProfessorResgistrationType::class, $professor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password

            $professor->setPassword($userPasswordHasher->hashPassword($professor, $plainPassword));
            $entityManager->persist($professor);
            $professor->setRoles(['ROLE_PROFESSOR']);
            $entityManager->flush();


            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $professor,
                (new TemplatedEmail())
                    ->from(new Address('convention.validation@conventio.com', 'Conventio Validator'))
                    ->to((string) $professor->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context(['user' => $professor,
                        'verificationUrl' => $verificationUrl])
            );

            return $security->login($professor, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'professorRegistration' => $form,
            'userType' => 'professor'
        ]);
    }


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            if (in_array('ROLE_PROFESSOR', $user->getRoles())) { // si le rôle professeur est bien enregistré
                return $this->redirectToRoute('app_register_teacher');
            }
            else if (in_array('ROLE_STUDENT', $user->getRoles())) {
                return $this->redirectToRoute('app_register_student');
            }
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
