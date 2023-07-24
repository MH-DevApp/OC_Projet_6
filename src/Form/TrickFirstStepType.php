<?php

namespace App\Form;

use App\Entity\GroupTrick;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickFirstStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("name", TextType::class, [
                "label" => "Nom de la figure",
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

            ])
            ->add("description", TextareaType::class, [
                "label" => "Description",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => true,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add("groupTrick", EntityType::class, [
                "label" => "Groupe de figure",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "class" => GroupTrick::class,
                "required" => true,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-select"
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'attr' => [
                "class" => "w-100 shadow p-3 mb-5 rounded"
            ]
        ]);
    }
}
