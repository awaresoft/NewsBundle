<?php

namespace Awaresoft\Sonata\NewsBundle\Entity;

use Awaresoft\Sonata\MediaBundle\Entity\Gallery;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Sonata\NewsBundle\Entity\BasePost as BasePost;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Extended Post (NewsBundle) Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="news__post", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uq_slug", columns={"slug"})
 * })
 * @ORM\Entity(repositoryClass="Awaresoft\Sonata\NewsBundle\Entity\PostRepository")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Post extends BasePost
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $meta_title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $meta_description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $meta_keywords;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\Sonata\MediaBundle\Entity\Gallery")
     *
     * @var Gallery
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     *
     * @var \Awaresoft\Sonata\MediaBundle\Entity\Media
     */
    protected $banner;

    /**
     * @ORM\OneToMany(targetEntity="Awaresoft\Sonata\NewsBundle\Entity\PostHasFiles", mappedBy="post", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $files;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $visits;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\Sonata\PageBundle\Entity\Site")
     *
     * @var Site
     */
    protected $site;

    /**
     * Post constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commentsDefaultStatus = 0;
        $this->files = new ArrayCollection();
        $this->visits = 0;
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->meta_title;
    }

    /**
     * @param string $meta_title
     */
    public function setMetaTitle($meta_title)
    {
        $this->meta_title = $meta_title;
    }

    /**
     * @return mixed
     */
    public function getMetaDescription()
    {
        return $this->meta_description;
    }

    /**
     * @param mixed $meta_description
     */
    public function setMetaDescription($meta_description)
    {
        $this->meta_description = $meta_description;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->meta_keywords;
    }

    /**
     * @param string $meta_keywords
     */
    public function setMetaKeywords($meta_keywords)
    {
        $this->meta_keywords = $meta_keywords;
    }

    /**
     * @return mixed
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @param mixed $gallery
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * @return \Awaresoft\Sonata\MediaBundle\Entity\Media
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param \Awaresoft\Sonata\MediaBundle\Entity\Media $banner
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $files
     */
    public function setFiles($files)
    {
        $this->files = new ArrayCollection();

        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    /**
     * @param PostHasFiles $file
     */
    public function addFile(PostHasFiles $file)
    {
        $file->setPost($this);
        $this->files[] = $file;
    }

    /**
     * Return enabled files
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function getFilesEnabled()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('enabled', true));

        return $this->files->matching($criteria);
    }

    /**
     * @return int
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * @param int $visits
     *
     * @return Post
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;

        return $this;
    }

    public function addVisit()
    {
        $this->visits++;

        return $this;
    }

    /**
     * @return SiteInterface
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param SiteInterface $site
     *
     * @return Post
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }
}