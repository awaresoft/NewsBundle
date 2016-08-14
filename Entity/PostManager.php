<?php

namespace Awaresoft\Sonata\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\PostManager as BasePostManager;
use Sonata\ClassificationBundle\Model\CollectionInterface;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class PostManager
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PostManager extends BasePostManager
{
    /**
     * @inheritdoc
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        if (!isset($criteria['mode'])) {
            $criteria['mode'] = 'public';
        }

        $parameters = array();
        $query = $this->getRepository()
            ->createQueryBuilder('p')
            ->select('p, t')
            ->orderBy('p.publicationDateStart', 'DESC');

        if ($criteria['mode'] == 'admin') {
            $query
                ->leftJoin('p.tags', 't')
                ->leftJoin('p.author', 'a');
        } else {
            $query
                ->leftJoin('p.tags', 't', Join::WITH, 't.enabled = true')
                ->leftJoin('p.author', 'a', Join::WITH, 'a.enabled = true');
        }

        if (!isset($criteria['enabled']) && $criteria['mode'] == 'public') {
            $criteria['enabled'] = true;
        }

        if (isset($criteria['enabled'])) {
            $query->andWhere('p.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['date']) && isset($criteria['date']['query']) && isset($criteria['date']['params'])) {
            $query->andWhere($criteria['date']['query']);
            $parameters = array_merge($parameters, $criteria['date']['params']);
        }

        if (isset($criteria['tag'])) {
            $query->andWhere('t.slug LIKE :tag');
            $parameters['tag'] = (string)$criteria['tag'];
        }

        if (isset($criteria['author'])) {
            if (!is_array($criteria['author']) && stristr($criteria['author'], 'NULL')) {
                $query->andWhere('p.author IS ' . $criteria['author']);
            } else {
                $query->andWhere(sprintf('p.author IN (%s)', implode((array)$criteria['author'], ',')));
            }
        }

        if (isset($criteria['collection']) && $criteria['collection'] instanceof CollectionInterface) {
            $query->andWhere('p.collection = :collectionid');
            $parameters['collectionid'] = $criteria['collection']->getId();
        }

        if (isset($criteria['site'])) {
            $query->andWhere('p.site = :site');
            $parameters['site'] = $criteria['site'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }
}