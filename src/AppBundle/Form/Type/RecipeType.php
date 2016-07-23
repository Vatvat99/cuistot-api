<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Form\Type\CategoryType;

class RecipeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('time', TextType::class)
            ->add('category', CategoryType::class, [
                'data_class' => 'AppBundle\Entity\Category'
            ])
            ->add('picture', TextType::class)
            ->add('ingredients', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add'    => true
            ])
            ->add('steps', CollectionType::class, [
                'entry_type' => TextareaType::class,
                'allow_add'    => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Recipe',
            'csrf_protection' => false,
        ]);
    }

}