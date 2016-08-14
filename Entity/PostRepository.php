<?php

namespace Awaresoft\Sonata\NewsBundle\Entity;

use Doctrine\ORM\NoResultException;
use Sonata\NewsBundle\Entity\BasePostRepository;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * Repository of Post Entity
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PostRepository extends BasePostRepository
{
    /**
     * Return one previous Post or null
     *
     * @param Post $post
     * @param SiteInterface $site
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPrev(Post $post, SiteInterface $site)
    {
        $gb = $this->createQueryBuilder('n')
            ->where('n.publicationDateStart < :publicationDateStart')
            ->andWhere('n.enabled = 1')
            ->andWhere('n.site = :site')
            ->orderBy('n.publicationDateStart', 'DESC')
            ->setParameter('publicationDateStart', $post->getPublicationDateStart())
            ->setParameter('site', $site)
            ->setMaxResults(1);

        return $gb->getQuery()->getOneOrNullResult();
    }

    /**
     * Return one next Post or null
     *
     * @param Post $post
     * @param SiteInterface $site
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNext(Post $post, SiteInterface $site)
    {
        $gb = $this->createQueryBuilder('n')
            ->where('n.publicationDateStart > :publicationDateStart')
            ->andWhere('n.enabled = 1')
            ->andWhere('n.site = :site')
            ->orderBy('n.publicationDateStart', 'ASC')
            ->setParameter('publicationDateStart', $post->getPublicationDateStart())
            ->setParameter('site', $site)
            ->setMaxResults(1);

        return $gb->getQuery()->getOneOrNullResult();
    }
}