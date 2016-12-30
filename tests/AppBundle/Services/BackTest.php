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

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\Back;
use AppBundle\Entity\Tricks;
use AppBundle\Entity\Commentary;
use UserBundle\Entity\User;

/**
 * Class BackTest.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class BackTest extends KernelTestCase
{
    /**
     * @var Back
     */
    private $back;

    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstName('Arnaud');
        $author->setLastName('Duchemin');
        $author->setUsername('Duduche');
        $author->setRoles('ROLE_ADMIN');

        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setAuthor($author);
        $tricks->setCreationDate('26/12/2016');
        $tricks->setGroup('Flip');
        $tricks->setResume('A simple backflip content ...');

        self::bootKernel();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->doctrine->persist($tricks);
        $this->doctrine->flush();

        $this->back = static::$kernel->getContainer()->get('app.back');
    }

    /**
     * Test if the back service can be found and if he's the right class.
     */
    public function testBackServiceIsFound()
    {
        if (is_object($this->back)) {
            $this->assertInstanceOf(Back::class, $this->back);
        }
    }

    /**
     * Test if the return of the "entity found method" is correct.
     */
    public function testBackReturnEntityMethod()
    {
        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertInstanceOf(
                Tricks::class,
                $this->back->getTricksByName('Backflip')
            );

            // Store the return to test the value passed through an array.
            $commentaries = $this->back->getCommentariesBytricks('Backflip');
            $this->assertArrayHasKey('Backflip', $commentaries);

            // Store the return to test the value passed through the getters.
            $tricks = $this->back->getTricksByName('Backflip');
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
        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertInstanceOf(
                FormView::class,
                $this->back->addtricks(new Request())
            );
        }
    }

    /**
     * Test if the app.back service can validate a trick using his $id.
     */
    public function testBackValidationMethod()
    {
        $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                ->findOneBy(['name' => 'Backflip']);

        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertInstanceof(
                RedirectResponse::class,
                $this->back->validateTricks($trick->getId())
            );
        }
    }

    /**
     * Test if the app.back service can refuse a validation using the Trick $id.
     */
    public function testBackNoValidationMethod()
    {
        $trick = $this->doctrine->getRepository('AppBundle:Tricks')
                                ->findOneBy(['name' => 'Backflip']);

        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertEquals(
                RedirectResponse::class,
                $this->back->refuseValidation($trick->getId())
            );
        }
    }

    /**
     * Test if the app.back method for deleting a tricks works.
     */
    public function testBackTricksSuppressionMethod()
    {
        $tricks = $this->doctrine->getRepository('AppBundle:Tricks')
                                 ->findOneBy(['name' => 'Backflip']);

        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertInstanceOf(
                RedirectResponse::class,
                $this->back->deleteTricks($tricks->getId())
            );
        }
    }

    /**
     * Test if the app.back method to add a commentary works.
     */
    public function testBackCommentaryAddMethod()
    {
        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertInstanceOf(
                FormView::class,
                $this->back->addCommentary(new Request())
            );
        }
    }

    /**
     * Test if the app.back method who delete a commentary linked to a tricks
     * using his id and the tricks name works.
     */
    public function testBackCommentaryDeletingMethod()
    {
        if (is_object($this->back) && $this->back instanceof Back) {
            $this->assertInstanceOf(
                RedirectResponse::class,
                $this->back->deleteCommentary('Backflip', 2)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->doctrine->close();
        $this->doctrine = null;
    }
}
