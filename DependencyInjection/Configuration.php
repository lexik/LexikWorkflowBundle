<?php

namespace Lexik\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $rootNode = $treeBuilder->root('lexik_workflow');

        $rootNode
            ->addDefaultsIfNotSet()
            ->append($this->createClassesNodeDefinition())
            ->append($this->createProcessesNodeDefinition())
        ;

        return $treeBuilder;
    }

    /**
     * Create a configuration node to customize classes used by the bundle.
     *
     * @return ArrayNodeDefinition
     */
    private function createClassesNodeDefinition()
    {
        $classesNode = new ArrayNodeDefinition('classes');

        $classesNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('process_handler')
                    ->defaultValue('Lexik\Bundle\WorkflowBundle\Handler\ProcessHandler')
                ->end()
                ->scalarNode('process')
                    ->defaultValue('Lexik\Bundle\WorkflowBundle\Flow\Process')
                ->end()
                ->scalarNode('step')
                    ->defaultValue('Lexik\Bundle\WorkflowBundle\Flow\Step')
                ->end()
            ->end()
        ;

        return $classesNode;
    }

    /**
     * Create a configuration node to define processes.
     *
     * @return ArrayNodeDefinition
     */
    private function createProcessesNodeDefinition()
    {
        $processesNode = new ArrayNodeDefinition('processes');

        $processesNode
            ->useAttributeAsKey('name')
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
                ->end()
                ->append($this->createStepsNodeDefinition())
                //->append($this->createNextStatesNodeDefinition()) // @todo allow a process to have sub processes
            ->end()
        ;

        return $processesNode;
    }

    /**
     * Create a configuration node to define the steps of a process.
     *
     * @return ArrayNodeDefinition
     */
    private function createStepsNodeDefinition()
    {
        $stepsNode = new ArrayNodeDefinition('steps');

        $stepsNode
            ->defaultValue(array())
            ->useAttributeAsKey('name')
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

                    ->scalarNode('on_invalid')
                        ->defaultNull()
                    ->end()
                ->end()
                ->append($this->createNextStatesNodeDefinition())
            ->end()
        ;

        return $stepsNode;
    }

    /**
     * Create a configuration node to define available next states of a step (or a processs)
     *
     * @return ArrayNodeDefinition
     */
    private function createNextStatesNodeDefinition()
    {
        $flowTypes = array('step', 'process');

        $nextStatesNode = new ArrayNodeDefinition('next_states');

        $nextStatesNode
            ->useAttributeAsKey('name')
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
                ->end()
            ->end()
        ;

        return $nextStatesNode;
    }
}
