<?php

namespace Awaresoft\Sonata\NewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;


/**
 * AwaresoftSonataNewsBundle class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class AwaresoftSonataNewsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SonataNewsBundle';
    }
}