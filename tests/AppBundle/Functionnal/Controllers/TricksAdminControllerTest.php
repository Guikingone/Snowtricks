<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\AppBundle\Functionnal\Controllers;

use AppBundle\Controller\Web\TricksAdminController;
use AppBundle\Events\Tricks\TricksRefusedEvent;
use AppBundle\Events\Tricks\TricksValidatedEvent;
use AppBundle\Listeners\TricksListeners;
use AppBundle\Managers\TricksManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
        $this->client = static::createClient();
    }

    /** Only for authentication purpose */
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';

        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
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
        $this->logIn();

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
        $this->logIn();

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
        $this->logIn();

        $this->client->request('GET', '/admin/tricks/refuse/TruckDriver');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the refuse process of a tricks using a bad argument.
     *
     * @see TricksAdminController::tricksRefusedAction()
     * @see TricksManager::refuseTricks()
     */
    public function testTricksRefusedWithBadArgument()
    {
        $this->logIn();

        $this->client->request('GET', '/admin/tricks/refuse/234');

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
        $this->logIn();

        $crawler = $this->client->request('GET', '/admin/tricks/update/TruckDriver');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        if ($this->client->getResponse()->getStatusCode() === 200) {
            $form = $crawler->selectButton('submit')->form();

            $form['update_tricks[name]'] = 'Airflip';
            $form['update_tricks[groups]']->select('Flip');
            $form['update_tricks[resume]'] = 'A new content about this tricks !';

            $crawler = $this->client->submit($form);

            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
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
        $this->logIn();

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
        $this->logIn();

        $this->client->request('GET', '/admin/tricks/delete/Bigfoot');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
