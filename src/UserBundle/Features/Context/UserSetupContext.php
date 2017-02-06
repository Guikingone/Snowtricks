<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use UserBundle\Entity\User;

/**
 * Class UserSetupContext.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class UserSetupContext implements
      Context
{
    /** @var EntityManager */
    private $doctrine;

    /**
     * UserSetupContext constructor.
     *
     * @param EntityManager $doctrine
     */
    public function __construct(EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Given there are Users with the following details:
     *
     * @param TableNode $users
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function thereAreUsersWithTheFollowingDetails(TableNode $users)
    {
        $usr = new User();

        foreach ($users->getColumnsHash() as $key => $value) {
            $confirmationToken = isset($val['confirmation_token']) && $val['confirmation_token'] !== ''
                ? $val['confirmation_token']
                : null;

            $user = clone $usr;

            $user->setActive(true);
            $user->setUsername($val['username']);
            $user->setEmail($val['email']);
            $user->setPlainPassword($val['password']);
            $user->setToken($confirmationToken);

            if (!empty($confirmationToken)) {
                $user->setBirthdate(new \DateTime());
            }

            $this->doctrine->persist($user);
        }

        $this->doctrine->flush();
    }
}
