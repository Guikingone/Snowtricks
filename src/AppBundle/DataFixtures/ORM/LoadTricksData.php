<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Tricks;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Workflow\Exception\LogicException;

/**
 * Class LoadTricksData.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadTricksData extends AbstractFixture implements
      OrderedFixtureInterface,
      ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws LogicException
     */
    public function load(ObjectManager $manager)
    {
        $tricks = new Tricks();
        $tricks->setName('Backflip');
        $tricks->setAuthor($this->getReference('author'));
        $tricks->setCreationDate(new \DateTime());
        $tricks->setGroups('Flip');
        $tricks->setResume('A simple backflip content ...');
        $tricks->setPublished(true);
        $tricks->setValidated(true);

        $tricksII = new Tricks();
        $tricksII->setName('Frontflip');
        $tricksII->setAuthor($this->getReference('author'));
        $tricksII->setCreationDate(new \DateTime());
        $tricksII->setGroups('Flip');
        $tricksII->setResume('A simple backflip content ...');
        $tricksII->setPublished(false);
        $tricksII->setValidated(false);

        $tricksIII = new Tricks();
        $tricksIII->setName('TruckDriver');
        $tricksIII->setAuthor($this->getReference('author'));
        $tricksIII->setCreationDate(new \DateTime());
        $tricksIII->setGroups('Grabs');
        $tricksIII->setResume('Look at this masterpiece !');
        $tricksIII->setPublished(false);
        $tricksIII->setValidated(false);

        $tricksIV = new Tricks();
        $tricksIV->setName('Bigfoot');
        $tricksIV->setAuthor($this->getReference('author'));
        $tricksIV->setCreationDate(new \DateTime());
        $tricksIV->setGroups('Rotations');
        $tricksIV->setResume('Feel like i\'m gonna be sick !');
        $tricksIV->setPublished(true);
        $tricksIV->setValidated(true);

        $tricksV = new Tricks();
        $tricksV->setName('Airflip');
        $tricksV->setAuthor($this->getReference('author'));
        $tricksV->setCreationDate(new \DateTime());
        $tricksV->setGroups('Flip');
        $tricksV->setResume('A simple airflip content ...');
        $tricksV->setPublished(false);
        $tricksV->setValidated(false);

        $tricksVI = new Tricks();
        $tricksVI->setName('Sideflip');
        $tricksVI->setAuthor($this->getReference('author'));
        $tricksVI->setCreationDate(new \DateTime());
        $tricksVI->setGroups('Flip');
        $tricksVI->setResume('A simple sideflip content ...');
        $tricksVI->setPublished(false);
        $tricksVI->setValidated(false);

        // Apply the workflow state foe every Entity.
        $workflow = $this->container->get('workflow.tricks_process');
        $workflow->apply($tricks, 'start_phase');
        $workflow->apply($tricks, 'validation_phase');
        $workflow->apply($tricksII, 'start_phase');
        $workflow->apply($tricksIII, 'start_phase');
        $workflow->apply($tricksIV, 'start_phase');
        $workflow->apply($tricksIV, 'validation_phase');
        $workflow->apply($tricksV, 'start_phase');
        $workflow->apply($tricksVI, 'start_phase');

        $manager->persist($tricks);
        $manager->persist($tricksII);
        $manager->persist($tricksIII);
        $manager->persist($tricksIV);
        $manager->persist($tricksV);
        $manager->flush();

        $this->addReference('tricks', $tricks);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
