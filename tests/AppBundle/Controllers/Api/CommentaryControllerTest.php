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
use AppBundle\Controller\Api\CommentaryController;
use AppBundle\Managers\ApiManagers\ApiCommentaryManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentaryControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class CommentaryControllerTest extends WebTestCase
{
    /** @var null */
    private $client;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Test if all the commentaries can be found using the id
     * of the tricks who contains the commentaries.
     *
     * @see CommentaryController::getCommentariesByTricksAction()
     * @see ApiCommentaryManager::getCommentariesByTricks()
     */
    public function testCommentariesFoundByTricks()
    {
        $this->client->request('GET', '/api/tricks/1/commentaries');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test if a commentary can be found using the tricks id
     * and the commentary id.
     *
     * @see CommentaryController::getSingleCommentaryByTricksAction()
     * @see ApiCommentaryManager::getSingleCommentaryById()
     */
    public function testCommentaryFoundById()
    {
        $this->client->request('GET', '/api/tricks/2/commentary/2');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $this->client->getResponse()->headers->contain(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Test if a commentary can be posted using the tricks name.
     *
     * @see CommentaryController::postNewCommentaryByTricksNameAction()
     * @see ApiCommentaryManager::postNewCommentary()
     */
    public function testCommentaryPostByTricksId()
    {
        $this->client->request('POST', '/api/tricks/BackFlip/commentary/new', [
            'content' => 'Hey, what\'s up guy ? What a great trick isn\'t it ?',
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $this->client->getResponse()->headers->contain(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Test if a commentary can be put (aka updated) using his id.
     *
     * @see CommentaryController::putSingleCommentaryByIdAction()
     * @see ApiCommentaryManager::putSingleCommentary()
     */
    public function testCommentaryCanBePutUsingHisId()
    {
        $this->client->request('PUT', '/api/commentary/put/2', [
            'content' => 'What a truly great tricks !',
        ]);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $this->client->getResponse()->headers->contain(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Test if a commentary can be deleted using his id.
     *
     * @see CommentaryController::deleteSingleCommentaryByIdAction()e()
     * @see ApiCommentaryManager::deleteCommentary()
     */
    public function testCommentaryCanBeDeletedUsingHisId()
    {
        $this->client->request('DELETE', '/api/commentary/delete/2');

        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $this->client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $this->client->getResponse()->headers->contain(
                'Content-Type',
                'application/json'
            )
        );
    }

    /**
     * Test if a commentary can be deleted using his id.
     *
     * @see CommentaryController::deleteSingleCommentaryByIdAction()e()
     * @see ApiCommentaryManager::deleteCommentary()
     */
    public function testCommentaryCanBeDeletedUsingHisId_BadId()
    {
        $this->client->request('DELETE', '/api/commentary/delete/255');

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $this->client->getResponse()->getStatusCode()
        );

        $this->assertTrue(
            $this->client->getResponse()->headers->contain(
                'Content-Type',
                'application/json'
            )
        );
    }
}
