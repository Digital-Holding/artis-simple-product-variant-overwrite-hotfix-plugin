<?php

declare(strict_types=1);

namespace DH\ArtisSimpleProductVariantOverwriteHotfixPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('artis_simple_product_variant_overwrite_hotfix');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('artis_simple_product_variant_overwrite_hotfix');
        }


        $rootNode
            ->children()
            ->arrayNode('ignored_nodes')
            ->defaultValue(null)
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
