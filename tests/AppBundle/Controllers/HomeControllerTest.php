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

use AppBundle\Controller\Web\HomeController;
use AppBundle\Managers\TricksManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

// Managers
use AppBundle\Managers\CommentaryManager;

// Forms
use AppBundle\Form\Type\CommentaryType;
use AppBundle\Form\Type\TricksType;

// Events
use AppBundle\Events\Commentary\CommentaryAddedEvent;

// listeners
use AppBundle\Listeners\CommentaryListeners;
use AppBundle\Listeners\TricksListeners;

/**
 * Class IndexControllerTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class HomeControllerTest extends WebTestCase
{
    /** @var null */
    private $client = null;

    /** {@inheritdoc} */
    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'Guikingone',
            'PHP_AUTH_PW' => 'Lk__DTHE',
        ]);
    }

    /**
     * Test the indexAction.
     *
     * @see HomeController::indexAction()
     */
    public function testIndex()
    {
        $this->client->request('GET', '/');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the tricksAction.
     *
     * @see HomeController::tricksAction()
     */
    public function testTricks()
    {
        $this->client->request('GET', '/tricks');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    /**
     * Test the tricksDetailsAction, first, the HTTP header code is test to ensure
     * that the route is correctly loaded, after this, the commentary add method
     * is tested through the crawler in oder to validate the Event and Listeners
     * linked to the Commentary entity.
     *
     * @see HomeController::tricksDetailsAction()
     * @see CommentaryManager::addCommentary()
     * @see CommentaryType
     * @see CommentaryAddedEvent
     * @see CommentaryListeners::onCommentaryAdded()
     */
    public function testTricksDetails()
    {
        $crawler = $this->client->request('GET', '/tricks/Airflip');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['commentary[content]'] = 'A new comment about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_FOUND,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test the tricksDetailsAction, first, the HTTP header code is test to ensure
     * that the route is correctly loaded, after this, the commentary add method
     * is tested through the crawler in oder to validate the Event and Listeners
     * linked to the Commentary entity.
     *
     * @see HomeController::tricksDetailsAction()
     * @see CommentaryManager::addCommentary()
     * @see CommentaryType
     * @see CommentaryAddedEvent
     * @see CommentaryListeners::onCommentaryAdded()
     */
    public function testTricksDetailsWithoutLogin()
    {
        $crawler = $this->client->request('GET', '/tricks/Airflip');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['commentary[content]'] = 'A new comment about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_FOUND,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test if a new Tricks can be added.
     *
     * @see HomeController::tricksAddAction()
     * @see TricksManager::addTrick()
     * @see TricksType
     * @see TricksListeners::prePersist()
     * @see TricksListeners::postPersist()
     */
    public function testsTricksAdd()
    {
        $crawler = $this->client->request('GET', '/tricks/add');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['tricks[name]'] = 'Sideflip';
            $form['tricks[groups]']->select('Flip');
            $form['tricks[resume]'] = 'A new content about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_OK,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }

    /**
     * Test if a new Tricks can be added without the login phase.
     *
     * @see HomeController::tricksAddAction()
     * @see TricksManager::addTrick()
     * @see TricksType
     * @see TricksListeners::prePersist()
     * @see TricksListeners::postPersist()
     */
    public function testsTricksAddWithoutLogin()
    {
        $crawler = $this->client->request('GET', '/tricks/add');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $form = $crawler->selectButton('submit')->form();

            $form['tricks[name]'] = 'BackAir';
            $form['tricks[groups]']->select('Grabs');
            $form['tricks[resume]'] = 'A new content about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(
                Response::HTTP_OK,
                $this->client->getResponse()->getStatusCode()
            );
        }
    }
}
