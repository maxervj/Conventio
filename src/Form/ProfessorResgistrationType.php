<?php

namespace App\Form;

use App\Entity\Professor;
use App\Validator\AllowedEmailDomain;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\Regex;

class ProfessorResgistrationType extends AbstractType
{

    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedDomains = $this->params->get('app.allowed_email_domains');

        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom *',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre nom',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Nom',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom *',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre prénom',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Prénom',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre email',
                    ]),
                    new Email([
                        'message' => 'L\'adresse email {{ value }} n\'est pas valide.',
                    ]),
                    new AllowedEmailDomain(
                        allowedDomains: $allowedDomains
                    ),
                ],
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe *',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Mot de passe',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir un mot de passe',
                        ]),
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 4096,
                        ]),
                        new PasswordStrength([
                            'minScore' => PasswordStrength::STRENGTH_WEAK,
                            'message' => 'Le mot de passe est trop faible. Veuillez utiliser un mot de passe plus robuste avec des lettres majuscules, minuscules, chiffres et caractères spéciaux.',
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.',
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                            'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial (@$!%*?&)',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Vérification du mot de passe *',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Confirmez le mot de passe',
                    ],
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les CGU',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions générales d\'utilisation.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Professor::class,
        ]);
    }
}
