<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Forms;

use Symfony\Component\Form\Test\TypeTestCase;
use UserBundle\Form\Type\RegisterType;
use UserBundle\Form\Type\RegistrationType;
use UserBundle\Entity\User;

/**
 * Class RegistrationTypeTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class RegistrationTypeTest extends TypeTestCase
{
    /**
     * Test the login form via 'basic' login data's.
     */
    public function testSubmitData()
    {
        $data = [
            'username' => 'Guik',
            'email' => 'contact.guillaume@kdl.fr',
            'password' => 'Ie1FGDL',
            'password_repeat' => 'Ie1FGDL',
        ];

        $form = $this->factory->create(RegisterType::class);

        $instance = User::fromArray($data);

        $form->submit($instance);

        $this->assertTrue($form->isSubmitted());
        $this->assertEquals($instance, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
