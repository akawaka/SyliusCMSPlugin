<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $cms = $event->getMenu()
            ->addChild('akawaka_cms')
            ->setLabel('akawaka_cms.menu.cms')
            ->setLabelAttribute('icon', 'tabler:file-text')
        ;

        $cms
            ->addChild('pages', ['route' => 'akawaka_cms_admin_page_index', 'extras' => ['routes' => [
                ['route' => 'akawaka_cms_admin_page_create'],
                ['route' => 'akawaka_cms_admin_page_update'],
            ]]])
            ->setLabel('akawaka_cms.menu.pages')
            ->setLabelAttribute('icon', 'tabler:files')
        ;
    }
}
