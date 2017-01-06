<?php

namespace AppBundle\Entity;

use UserBundle\Entity\User;

/**
 * Commentary.
 */
class Commentary
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $publicationDate;

    /**
     * @var string
     */
    private $content;

    private $author;

    private $tricks;

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
     * Set publicationDate.
     *
     * @param \DateTime $publicationDate
     *
     * @return Commentary
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate.
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Commentary
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set tricks.
     *
     * @param \AppBundle\Entity\Tricks $tricks
     *
     * @return Commentary
     */
    public function setTricks(Tricks $tricks = null)
    {
        $this->tricks = $tricks;

        return $this;
    }

    /**
     * Get tricks.
     *
     * @return \AppBundle\Entity\Tricks
     */
    public function getTricks()
    {
        return $this->tricks;
    }

    /**
     * Set author.
     *
     * @param \UserBundle\Entity\User $author
     *
     * @return Commentary
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
