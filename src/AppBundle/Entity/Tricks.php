<?php

/*
 * This file is part of the Snowtricks project.
 *
 * (c) Guillaume Loulier <guillaume.loulier@hotmail.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\User;

/**
 * Tricks.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TricksRepository")
 * @ORM\Table("_tricks")
 */
class Tricks
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="groups", type="string", length=100, nullable=false)
     */
    private $groups;

    /**
     * @var string
     *
     * @ORM\Column(name="resume", type="text", nullable=false)
     */
    private $resume;

    /**
     * @var array
     *
     * @ORM\Column(name="images", type="array", nullable=true)
     */
    private $images;

    /**
     * @var array
     *
     * @ORM\Column(name="videos", type="array", nullable=true)
     */
    private $videos;

    /**
     * @var bool
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $published;

    /**
     * @var bool
     *
     * @ORM\Column(name="validated", type="boolean")
     */
    private $validated;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="tricks", cascade={"persist"})
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Commentary", mappedBy="tricks", cascade={"persist", "remove"})
     */
    private $commentary;

    /**
     * @var array
     *
     * @ORM\Column(name="current_state", type="array", nullable=false)
     */
    public $currentState;

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
     * Set name.
     *
     * @param string $name
     *
     * @return Tricks
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Tricks
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set groups.
     *
     * @param string $groups
     *
     * @return Tricks
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups.
     *
     * @return string
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set resume.
     *
     * @param string $resume
     *
     * @return Tricks
     */
    public function setResume($resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * Get resume.
     *
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Add image.
     *
     * @param string $image
     *
     * @return $this
     */
    public function addImage(string $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Set multiples images.
     *
     * @param array $images
     *
     * @return $this
     */
    public function setImages(array $images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Return a single image.
     *
     * @param string $image
     *
     * @return mixed
     */
    public function getImage($image)
    {
        return $this->images[] = $image;
    }

    /**
     * Get images.
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add videos.
     *
     * @param string $video
     *
     * @return $this
     */
    public function addVideos(string $video)
    {
        $this->videos[] = $video;

        return $this;
    }

    /**
     * Set videos.
     *
     * @param array $videos
     *
     * @return Tricks
     */
    public function setVideos(array $videos)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Get videos.
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set published.
     *
     * @param bool $published
     *
     * @return Tricks
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set validated.
     *
     * @param bool $validated
     *
     * @return Tricks
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
     * Constructor.
     */
    public function __construct()
    {
        $this->commentary = new ArrayCollection();
    }

    /**
     * Add commentary.
     *
     * @param \AppBundle\Entity\Commentary $commentary
     *
     * @return Tricks
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
     * Set author.
     *
     * @param \UserBundle\Entity\User $author
     *
     * @return Tricks
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return \UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set currentState.
     *
     * @param array $currentState
     *
     * @return Tricks
     */
    public function setCurrentState($currentState)
    {
        $this->currentState = $currentState;

        return $this;
    }

    /**
     * Get currentState.
     *
     * @return array
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * Allow to check if the user connected is the author.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function isAuthor(User $user = null)
    {
        return $user === $this->author;
    }
}
