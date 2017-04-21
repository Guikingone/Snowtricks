<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Entity;

// Entities
use AppBundle\Entity\Tricks;
use AppBundle\Entity\Commentary;

// ORM
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

// Serializer
use Symfony\Component\Serializer\Annotation\Groups;

// Security
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @ORM\Table("_user")
 *
 * @UniqueEntity(fields="email", message="L'adresse email est déjà utilisée.")
 * @UniqueEntity(fields="username", message="Le pseudonyme est déjà utilisé.")
 */
class User implements
    AdvancedUserInterface,
    \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"commentaries", "author"})
     */
    private $id;

    /**
     * @var string
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="firstname", type="string", length=155, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="lastname", type="string", length=155, nullable=true)
     */
    private $lastname;

    /**
     * @var \DateTime
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="birthdate", type="datetime", nullable=true)
     */
    private $birthdate;

    /**
     * @var string
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="occupation", type="string", length=200, nullable=true)
     */
    private $occupation;

    /**
     * @var string
     *
     * @Groups({"commentaries", "author"})
     *
     * @ORM\Column(name="username", type="string", length=100, nullable=false)
     */
    private $username;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @Groups({"commentaries", "author"})
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var array
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="roles", type="array", nullable=true)
     */
    private $roles;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=36, nullable=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", nullable=true)
     */
    private $apiKey;

    /**
     * @var bool
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="validated", type="boolean")
     */
    private $validated;

    /**
     * @var bool
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked;

    /**
     * @var bool
     *
     * @Groups({"author"})
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Commentary", mappedBy="author")
     */
    private $commentary;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tricks", mappedBy="author")
     * @Groups({"tricks"})
     */
    private $tricks;

    /**
     * @var array
     *
     * @ORM\Column(name="current_status", type="array")
     */
    public $currentStatus;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tricks = new ArrayCollection();
        $this->commentary = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthdate.
     *
     * @param \DateTime $birthdate
     *
     * @return User
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate.
     *
     * @return \DateTime
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set occupation.
     *
     * @param string $occupation
     *
     * @return User
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation.
     *
     * @return string
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get plainPassword.
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set plainPassword.
     *
     * @param $password
     *
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set apiKey.
     *
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set validated.
     *
     * @param bool $validated
     *
     * @return User
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get validated.
     *
     * @return bool
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set locked.
     *
     * @param bool $locked
     *
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked.
     *
     * @return bool
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set isActive.
     *
     * @param bool $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Add trick.
     *
     * @param \AppBundle\Entity\Tricks $trick
     *
     * @return User
     */
    public function addTrick(Tricks $trick)
    {
        $this->tricks[] = $trick;

        return $this;
    }

    /**
     * Remove trick.
     *
     * @param \AppBundle\Entity\Tricks $trick
     */
    public function removeTrick(Tricks $trick)
    {
        $this->tricks->removeElement($trick);
    }

    /**
     * Get tricks.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTricks()
    {
        return $this->tricks;
    }

    /**
     * Add commentary.
     *
     * @param \AppBundle\Entity\Commentary $commentary
     *
     * @return User
     */
    public function addCommentary(Commentary $commentary)
    {
        $this->commentary[] = $commentary;

        return $this;
    }

    /**
     * Remove commentary.
     *
     * @param \AppBundle\Entity\Commentary $commentary
     */
    public function removeCommentary(Commentary $commentary)
    {
        $this->commentary->removeElement($commentary);
    }

    /**
     * Get commentary.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommentary()
    {
        return $this->commentary;
    }

    /**
     * Set currentStatus.
     *
     * @param array $currentStatus
     *
     * @return User
     */
    public function setCurrentStatus($currentStatus)
    {
        $this->currentStatus = $currentStatus;

        return $this;
    }

    /**
     * Get currentStatus.
     *
     * @return array
     */
    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->active;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->active,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->active) = unserialize($serialized);
    }
}
