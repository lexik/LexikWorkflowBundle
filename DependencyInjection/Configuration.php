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

        $validatorSyntax = function(array $values) {
            foreach ($values as $value) {
                if (2 !== count($parts = explode(':', $value))) {
                    return true;
                }
            }
        };

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('process_handler_class')
                    ->defaultValue('FreeAgent\WorkflowBundle\Handler\ProcessHandler')
                ->end()
                ->scalarNode('flow_process_class')
                    ->defaultValue('FreeAgent\WorkflowBundle\Flow\Process')
                ->end()
                ->scalarNode('flow_step_class')
                    ->defaultValue('FreeAgent\WorkflowBundle\Flow\Step')
                ->end()

                ->arrayNode('processes')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('start')
                                ->defaultNull()
                            ->end()

                            ->arrayNode('end')
                                ->defaultValue(array())
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
                                            ->validate()
                                                ->ifTrue(function($value) use ($validatorSyntax) {
                                                    return (is_array($value) && $validatorSyntax($value));
                                                })
                                                ->thenInvalid('You must specify valid action name as serviceId:method string')
                                            ->end()
                                            ->prototype('scalar')->end()
                                        ->end()

                                        ->arrayNode('validations')
                                            ->validate()
                                                ->ifTrue(function($value) use ($validatorSyntax) {
                                                    return (is_array($value) && $validatorSyntax($value));
                                                })
                                                ->thenInvalid('You must specify valid validation name as serviceId:method string')
                                            ->end()
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

                                                    ->scalarNode('target')
                                                        ->cannotBeEmpty()
                                                    ->end()

                                                    ->scalarNode('onInvalid')
                                                        ->defaultNull()
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

            ->end()
        ;

        return $treeBuilder;
    }
}
