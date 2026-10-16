<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class AkawakaSyliusCMSExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    /** @psalm-suppress UnusedVariable */
    public function load(array $configs, ContainerBuilder $container): void
    {
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
