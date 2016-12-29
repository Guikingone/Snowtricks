<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BackTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class BackTest extends WebTestCase
{
    /**
     * Test if the back service can be found and if he's the right class.
     */
    public function testBackServiceIsFound()
    {
        $kernel = static::createKernel();
        $service = $kernel->getContainer()->get('app.back');

        if (is_object($service)) {
            $this->assertInstanceOf(Back::class, $service);
        }
    }

    /**
     * Test if the return of the "entity found method" is correct.
     */
    public function testBackReturnEntityMethod()
    {
        $kernel = static::createKernel();
        $service = $kernel->getContainer()->get('app.back');

        if (is_object($service) && $service instanceof Back) {
            $this->assertInstanceOf(
                Tricks::class,
                $service->getTricksByName('Backflip')
            );
            $this->assertInstanceOf(
                Commentary::class,
                $service->getCommentaryByTricks('Backflip')
            );
            // Store the return to test the value passed through the getters.
            $tricks = $service->getTricksByName('Backflip');
            $this->assertEquals(
                'BackFlip',
                $tricks->getName('BackFlip')
            );
        }
    }

    /**
     * Test if the add tricks method return the right class.
     */
    public function testBackTricksAddingMethod()
    {
        $kernel = static::createKernel();
        $service = $kernel->getContainer()->get('app.back');

        if (is_object($service) && $service instanceof Back) {
            $this->assertInstanceOf(
                FormView::class,
                $service->addtricks(new Request())
            );
        }
    }

    /**
     * Test if the app.back service can validate a trick using his $id.
     */
    public function testBackValidationMethod()
    {
        $kernel = static::createKernel();
        $service = $kernel->getContainer()->get('app.back');
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager')
                           ->getRepository('AppBundle:TricksRepository')
                           ->findOneBy(['name' => 'backflip']);

        if (is_object($service) && $service instanceof Back) {
            $this->assertInstanceof(
                Response::class,
                $service->validateTricks($doctrine->getId())
            );
        }
    }

    /**
     * Test if the app.back service can refuse a validation using the Trick $id.
     */
    public function testBackNoValidationMethod()
    {
        $kernel = static::createKernel();
        $service = $kernel->getContainer()->get('app.back');
        $doctrine = $kernel->getContainer()->get('doctrine.orm.entity_manager')
                           ->getRepository('AppBundle:TricksRepository')
                           ->findOneBy(['name' => 'backflip']);

        if (is_object($service) && $service instanceof Back) {
            $this->assertEquals(
                Response::class,
                $service->refuseValidation($doctrine->getId())
            );
        }
    }
}
