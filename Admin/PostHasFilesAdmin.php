<?php

namespace Awaresoft\Sonata\NewsBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Validator\Constraints\slug;

/**
 * PostHasFilesAdmin class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PostHasFilesAdmin extends AwaresoftAbstractAdmin
{
    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('file', 'sonata_type_model_list', array('required' => false), array(
                'link_parameters' => array(
                    //'context' => 'custom_context',
                    'provider' => 'sonata.media.provider.file'
                )
            ))
            ->add('enabled', null, array('required' => false))
            ->add('position', 'hidden');
    }

    /**
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('file')
            ->add('position')
            ->add('enabled');
    }
}
