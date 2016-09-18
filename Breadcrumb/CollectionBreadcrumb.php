<?php

namespace Awaresoft\Sonata\NewsBundle\Breadcrumb;

use Application\MainBundle\Breadcrumb\PageBreadcrumb;
use Awaresoft\BreadcrumbBundle\Breadcrumb\AbstractBreadcrumb;
use Awaresoft\BreadcrumbBundle\Breadcrumb\BreadcrumbItem;

class CollectionBreadcrumb extends AbstractBreadcrumb
{
    /**
     * @inheritdoc
     */
    public function create()
    {
        $parentPage = $this->getPageRepository()->findOneBySiteAndRoute($this->site, 'sonata_news_home');
        $collection = $this->container->get('sonata.classification.manager.collection')->findOneBy([
            'slug' => $this->request->get('collection'),
            'enabled' => true,
        ]);

        $parentBreadcrumb = new PageBreadcrumb($this->container);
        $parentBreadcrumb->setPage($parentPage);
        $breadcrumbs = $parentBreadcrumb->create();

        if (!$collection) {
            return $breadcrumbs;
        }

        $item = new BreadcrumbItem();
        $item->setName($collection->getName());
        $item->setActive(false);
        $breadcrumbs[] = $item;

        return $breadcrumbs;
    }
}
