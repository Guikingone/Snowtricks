<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\UserBundle\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use UserBundle\Services\Web\Security;

/**
 * Class SecurityTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class SecurityTest extends KernelTestCase
{
    /** @var Security */
    private $security;

    /**
     * Set the entity for BDD.
     */
    public function setUp()
    {
        self::bootKernel();

        $this->security = static::$kernel->getContainer()->get('user.security');
    }

    /**
     * Test if the service is found and correct.
     */
    public function testServiceIsFound()
    {
        if (is_object($this->security)) {
            $this->assertInstanceOf(
                Security::class,
                $this->security
            );
        }
    }
}
