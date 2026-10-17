<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\DependencyInjection;

use Akawaka\SyliusCMSPlugin\Block\BlockTypeInterface;
use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class AkawakaSyliusCMSExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('akawaka_cms.hook_prefixes', array_values($config['hook_prefixes']));

        // Custom block types (in the host application) are registered just by implementing the interface.
        $container->registerForAutoconfiguration(BlockTypeInterface::class)->addTag('akawaka_cms.block_type');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);

        $container->prependExtensionConfig('framework', [
            'html_sanitizer' => [
                'sanitizers' => [
                    'akawaka_cms.rich_text' => [
                        'allow_safe_elements' => true,
                        'allow_relative_links' => true,
                        'allow_relative_medias' => true,
                        // Formatting produced by the WYSIWYG editor (alignment, colors, table styling).
                        'allow_attributes' => [
                            'style' => ['p', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'ul', 'ol', 'li', 'table', 'tr', 'td', 'th'],
                            'class' => ['table', 'div', 'span', 'figure'],
                            'colspan' => ['td', 'th'],
                            'rowspan' => ['td', 'th'],
                        ],
                    ],
                ],
            ],
        ]);

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'AkawakaSyliusCMSPlugin' => [
                        'type' => 'attribute',
                        'is_bundle' => false,
                        'dir' => __DIR__ . '/../Entity',
                        'prefix' => 'Akawaka\\SyliusCMSPlugin\\Entity',
                        'alias' => 'AkawakaSyliusCMSPlugin',
                    ],
                ],
            ],
        ]);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Akawaka\\SyliusCMSPlugin\\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@AkawakaSyliusCMSPlugin/src/Migrations';
    }

    /** @return list<string> */
    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }
}
