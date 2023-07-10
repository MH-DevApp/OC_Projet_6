<?php

namespace App\Form;

use App\Entity\MediaTrick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Url;

class MediaTrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                "label" => "Type de media",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => true,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-select"
                ],
                "choices" => [
                    "Image" => "image",
                    "Vidéo" => "video"
                ]
            ])
            ->add('imageFile', FileType::class, [
                "label" => "Image",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => false,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ],
                "mapped" => false,
                "constraints" => [
                    new Image([
                        "mimeTypes" => ["image/png", "image/jpeg"],
                        "mimeTypesMessage" => "L'extension {{ type }} n'est pas valide.
                            Les extensions autorisées sont {{ types }}.",
                        "maxSize" => "1M",
                        "maxSizeMessage" => "Votre image fait {{ size }} {{ suffix }}. 
                            La limite autorisée est de {{ limit }} {{ suffix }}.",
                    ])
                ]
            ])
            ->add('linkMovie', TextType::class, [
                "label" => "Lien de la vidéo",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => false,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ],
                "mapped" => false,
                "constraints" => [
                    new Url([
                        "protocols" => ["https"],
                        "message" => "L'URL n'est pas valide.",
                    ])
                ]
            ])
            ->add('sourceName', TextType::class, [
                "label" => "Nom de la source",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => false,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('sourceLink', UrlType::class, [
                "label" => "Lien de la source",
                "label_attr" => [
                    "class" => "form-label"
                ],
                "required" => false,
                "row_attr" => [
                    "class" => "mb-3"
                ],
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add("isFeatured", CheckboxType::class, [
                "label" => null,
                "row_attr" => [
                    "class" => "d-none mb-3"
                ],
                "required" => false,
                "attr" => [
                    "class" => "form-check"
                ],
                "mapped" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MediaTrick::class,
            'attr' => [
                "class" => "w-100 p-3 my-3 rounded"
            ]
        ]);
    }
}
