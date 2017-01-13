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

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use UserBundle\Entity\User;

/**
 * Tricks.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
class Tricks
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var string
     */
    private $groups;

    /**
     * @var string
     */
    private $resume;

    /**
     * @var array
     */
    private $images;

    /**
     * @var array
     */
    private $videos;

    /**
     * @var bool
     */
    private $published;

    /**
     * @var bool
     */
    private $validated;

    private $author;

    private $commentary;

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
     * Add image.
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
    public function setVideos($videos)
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
}
