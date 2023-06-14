<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class RefreshConfirmEmailType extends AbstractType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'attr' => [
                "class" => "w-100 shadow p-3 mb-5 rounded"
            ]
        ]);
    }
}
