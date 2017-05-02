<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class UpdateTricksType.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UpdateTricksType extends AbstractType
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
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Votre entrée est trop courte, veuillez réessayer.',
                        'maxMessage' => 'Votre entrée est top longue, veuillez réessayer.',
                    ]),
                ],
            ])
            ->add('groups', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Votre entrée est trop courte, veuillez réessayer.',
                        'maxMessage' => 'Votre entrée est top longue, veuillez réessayer.',
                    ]),
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
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Votre entrée est trop courte, veuillez réessayer.',
                    ]),
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
            'data_class' => 'AppBundle\Entity\Tricks',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_bundle_update_tricks_type';
    }
}
