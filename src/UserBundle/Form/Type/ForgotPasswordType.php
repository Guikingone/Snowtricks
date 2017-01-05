<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ForgotPasswordType.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class ForgotPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('username', TextType::class)
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user_bundle_forgot_password';
    }
}
