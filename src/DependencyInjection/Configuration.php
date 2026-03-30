<?php

namespace InSquare\OpendxpFaviconBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('in_square_opendxp_favicon');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('theme_color')
                    ->defaultValue('#ffffff')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('tile_color')
                    ->defaultValue('#ffffff')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('manifest_name')
                    ->defaultValue('App')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('manifest_base_path')
                    ->defaultValue('/favicon')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
