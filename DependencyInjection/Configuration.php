<?php

namespace FreeAgent\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('free_agent_workflow');

        $rootNode
            ->fixXmlConfig('workflow')
            ->children()
                ->arrayNode('workflows')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('default_step')->end()
                            ->arrayNode('steps')
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('label')->end()
                                        ->arrayNode('actions')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('validators')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('possible_next_steps')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
