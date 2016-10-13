<?php

namespace Awaresoft\Sonata\NewsBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Gedmo\Sluggable\Util\Urlizer;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\NewsBundle\Permalink\PermalinkInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\FormatterBundle\Formatter\Pool as FormatterPool;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\UserBundle\Model\UserManagerInterface;

/**
 * Class PostAdmin rewrite functionallity from Sonata PostAdmin
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PostAdmin extends AwaresoftAbstractAdmin
{
    /**
     * @inheritdoc
     */
    protected $multisite = true;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var Pool
     */
    protected $formatterPool;

    /**
     * @var PermalinkInterface
     */
    protected $permalinkGenerator;

    /**
     * Abstract field max length
     */
    const ABSTRACT_MAX_LENGTH = 500;

    /**
     * @param UserManagerInterface $userManager
     */
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param FormatterPool $formatterPool
     */
    public function setPoolFormatter(FormatterPool $formatterPool)
    {
        $this->formatterPool = $formatterPool;
    }

    /**
     * @param PermalinkInterface $permalinkGenerator
     */
    public function setPermalinkGenerator(PermalinkInterface $permalinkGenerator)
    {
        $this->permalinkGenerator = $permalinkGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($post)
    {
        $post->setSlug(Urlizer::urlize($post->getTitle()));
        $post->setContent($this->formatterPool->transform($post->getContentFormatter(), $post->getRawContent()));
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($post)
    {
        $post->setSlug(Urlizer::urlize($post->getTitle()));
        $post->setContent($this->formatterPool->transform($post->getContentFormatter(), $post->getRawContent()));
        $this->updateCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement->with('abstract')
            ->assertLength(['max' => self::ABSTRACT_MAX_LENGTH])
            ->end();

        $duplicate = $this->container->get('sonata.news.manager.post')->findOneBy([
            'slug' => \Gedmo\Sluggable\Util\Urlizer::urlize($object->getTitle()),
        ]);

        if ($duplicate && $duplicate !== $object) {
            $errorElement->with('title')
                ->addViolation($this->trans('news.admin.unique_name.constraint'))
                ->end();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('author')
            ->add('enabled')
            ->add('title')
            ->add('abstract')
            ->add('content', null, ['safe' => true])
            ->add('tags');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->add('custom', 'string', [
            'template' => 'SonataNewsBundle:Admin:list_post_custom.html.twig',
            'label' => 'Post',
        ])
            ->add('site')
            ->add('enabled', null, ['editable' => true])
            ->add('publicationDateStart');

        $listMapper->add('_action', 'actions', [
            'actions' => [
                'show' => [],
                'edit' => [],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('site')
            ->add('collections')
            ->add('title')
            ->add('content')
            ->add('enabled');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $imageRequired = false;

        $formMapper->with($this->trans('admin.admin.form.group.main'), ['class' => 'col-md-8'])->end()
            ->with($this->trans('admin.admin.form.group.classification'), ['class' => 'col-md-8'])->end()
            ->with($this->trans('admin.admin.form.group.seo'), ['class' => 'col-xs-12 col-md-4 pull-right'])->end()
            ->with($this->trans('admin.admin.form.group.status'), ['class' => 'col-md-4'])->end()
            ->with($this->trans('admin.admin.form.group.media') . ' #1', ['class' => 'col-xs-12 col-md-4 pull-right'])->end()
            ->with($this->trans('admin.admin.form.group.media') . ' #2', ['class' => 'col-xs-12 col-md-4 pull-right clear-right'])->end()
            ->with($this->trans('admin.admin.form.group.files'), ['class' => 'col-xs-12'])->end();

        $formMapper->with($this->trans('admin.admin.form.group.main'))
            ->add('title', null, [
                'disabled' => false,
            ]);

        $formMapper->add('site', null, ['required' => true, 'read_only' => true]);

        $formMapper->add('abstract', 'textarea', [
            'attr' => [
                'rows' => 5,
                'max_length' => self::ABSTRACT_MAX_LENGTH,
            ],
        ])
            ->add('content', 'sonata_formatter_type', [
                'event_dispatcher' => $formMapper->getFormBuilder()
                    ->getEventDispatcher(),
                'format_field' => 'contentFormatter',
                'source_field' => 'rawContent',
                'source_field_options' => [
                    'horizontal_input_wrapper_class' => $this->getConfigurationPool()
                        ->getOption('form_type') == 'horizontal' ? 'col-lg-12' : '',
                    'attr' => [
                        'class' => $this->getConfigurationPool()
                            ->getOption('form_type') == 'horizontal' ? 'span10 col-sm-10 col-md-10' : '',
                        'rows' => 20,
                    ],
                ],
                'ckeditor_context' => 'default',
                'target_field' => 'content',
                'listener' => true,
            ])
            ->end();

        $formMapper->with($this->trans('admin.admin.form.group.status'))
            ->add('enabled', null, ['required' => false])
            ->add('publicationDateStart', 'sonata_type_datetime_picker', [
                'dp_side_by_side' => true,
            ])
            ->end()
            ->with($this->trans('admin.admin.form.group.media') . ' #1')
            ->add('image', 'sonata_media_type', [
                'provider' => 'sonata.media.provider.image',
                'context' => 'news',
                'required' => $imageRequired,
            ])
            ->end()
            ->with($this->trans('admin.admin.form.group.media') . ' #2')
            ->add('gallery', 'sonata_type_model_list', ['required' => false], ['link_parameters' => ['context' => 'news']])
            ->end()
            ->with($this->trans('admin.admin.form.group.seo'))
            ->add('meta_title', 'text', [
                'max_length' => AwaresoftAbstractAdmin::SEO_TITLE_MAX_LENGTH,
                'required' => true,
            ])
            ->add('meta_description', 'textarea', [
                'max_length' => AwaresoftAbstractAdmin::SEO_DESCRIPTION_MAX_LENGTH,
            ])
            ->end();

        $formMapper->with($this->trans('admin.admin.form.group.files'))
            ->add('files', 'sonata_type_collection', [
                'required' => false,
                'by_reference' => false,
                'label' => false,
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
            ])
            ->end();

        $formMapper
            ->with($this->trans('admin.admin.form.group.classification'), [
                'class' => 'col-md-4',
            ])
//            ->add('tags', 'sonata_type_model_autocomplete', array(
//                'property' => 'name',
//                'multiple' => 'true'
//            ))
            ->add('collections', null, [
                'required' => true,
            ])
            ->end();

        $formMapper->setHelps([
            'tags' => $this->trans('admin.admin.help.tags'),
            'image' => $this->trans('admin.admin.help.media_info'),
            'meta_title' => $this->trans('admin.admin.help.meta_title'),
            'meta_description' => $this->trans('admin.admin.help.meta_description'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        if ($this->hasSubject() && $this->getSubject()->getId() !== null) {
            $menu->addChild($this->trans('sidemenu.link_view_post'), [
                'uri' => $admin->getRouteGenerator()
                    ->generate('sonata_news_view', ['permalink' => $this->permalinkGenerator->generate($this->getSubject())]),
            ]);
        }
    }
}
