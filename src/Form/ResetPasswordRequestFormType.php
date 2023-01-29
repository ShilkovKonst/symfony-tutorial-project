<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('lastname', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control my-2'
            //     ],
            //     'label' => 'Enter your last name here'
            // ])
            // ->add('firstname', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control my-2'
            //     ],
            //     'label' => 'Enter your first name here'
            // ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control my-2',
                    'placeholder' => 'exemle@email.fr'
                ],
                'label' => 'Enter your e-mail here'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
