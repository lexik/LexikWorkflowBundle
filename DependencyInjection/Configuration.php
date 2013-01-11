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
                        ->validate()
                            ->ifTrue(function($value) {
                                return !empty($value['import']) && !empty($value['steps']);
                            })
                            ->thenInvalid('You can\'t use "import" and "steps" keys at the same time.')
                        ->end()
                        ->children()
                            ->scalarNode('import')
                                ->defaultNull()
                            ->end()

                            ->scalarNode('start')
                                ->defaultNull()
                            ->end()

                            ->arrayNode('end')
                                ->defaultValue(array())
                                ->prototype('scalar')->end()
                            ->end()

                            ->arrayNode('steps')
                                ->defaultValue(array())
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

                                        ->arrayNode('model_status')
                                            ->validate()
                                                ->ifTrue(function($value) {
                                                    return (is_array($value) && count($value) < 2);
                                                })
                                                ->thenInvalid('You must specify an array with [ method, constant ]')
                                                ->ifTrue(function($value) {
                                                    return ( ! defined($value[1]));
                                                })
                                                ->thenInvalid('You must specify a valid constant name as second parameter')
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

                                        ->scalarNode('on_invalid')
                                            ->defaultNull()
                                        ->end()

                                        ->arrayNode('next_states')
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

                                                    ->arrayNode('validations')
                                                        ->validate()
                                                            ->ifTrue(function($value) use ($validatorSyntax) {
                                                                return (is_array($value) && $validatorSyntax($value));
                                                            })
                                                            ->thenInvalid('You must specify valid validation name as serviceId:method string')
                                                        ->end()
                                                        ->prototype('scalar')->end()
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
