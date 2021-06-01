<?php

namespace Prokl\StaticPageMakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Prokl\StaticPageMakerBundle\DependencyInjection
 *
 * @since 23.01.2021
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('static_page_maker');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('post_type')->defaultValue('')->end()
                ->booleanNode('set_last_modified_header')->defaultValue(false)->end()
            ->end();

        return $treeBuilder;
    }
}
