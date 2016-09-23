<?php

namespace Awaresoft\Sonata\NewsBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\NewsBundle\Block\RecentPostsBlockService;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RecentPostsBlock extends RecentPostsBlockService
{
    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $criteria = [
            'mode' => $blockContext->getSetting('mode'),
        ];

        $criteria['date']['query'] = 'p.publicationDateStart <= :now';
        $criteria['date']['params'] = ['now' => new \DateTime()];

        $parameters = [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'pager' => $this->manager->getPager($criteria, 1, $blockContext->getSetting('number')),
            'admin_pool' => $this->adminPool,
        ];

        if ($blockContext->getSetting('mode') === 'admin') {
            return $this->renderPrivateResponse($blockContext->getTemplate(), $parameters, $response);
        }

        return $this->renderResponse($blockContext->getTemplate(), $parameters, $response);
    }
}
