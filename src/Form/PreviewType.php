<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Regex;

class PreviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, [
                'label' => 'Enter URL',
                'required' => true,
                'attr' => ['placeholder' => 'https://www.example.com'],
                'constraints' => [
                    new Url(['message' => 'Invalid URL format. Please enter a valid URL.']),
//                    new Regex([
//                        'pattern' => '/((http|https):\/\/)(www\.)?[a-zA-Z0-9@:%._\+~#?&\/\/=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%._\+~#?&\/\/=]*)/',
//                        'message' => 'Invalid URL format. Please enter a valid URL.',
//                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Generate Preview']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
