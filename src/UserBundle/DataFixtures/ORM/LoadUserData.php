<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

// Entity
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Workflow\Exception\LogicException;
use UserBundle\Entity\User;

/**
 * Class LoadUserData.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class LoadUserData extends AbstractFixture implements
      OrderedFixtureInterface,
      ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /** {@inheritdoc} */
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
        // Create a user in order to simulate the authentication process.
        $author = new User();
        $author->setFirstname('Arnaud');
        $author->setLastname('Duchemin');
        $author->setUsername('Duduche');
        $author->setRoles(['ROLE_ADMIN']);
        $author->setPlainPassword('Lk__DTHE');
        $author->setBirthdate(new \DateTime());
        $author->setOccupation('Rally Driver');
        $author->setEmail('duduche@snowtricks.fr');
        $author->setToken('token_e61e26a5a42d3e85d');
        $author->setValidated(true);
        $author->setLocked(false);
        $author->setActive(true);

        // Create a user in order to simulate the authentication process.
        $author_II = new User();
        $author_II->setLastname('Loulier');
        $author_II->setFirstname('Guillaume');
        $author_II->setUsername('Guikingone');
        $author_II->setBirthdate(new \DateTime());
        $author_II->setRoles(['ROLE_ADMIN']);
        $author_II->setPlainPassword('Lk__DTHE');
        $author_II->setOccupation('Rally Driver');
        $author_II->setEmail('guik@guillaumeloulier.fr');
        $author_II->setToken('token_e61e26a5a42d3g9r4');
        $author_II->setValidated(true);
        $author_II->setLocked(false);
        $author_II->setActive(true);

        // Create a user in order to simulate the authentication process.
        $author_III = new User();
        $author_III->setFirstname('Hervé');
        $author_III->setLastname('Delafalaise');
        $author_III->setUsername('Vévé');
        $author_III->setRoles(['ROLE_USER']);
        $author_III->setPlainPassword('LODP_DIL');
        $author_III->setBirthdate(new \DateTime());
        $author_III->setOccupation('F1 Driver');
        $author_III->setEmail('veve@snowtricks.fr');
        $author_III->setToken('token_e61e26a5a42d34281');
        $author_III->setValidated(true);
        $author_III->setLocked(false);
        $author_III->setActive(true);

        // Create a user in order to simulate the authentication process.
        $author_IV = new User();
        $author_IV->setFirstname('Manon');
        $author_IV->setLastname('Delasource');
        $author_IV->setUsername('Nanon');
        $author_IV->setRoles(['ROLE_ADMIN']);
        $author_IV->setPlainPassword('lappd_dep');
        $author_IV->setBirthdate(new \DateTime());
        $author_IV->setOccupation('Professionnal jumper');
        $author_IV->setEmail('nanon@snowtricks.fr');
        $author_IV->setToken('token_e61e26a5a42d1247d');
        $author_IV->setValidated(true);
        $author_IV->setLocked(false);
        $author_IV->setActive(true);

        // Create a user in order to simulate the authentication process.
        $author_V = new User();
        $author_V->setFirstname('Maurice');
        $author_V->setLastname('Delacarrière');
        $author_V->setUsername('Momo');
        $author_V->setRoles(['ROLE_USER']);
        $author_V->setPlainPassword('ldpalpd');
        $author_V->setBirthdate(new \DateTime());
        $author_V->setOccupation('Professionnal runner');
        $author_V->setEmail('momo@snowtricks.fr');
        $author_V->setToken('token_e61e26a5a42d12875');
        $author_V->setValidated(true);
        $author_V->setLocked(false);
        $author_V->setActive(true);

        $workflow = $this->container->get('workflow.user_process');
        $encoder = $this->container->get('security.password_encoder');

        $password = $encoder->encodePassword($author, $author->getPlainPassword());
        $author->setPassword($password);

        $password = $encoder->encodePassword($author_II, $author_II->getPlainPassword());
        $author_II->setPassword($password);

        $password = $encoder->encodePassword($author_III, $author_III->getPlainPassword());
        $author_III->setPassword($password);

        $password = $encoder->encodePassword($author_IV, $author_IV->getPlainPassword());
        $author_IV->setPassword($password);

        $password = $encoder->encodePassword($author_V, $author_V->getPlainPassword());
        $author_V->setPassword($password);

        $workflow->apply($author, 'register_phase');
        $workflow->apply($author_II, 'register_phase');
        $workflow->apply($author_III, 'register_phase');
        $workflow->apply($author_III, 'validation_phase');
        $workflow->apply($author_IV, 'register_phase');
        $workflow->apply($author_IV, 'validation_phase');
        $workflow->apply($author_V, 'register_phase');
        $workflow->apply($author_V, 'validation_phase');

        $manager->persist($author);
        $manager->persist($author_II);
        $manager->persist($author_III);
        $manager->persist($author_IV);
        $manager->persist($author_V);
        $manager->flush();

        $this->addReference('user', $author);
        $this->addReference('userII', $author_II);
        $this->addReference('userIII', $author_III);
        $this->addReference('userIV', $author_IV);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
