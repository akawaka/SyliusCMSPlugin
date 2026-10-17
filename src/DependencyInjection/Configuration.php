<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('akawaka_sylius_cms');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('hook_prefixes')
                    ->info('Twig hook namespaces content blocks can be attached to (e.g. add your application\'s own hooks).')
                    ->scalarPrototype()->cannotBeEmpty()->end()
                    ->defaultValue(['sylius_shop'])
                    ->requiresAtLeastOneElement()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
