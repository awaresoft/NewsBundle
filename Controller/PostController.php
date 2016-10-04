<?php

namespace Awaresoft\Sonata\NewsBundle\Controller;

use Sonata\NewsBundle\Entity\PostManager;
use Sonata\NewsBundle\Model\PostInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\NewsBundle\Controller\PostController as BasePostController;

/**
 * Class PostController
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PostController extends BasePostController
{
    const DEFAULT_LIST_LIMIT = 6;

    /**
     * Extended method
     *
     * @return Response
     */
    public function homeAction()
    {
        return $this->renderArchive();
    }

    /**
     * Render archive with built in ajax support
     *
     * @param array $criteria
     * @param array $parameters
     * @param Request $request
     *
     * @return Response
     */
    public function renderArchive(array $criteria = [], array $parameters = [], Request $request = null)
    {
        $request = $this->resolveRequest($request);

        $limit = self::DEFAULT_LIST_LIMIT;
        $limitFromSetting = $this->get('awaresoft.setting')->getField('NEWS', 'LIST_LIMIT');
        $site = $this->get('sonata.page.site.selector')->retrieve();

        if ($limitFromSetting && $limitFromSetting->getValue() > 0) {
            $limit = $limitFromSetting->getValue();
        }

        if ($site) {
            $criteria['site'] = $site;
        }

        $criteria['date']['query'] = 'p.publicationDateStart <= :now';
        $criteria['date']['params'] = ['now' => new \DateTime()];

        $pager = $this->getPostManager()->getPager(
            $criteria,
            $request->get('page', 1),
            $limit
        );

        $parameters = array_merge([
            'pager' => $pager,
            'blog' => $this->get('sonata.news.blog'),
            'tag' => false,
            'collection' => isset($criteria['collection']) ? $criteria['collection'] : false,
            'route' => $request->get('_route'),
            'route_parameters' => $request->get('_route_params'),
        ], $parameters);

        if ('rss' === $request->getRequestFormat()) {
            $response = $this->render(sprintf('SonataNewsBundle:Post:archive.%s.twig', $request->getRequestFormat()), $parameters);
            $response->headers->set('Content-Type', 'application/rss+xml');

            return $response;
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'template' => $this->renderView('AwaresoftSonataNewsBundle:Post/Helper:posts.html.twig', $parameters),
            ]);
        }

        return $this->render('SonataNewsBundle:Post:archive.html.twig', $parameters);
    }

    /**
     * Extended method
     *
     * @param $permalink
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function viewAction($permalink)
    {
        $site = $this->get('sonata.page.site.selector')->retrieve();
        $em = $this->getEntityManager();
        $postRepo = $em->getRepository('AwaresoftSonataNewsBundle:Post');
        $translator = $this->get('translator');
        $post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'));

        if (!$post || (!$post->isPublic() && !$this->isGranted("ROLE_ADMIN"))) {
            throw $this->createNotFoundException($translator->trans('site_not_exists'));
        }

        if ($site !== $post->getSite()) {
            throw $this->createNotFoundException();
        }

        if ($seoPage = $this->getSeoPage()) {
            $seoPage
                ->setTitle($post->getMetaTitle())
                ->addMeta('name', 'description', $post->getMetaDescription())
                ->addMeta('property', 'og:title', $post->getMetaTitle())
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:url', $this->generateUrl('sonata_news_view', [
                    'permalink' => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                ], true))
                ->addMeta('property', 'og:description', $post->getMetaDescription());
        }

        $nextPost = $postRepo->findNext($post, $site);
        $prevPost = $postRepo->findPrev($post, $site);
        $files = $post->getFilesEnabled();
        $this->preparePostSeo($post);

        $response = $this->render('SonataNewsBundle:Post:view.html.twig', [
            'post' => $post,
            'files' => $files,
            'form' => false,
            'blog' => $this->get('sonata.news.blog'),
            'nextPost' => $nextPost,
            'prevPost' => $prevPost,
        ]);

        $statCookie = $this->getRequest()->cookies->get('stat');
        if (!isset($statCookie['post'][$post->getId()])) {
            $post->addVisit();
            $cookie = new Cookie(sprintf('stat[post][%d]', $post->getId()), true);
            $response->headers->setCookie($cookie);
            $em->flush($post);
        }

        return $response;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return PostManager
     */
    protected function getPostManager()
    {
        return $this->get('sonata.news.manager.post');
    }

    /**
     * Add Post seo metatags
     *
     * @param PostInterface $post
     */
    protected function preparePostSeo(PostInterface $post)
    {
        $this->container->get('sonata.seo.page')
            ->addMeta(
                'property',
                'og:image',
                $this->getRequest()->getSchemeAndHttpHost() .
                $this->getRequest()->getBaseUrl() .
                $this->container->get('sonata.media.twig.extension')->path($post->getImage(), 'big')
            );
    }

    /**
     * To keep backwards compatibility with older Sonata News code.
     *
     * @internal
     *
     * @param Request $request
     *
     * @return Request
     */
    private function resolveRequest(Request $request = null)
    {
        if (null === $request) {
            return $this->getRequest();
        }

        return $request;
    }
}
