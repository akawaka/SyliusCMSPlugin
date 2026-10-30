<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Menu;

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
            ->addChild('homepage', ['route' => 'akawaka_cms_admin_homepage_index', 'extras' => ['routes' => [
                ['route' => 'akawaka_cms_admin_homepage_create'],
                ['route' => 'akawaka_cms_admin_homepage_update'],
            ]]])
            ->setLabel('akawaka_cms.menu.homepage')
            ->setLabelAttribute('icon', 'tabler:home')
        ;

        $cms
            ->addChild('content_blocks', ['route' => 'akawaka_cms_admin_content_block_index', 'extras' => ['routes' => [
                ['route' => 'akawaka_cms_admin_content_block_create'],
                ['route' => 'akawaka_cms_admin_content_block_update'],
            ]]])
            ->setLabel('akawaka_cms.menu.content_blocks')
            ->setLabelAttribute('icon', 'tabler:layout-board')
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
