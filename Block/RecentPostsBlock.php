<?php

namespace Awaresoft\Sonata\NewsBundle\Block;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\ClassificationBundle\Admin\CollectionAdmin;
use Sonata\ClassificationBundle\Model\CollectionInterface;
use Sonata\NewsBundle\Block\RecentPostsBlockService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RecentPostsBlock extends RecentPostsBlockService
{
    /**
     * @var CollectionAdmin
     */
    protected $collectionAdmin;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);

        $resolver->setDefault('collectionId', null);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        if (!$block->getSetting('collectionId') instanceof CollectionInterface) {
            $this->load($block);
        }

        $formMapper->add('settings', 'sonata_type_immutable_array', [
            'keys' => [
                ['number', 'integer', [
                    'required' => true,
                    'label' => 'form.label_number',
                ]],
                ['title', 'text', [
                    'required' => false,
                    'label' => 'form.label_title',
                ]],
                [$this->getCollectionBuilder($formMapper), null, [
                    'required' => false,
                ]],
            ],
            'translation_domain' => 'SonataNewsBundle',
        ]);
    }

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

        if ($blockContext->getBlock()->getSetting('collectionId')) {
            $criteria['collection'] = $blockContext->getBlock()->getSetting('collectionId');
        }

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

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $collectionId = $block->getSetting('collectionId', null);

        if (is_int($collectionId)) {
            $collection = $this->container->get('sonata.classification.manager.collection')->find($collectionId);
            $block->setSetting('collectionId', $collection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('collectionId', is_object($block->getSetting('collectionId')) ? $block->getSetting('collectionId')->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('collectionId', is_object($block->getSetting('collectionId')) ? $block->getSetting('collectionId')->getId() : null);
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormMapper $formMapper
     *
     * @return FormBuilder
     */
    protected function getCollectionBuilder(FormMapper $formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->getCollectionAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->getCollectionAdmin()->getClass(), 'post');
        $fieldDescription->setAssociationAdmin($this->getCollectionAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping([
            'fieldName' => 'collection',
            'type' => ClassMetadataInfo::MANY_TO_ONE,
        ]);

        return $formMapper->create('collectionId', 'sonata_type_model_list', [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getCollectionAdmin()->getClass(),
            'model_manager' => $this->getCollectionAdmin()->getModelManager(),
        ]);
    }

    /**
     * @return CollectionAdmin
     */
    protected function getCollectionAdmin()
    {
        if (!$this->collectionAdmin) {
            $this->collectionAdmin = $this->container->get('sonata.classification.admin.collection');

            return $this->collectionAdmin;
        }

        return $this->collectionAdmin;
    }
}
