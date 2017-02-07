<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Controllers\Api;

use AppBundle\Controller\Api\TricksController;
use AppBundle\Managers\ApiManagers\ApiTricksManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TricksControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class TricksControllerTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Test if the api can return all the tricks.
     *
     * @see TricksController::getTricksAction()
     * @see ApiTricksManager::getAllTricks()
     */
    public function testAllTricksCanBeFound()
    {
        $this->client->request('GET', '/api/tricks');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a single tricks can be found using his name.
     *
     * @see TricksController::getTricksByNameAction()
     * @see ApiTricksManager::getSingleTricks()
     */
    public function testSingleTricksFoundByName()
    {
        $this->client->request('GET', '/api/tricks/Bigfoot');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a new Tricks can be added.
     *
     * @see TricksController::postTricksAction()
     * @see ApiTricksManager::postNewTricks()
     */
    public function testNewTricks()
    {
        $this->client->request('POST', '/api/tricks/new', [
            'name' => 'AirLock',
            'groups' => 'Old school',
            'resume' => 'My jaw, that\'s what i call a trick !'
        ]);

        $this->assertEquals(
            Response::HTTP_CREATED,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testPutTricksUsingHisId()
    {
        $this->client->request('PUT', '/api/tricks/put/5', [
            'name' => 'FrontGrab',
            'groups' => 'Grabs',
            'resume' => 'What a nice grab !'
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
