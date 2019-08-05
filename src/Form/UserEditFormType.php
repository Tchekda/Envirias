<?php

namespace App\Form;

use App\Entity\Badge;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditFormType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('username', null, [
                'label' => 'Pseudo'
            ])
            ->add('email')
            ->add('biography', TextareaType::class, [
                'attr' => [
                    'maxlength' => 255,
                    'data-length' => 255,
                ],
                'label' => 'Biographie',
                'required' => false
            ])
            ->add('city', null, [
                'label' => 'Ville'
            ])
            ->add('website', null, [
                'label' => 'Site Web (sans le http(s)://)'
            ])
            ->add('facebook')
            ->add('twitter')
            ->add('instagram')
            ->add('picture', FileType::class, [
                'mapped' => false,
                'label' => false,
                'required' => false
            ]);
        if (in_array('ROLE_ADMIN', $options['roles'])) {
            $builder
                ->add('roles', ChoiceType::class, [
                    //'mapped' => false,
                    'label' => "Roles",
                    'multiple' => true,
                    'data' => $builder->getData()->getRoles(),
                    'choices' => [
                        'ROLE_ADMIN' => 'ROLE_ADMIN',
                        'ROLE_ALLOWED_TO_SWITCH' => 'ROLE_ALLOWED_TO_SWITCH',
                        'ROLE_CERTIFIED' => 'ROLE_CERTIFIED'
                    ]
                ])
                ->add('totalScore')
                ->add('monthScore')
                ->add('badges', EntityType::class, [
                    'attr' => ['name' => 'user_edit_form_badges'],
                    'multiple' => true,
                    'required' => false,
                    'class' => Badge::class,
                    'choice_label' => 'title',
                    'placeholder' => 'Badges',
                    'invalid_message' => "Ce n'est pas un choix valide!"
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
            'roles' => ['ROLE_USER']
        ]);
    }
}
