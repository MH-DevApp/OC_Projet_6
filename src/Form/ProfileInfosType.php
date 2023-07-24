<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileInfosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                "class" => "w-100 shadow p-3 rounded"
            ]
        ]);
    }
}
