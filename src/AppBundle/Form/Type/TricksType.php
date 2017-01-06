<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Constraints
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TricksType.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksType extends AbstractType
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
            'data_class' => 'AppBundle\Entity\Tricks',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_bundle_tricks_type';
    }
}
