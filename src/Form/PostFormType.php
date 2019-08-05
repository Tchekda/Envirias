<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $tags = array();
        /** @var Tag $tag */
        foreach ($builder->getData()->getTags() as $tag){
            $tags[] = ["tag" => $tag->getName()];
        }
        $builder
            ->add('content', null, [
                'label' => 'Tapez votre post ici',
                'attr' => [

                ]
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'label' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Votre image ne peut avoir comme extension jpeg, jpg, png ou gif',
                    ])
                ],
            ])

            ->add('tagsJS', TextType::class, [
                'mapped' => false,
                'label' => 'Nouveaux Tags',
                'required' => false,
                'attr' => [
                    'divclass' => 'chips chips-autocomplete'
                ]
            ])
            ->add('newTags', HiddenType::class, [
                'mapped' => false,
                'data' => json_encode($tags),
                'required' => false,
                'attr' => [
                    'id' => 'editTagsFormInput'
                ]
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
