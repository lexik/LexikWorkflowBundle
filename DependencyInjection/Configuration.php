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

        $flowTypes = array('step', 'process');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('step_handler_class')
                    ->defaultValue('FreeAgent\WorkflowBundle\Handler\StepHandler')
                ->end()

                ->arrayNode('processes')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('start')->end()
                            ->arrayNode('end')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('steps')
                                ->isRequired()
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')
                                            ->defaultValue('')
                                        ->end()
                                        ->arrayNode('roles')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('actions')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('validations')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('next_steps')
                                            ->useAttributeAsKey('id')
                                            ->prototype('array')
                                                ->addDefaultsIfNotSet()
                                                ->children()
                                                    ->scalarNode('type')
                                                        ->defaultValue('step')
                                                        ->validate()
                                                             ->ifNotInArray($flowTypes)
                                                             ->thenInvalid('Invalid next element type "%s". Please use one of the following types: '.implode(', ', $flowTypes))
                                                        ->end()
                                                    ->end()
                                                    ->scalarNode('target')->end()
                                                ->end()
                                            ->end()
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
