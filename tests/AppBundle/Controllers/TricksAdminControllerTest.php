<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Controllers;

use AppBundle\Controller\Web\TricksAdminController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

// Listeners
use AppBundle\Listeners\TricksListeners;

// Manager
use AppBundle\Managers\TricksManager;

// Events
use AppBundle\Events\Tricks\TricksRefusedEvent;
use AppBundle\Events\Tricks\TricksValidatedEvent;

/**
 * Class TricksAdminControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksAdminControllerTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'Nano',
            'PHP_AUTH_PW' => 'lappd_dep',
        ]);
    }

    /**
     * Test the validation of a tricks using his name.
     *
     * @see TricksAdminController::tricksValidationAction()
     * @see TricksManager::validateTricks()
     * @see TricksValidatedEvent
     * @see TricksListeners::onValidateTricks()
     */
    public function testTricksValidation()
    {
        $this->client->request('GET', '/admin/tricks/validate/Frontflip');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the validation of a tricks using a bad name.
     *
     * @see TricksAdminController::tricksValidationAction()
     * @see TricksManager::validateTricks()
     */
    public function testTricksValidationWithBadName()
    {
        $this->client->request('GET', '/admin/tricks/validate/BackAir');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the refuse process of a tricks using his name.
     *
     * @see TricksAdminController::tricksRefusedAction()
     * @see TricksManager::refuseTricks()
     * @see TricksRefusedEvent
     * @see TricksListeners::onRefuseTricks()
     */
    public function testTricksRefused()
    {
        $this->client->request('GET', '/admin/tricks/refuse/TruckDriver');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the refuse process of a tricks using a bad argument.
     *
     * @see TricksAdminController::tricksRefusedAction()
     * @see TricksManager::refuseTricks()
     */
    public function testTricksRefusedWithBadArgument()
    {
        $this->client->request('GET', '/admin/tricks/refuse/'. 243);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a tricks can be updated.
     *
     * @see TricksAdminController::tricksUpdateAction()
     */
    public function testTricksUpdated()
    {
        $crawler = $this->client->request('GET', '/admin/tricks/update/BackAir');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['update_tricks[name]'] = 'FrontGrab';
            $form['update_tricks[groups]']->select('Grabs');
            $form['update_tricks[resume]'] = 'A new grab content !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_OK,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test if a tricks can be updated using a bad name.
     *
     * @see TricksAdminController::tricksUpdateAction()
     * @see TricksManager::updateTricks()
     */
    public function testTricksUpdatedWithBadName()
    {
        $this->client->request('GET', '/admin/tricks/update/BackAirFlip');

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the refuse process of a tricks using his name.
     */
    public function testTricksDeleted()
    {
        $this->client->request('GET', '/admin/tricks/delete/Bigfoot');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
