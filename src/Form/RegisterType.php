<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Unique;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("username", TextType::class, [
                "label" => "Nom d'utilisateur",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => true,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ],
                "constraints" => [
                    new Regex([
                        "pattern" => "/^[a-zA-Z0-9]+$/",
                        "message" => "Seul les caractères alphanumeric sont autorisés.",
                    ]),
                    new Regex([
                        "pattern" => "/^[a-zA-Z]{1}[a-zA-Z0-9]+$/",
                        "message" => "Le nom d'utilisateur doit commencer par une lettre."
                    ])
                ]
            ])
            ->add("email", EmailType::class, [
                "label" => "Email",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => true,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ],
                "constraints" => [
                    new Email([
                        "message" => "Veuillez entrer un email valide.",
                    ])
                ]
            ])
            ->add("repeatedPassword", RepeatedType::class, [
                "type" => PasswordType::class,
                "mapped" => false,
                "invalid_message" => "Les mots de passe ne sont pas identiques.",
                "first_options" => [
                    "label" => "Mot de passe",
                    "label_attr" => [
                        "class" => "form-label"
                    ],
                    "row_attr" => [
                        "class" => "mb-3"
                    ],
                    "attr" => [
                        "class" => "form-control"
                    ],
                ],
                "second_options" => [
                    "label" => "Confirmer le mot de passe",
                    "label_attr" => [
                        "class" => "form-label"
                    ],
                    "row_attr" => [
                        "class" => "mb-3"
                    ],
                    "attr" => [
                        "class" => "form-control"
                    ],
                ],
                "required" => true,
                "constraints" => [
                    new Length([
                        "min" => 6,
                        "max" => 20,
                        "minMessage" => "Votre mot de passe doit contenir au moins {{ limit }} caractères.",
                        "maxMessage" => "Votre mot de passe doit contenir au plus {{ limit }} caractères.",
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                "class" => "w-100 shadow p-3 mb-5 rounded"
            ]
        ]);
    }
}
