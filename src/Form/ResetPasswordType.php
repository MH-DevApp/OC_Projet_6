<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (isset($options["data"]) && $options["data"]["type"] === "profile") {
            $builder
                ->add("actualPassword", PasswordType::class, [
                    "mapped" => false,
                    "label" => "Mot de passe actuel",
                    "label_attr" => [
                        "class" => "form-label"
                    ],
                    "row_attr" => [
                        "class" => "mb-3"
                    ],
                    "attr" => [
                        "class" => "form-control"
                    ],
                    "required" => true,
                    "constraints" => [
                        new UserPassword([
                            "message" => "Le mot de passe actuel est incorrect."
                        ])
                    ]
                ]);
        }

        $builder
            ->add("repeatedPassword", RepeatedType::class, [
                "type" => PasswordType::class,
                "mapped" => false,
                "invalid_message" => "Les mots de passe ne sont pas identiques.",
                "first_options" => [
                    "label" => "Nouveau mot de passe",
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
                    "label" => "Confirmer le nouveau mot de passe",
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
            // Configure your form options here
            'attr' => [
                "class" => "w-100 shadow p-3 rounded"
            ]
        ]);
    }
}
