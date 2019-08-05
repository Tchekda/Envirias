<?php

namespace App\Form;

use App\Entity\Badge;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $colors = [
            "red",
            "pink",
            "purple",
            "deep-purple",
            "indigo",
            "blue",
            "light-blue",
            "cyan",
            "teal",
            "green",
            "light-green",
            "lime",
            "yellow",
            "amber",
            "orange",
            "deep-orange",
            "brown",
            "grey",
            "blue-grey",
            "black",
            "white",
            "transparent"
        ];
        $builder
            ->add('title', null, [
                'label' => 'Titre'
            ])
            ->add('icon', null, [
                'label' => 'Icones <a href="https://material.io/ressources/icons/" target="_blank">Liste</a>'
            ])
            ->add('color', ChoiceType::class, [
                'choices' => $colors,
                'choice_label' => function(string $color) {
                    return $color;
                },
                'choice_attr' => function(string $color) {
                    return ['class' => $color . ' black-text'];
                },
                'label' => 'Couleur de l\'icone <a href="https://materializecss.com/color.html" target="_blank">Liste</a>'
            ])
            ->add('bgColor', ChoiceType::class, [
                'choices' => $colors,
                'choice_label' => function(string $color) {
                    return $color;
                },
                'choice_attr' => function(string $color) {
                    return ['class' => $color . ' black-text'];
                },
                'label' => 'Couleur de fond <a href="https://materializecss.com/color.html" target="_blank">Liste</a>'
            ])
            ->add('cost', null, [
                'label' => 'Poids'
            ])
            ->add('users', EntityType::class, [
                'multiple' => true,
                'class' => User::class,
                'choice_label' => 'username',
                'placeholder' => 'Utilisateurs',
                'invalid_message' => "Ce n'est pas un choix valide!"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Badge::class,
        ]);
    }
}
