<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('akawaka_sylius_cms');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
