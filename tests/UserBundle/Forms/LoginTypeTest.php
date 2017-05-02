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
use UserBundle\Entity\User;
use UserBundle\Form\Type\LoginType;

/**
 * Class LoginTypeTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoginTypeTest extends TypeTestCase
{
    /**
     * Test the login form via 'basic' login data's.
     */
    public function testSubmitData()
    {
        $data = [
            'email' => 'guik@guillaumeloulier.fr',
            'password' => 'Lk__DTHE',
        ];

        $form = $this->factory->create(LoginType::class);
        $form->submit($data);

        $this->assertTrue($form->isSubmitted());
        $this->assertInstanceOf(User::class, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
