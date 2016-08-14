<?php

namespace Awaresoft\Sonata\NewsBundle\Breadcrumb;

use Awaresoft\Sonata\NewsBundle\Entity\PostRepository;
use Awaresoft\Sonata\PageBundle\Entity\PageRepository;
use Awaresoft\BreadcrumbBundle\Breadcrumb\AbstractBreadcrumb;
use Awaresoft\BreadcrumbBundle\Breadcrumb\BreadcrumbItem;
use Application\MainBundle\Breadcrumb\PageBreadcrumb as PageBreadcrumb;
use Sonata\NewsBundle\Model\PostManagerInterface;

class NewsBreadcrumb extends AbstractBreadcrumb
{
    /**
     * @inheritdoc
     */
    public function create()
    {
        $parentPage = $this->getPageRepository()->findOneByRouteName('sonata_news_home');

        $parentBreadcrumb = new PageBreadcrumb($this->container);
        $parentBreadcrumb->setPage($parentPage);
        $breadcrumbs = $parentBreadcrumb->create();

        $permalink = $this->request->attributes->get('permalink');
        $post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'));

        if (!$post) {
            return $breadcrumbs;
        }

        $item = new BreadcrumbItem();
        $item->setName($post->getTitle());
        $item->setUrl($this->router->generate('sonata_news_view', array('permalink' => $permalink)));
        $item->setActive(true);
        $breadcrumbs[] = $item;

        return $breadcrumbs;
    }

    /**
     * @return PageRepository
     */
    protected function getPageRepository()
    {
        return $this->em->getRepository('AwaresoftSonataPageBundle:Page');
    }

    /**
     * @return PostRepository
     */
    protected function getPostRepository()
    {
        return $this->em->getRepository('AwaresoftSonataNewsBundle:Post');
    }

    /**
     * @return PostManagerInterface
     */
    protected function getPostManager()
    {
        return $this->container->get('sonata.news.manager.post');
    }
}
