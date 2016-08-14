<?php

namespace Awaresoft\Sonata\NewsBundle\DataFixtures\ORM;

use Awaresoft\Doctrine\Common\DataFixtures\AbstractFixture as AwaresoftAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Awaresoft\SettingBundle\Entity\Setting;
use Awaresoft\SettingBundle\Entity\SettingHasField;

/**
 * Class LoadDynamicBlockData
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class LoadNewsData extends AwaresoftAbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironments()
    {
        return array('dev', 'prod');
    }

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->createSettings($manager);
    }

    /**
     * Create dynamic block settting
     *
     * @param ObjectManager $manager
     */
    protected function createSettings(ObjectManager $manager)
    {
        $setting = new Setting();
        $setting
            ->setName('NEWS')
            ->setEnabled(true)
            ->setHidden(true)
            ->setInfo('News module parameters.');
        $manager->persist($setting);

        $settingField = new SettingHasField();
        $settingField->setSetting($setting);
        $settingField->setName('LIST_LIMIT');
        $settingField->setValue('10');
        $settingField->setInfo('Number of posts displaying in list view.');
        $settingField->setEnabled(true);
        $manager->persist($settingField);

        $manager->flush();
    }
}
