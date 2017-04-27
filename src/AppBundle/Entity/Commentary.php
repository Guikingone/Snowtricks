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
use Symfony\Component\Serializer\Annotation\Groups;

// Entity
use UserBundle\Entity\User;

/**
 * Commentary.
 *
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentaryRepository")
 * @ORM\Table("_commentary")
 */
class Commentary
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"tricks", "commentaries", "users"})
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publication_date", type="datetime")
     *
     * @Groups({"tricks", "commentaries", "users"})
     */
    private $publicationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=255, nullable=false)
     *
     * @Groups({"tricks", "commentaries", "users"})
     */
    private $content;

    /**
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="commentary", cascade={"persist"})
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     *
     * @Groups({"tricks", "commentaries"})
     */
    private $author;

    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tricks", inversedBy="commentary", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="tricks_id", referencedColumnName="id")
     *
     * @Groups({"commentaries"})
     */
    private $tricks;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() : int
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
    public function setPublicationDate($publicationDate) : Commentary
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate.
     *
     * @return \DateTime
     */
    public function getPublicationDate() : \DateTime
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
    public function setContent($content) : Commentary
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent() : string
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
    public function setTricks(Tricks $tricks = null) : Commentary
    {
        $this->tricks = $tricks;

        return $this;
    }

    /**
     * Get tricks.
     *
     * @return \AppBundle\Entity\Tricks
     */
    public function getTricks() : Tricks
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
    public function setAuthor(User $author = null) : Commentary
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return \UserBundle\Entity\User
     */
    public function getAuthor() : User
    {
        return $this->author;
    }
}
