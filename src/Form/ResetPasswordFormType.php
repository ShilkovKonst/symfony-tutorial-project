<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordFormType extends AbstractType
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
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control my-2'
                ],
                'label' => 'Enter new password here'
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
