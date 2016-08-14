<?php

namespace Awaresoft\Sonata\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BaseComment as BaseComment;
use Doctrine\ORM\Mapping as ORM;

/**
 * Extended Comment (NewsBundle) Entity
 * @ORM\Entity
 * @ORM\Table(name="news__comment")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Comment extends BaseComment
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }
}