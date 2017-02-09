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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

// Controllers
use AppBundle\Controller\Api\TricksController;

// Managers
use AppBundle\Managers\ApiManagers\ApiTricksManager;

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
     * Test if a single tricks can be found using his name.
     *
     * @see TricksController::getTricksByNameAction()
     * @see ApiTricksManager::getSingleTricks()
     */
    public function testSingleTricksFoundByName_Failure()
    {
        $this->client->request('GET', '/api/tricks/MarioTime');

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
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
            'resume' => 'My jaw, that\'s what i call a trick !',
        ]);

        $this->assertEquals(
            Response::HTTP_CREATED,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a new Tricks can be added with bad informations
     * send through the form.
     *
     * @see TricksController::postTricksAction()
     * @see ApiTricksManager::postNewTricks()
     */
    public function testNewTricks_Failure_SameName()
    {
        $this->client->request('POST', '/api/tricks/new', [
            'name' => 'AirLock',
            'groups' => 'Old school',
            'resume' => 'My jaw, that\'s what i call a trick !',
        ]);

        $this->assertEquals(
            Response::HTTP_SEE_OTHER,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a new Tricks can be added with bad informations
     * send through the form.
     *
     * @see TricksController::postTricksAction()
     * @see ApiTricksManager::postNewTricks()
     */
    public function testNewTricks_Failure()
    {
        $this->client->request('POST', '/api/tricks/new', [
            'nam' => 'AirLock',
            'group' => 'Old school',
            'resume' => 'My jaw, that\'s what i call a trick !',
        ]);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the put method in order to update the tricks.
     *
     * @see TricksController::putSingleTricksAction()
     * @see ApiTricksManager::putSingleTricks()
     */
    public function testPutTricksUsingHisId()
    {
        $this->client->request('PUT', '/api/tricks/put/11', [
            'name' => 'Frontflip',
            'groups' => 'Flip',
            'resume' => 'What a nice flip from hell !',
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the put method in order to update the tricks.
     *
     * @see TricksController::putSingleTricksAction()
     * @see ApiTricksManager::putSingleTricks()
     */
    public function testPutTricksUsingHisId_Failure_BadId()
    {
        $this->client->request('PUT', '/api/tricks/put/5', [
            'name' => 'FrontGrab',
            'groups' => 'Grabs',
            'resume' => 'What a nice grab !',
        ]);

        $this->assertEquals(
            Response::HTTP_CREATED,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the put method in order to update the tricks.
     *
     * @see TricksController::putSingleTricksAction()
     * @see ApiTricksManager::putSingleTricks()
     */
    public function testPutTricksUsingHisId_Failure()
    {
        $this->client->request('PUT', '/api/tricks/put/568', [
            'name' => 'Frontflip',
            'groups' => 'Flip',
            'resume' => 'What a nice flip from hell !',
        ]);

        $this->assertEquals(
            Response::HTTP_SEE_OTHER,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the resource can be patched using his id.
     *
     * @see TricksController::patchSingleTricksAction()
     * @see ApiTricksManager::patchSingleTricks()
     */
    public function testPatchSingleTricksUsingHisId()
    {
        $this->client->request('PATCH', '/api/tricks/patch/5', [
            'name' => 'BackLockFlip',
            'groups' => 'Flip',
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the resource can be patched using his id.
     *
     * @see TricksController::patchSingleTricksAction()
     * @see ApiTricksManager::patchSingleTricks()
     */
    public function testPatchSingleTricksUsingHisId_Failure()
    {
        $this->client->request('PATCH', '/api/tricks/patch/5', [
            'nam' => 'BackLockFlip',
            'groups' => 'Flip',
        ]);

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a resource can be deleted using his id.
     *
     * @see TricksController::deleteTricksByIdAction()
     * @see ApiTricksManager::deleteSingleTricks()
     */
    public function deleteSingleTricksUsingHisId()
    {
        $this->client->request('DELETE', '/api/tricks/delete/5');

        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if the delete method could fail if the wrong id is send.
     *
     * @see TricksController::deleteTricksByIdAction()
     * @see ApiTricksManager::deleteSingleTricks()
     */
    public function deleteSingleTricksUsingHisId_Failure()
    {
        $this->client->request('DELETE', '/api/tricks/delete/354');

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
