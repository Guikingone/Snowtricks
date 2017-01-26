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
        $user = new User();
        $user->setFirstname('Arnaud');
        $user->setLastname('Tricks');
        $user->setBirthdate(new \DateTime());
        $user->setOccupation('Professional snowboarder');
        $user->setUsername('Nono');
        $user->setPassword('Lk__DTHE');
        $user->setEmail('contact@snowtricks.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setToken('dd21498e61e26a5a42d3g9r4z2a364f2s3a2');
        $user->setValidated(true);
        $user->setLocked(false);
        $user->setActive(true);

        $data = (array) $user;

        $form = $this->factory->create(RegisterType::class);
        $form->submit($data);

        $this->assertTrue($form->isSubmitted());
        $this->assertEquals($data, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
