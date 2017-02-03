<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Form\Type\Api;

use Nelmio\ApiDocBundle\Tests\Fixtures\Form\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Entity
use AppBundle\Entity\Tricks;

// Constraints
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ApiTricksType
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ApiTricksType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                        'max' => '100',
                        'minMessage' => 'Le nom donné est trop court, veuillez réessayer.',
                        'maxMessage' => 'Le nom donné est trop long, veuillez réessayer.',
                    ]),
                ],
                'required' => true,
            ])
            ->add('groups', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'choices' => [
                    'Grabs' => 'Grabs',
                    'Flip' => 'Flip',
                    'Rotations' => 'Rotations',
                    'Rotations désaxées' => 'Rotations désaxées',
                    'Slides' => 'Slides',
                    'One foot tricks' => 'One foot tricks',
                    'Old school' => 'Old school',
                ],
                'required' => true,
            ])
            ->add('resume', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'required' => true,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
            'csrf_protection' => false
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_bundle_api_tricks_type';
    }
}
