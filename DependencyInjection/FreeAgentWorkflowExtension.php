<?php

namespace FreeAgent\WorkflowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FreeAgentWorkflowExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('free_agent_workflow.process_handler_class', $config['process_handler_class']);

        // build process and factories definitions
        $processReferences = $this->buildProcesses($config['processes'], $container, $config['flow_process_class'], $config['flow_step_class']);
        $this->buildProcessHandlers($processReferences, $container, $config['process_handler_class']);

        // inject processes into ProcessManager (not possible from a CompilerPass because definitions are loaded from Extension class...)
        if ($container->hasDefinition('free_agent_workflow.process_manager')) {
            $container->findDefinition('free_agent_workflow.process_manager')->replaceArgument(0, $processReferences);
        }
    }

    /**
     * Build process handler (factories) definitions from configuration.
     *
     * @param array            $processReferences
     * @param ContainerBuilder $container
     * @param string           $processHandlerClass
     */
    protected function buildProcessHandlers($processReferences, $container, $processHandlerClass)
    {
        foreach ($processReferences as $processName => $processReference) {
            $definition = new Definition($processHandlerClass, array(
                $processName,
                new Reference('free_agent_workflow.model_storage'),
                new Reference('security.context'),
            ));
            $definition->setFactoryService(new Reference('free_agent_workflow.process_manager'))
                       ->setFactoryMethod('createProcessHandler');

            $container->setDefinition(sprintf('free_agent_workflow.handler.%s', $processName), $definition);
        }
    }

    /**
     * Build process definitions from configuration.
     *
     * @param array            $processes
     * @param ContainerBuilder $container
     * @param string           $processClass
     * @param string           $stepClass
     *
     * @return array
     */
    protected function buildProcesses($processes, $container, $processClass, $stepClass)
    {
        $processReferences = array();

        foreach ($processes as $processName => $processConfig) {
            $stepReferences = $this->buildSteps($processName, $processConfig['steps'], $container, $stepClass);

            $definition = new Definition($processClass, array(
                $processName,
                $stepReferences,
                $processConfig['start'],
                $processConfig['end'],
            ));

            $definition->setPublic(false)
                       ->addTag('free_agent_workflow.process', array('alias' => $processName));

            $processReference = sprintf('free_agent_workflow.process.%s', $processName);
            $container->setDefinition($processReference, $definition);

            $processReferences[$processName] = new Reference($processReference);
        }

        return $processReferences;
    }

    /**
     * Build steps definitions from configuration.
     *
     * @param string           $processName
     * @param array            $steps
     * @param ContainerBuilder $container
     * @param string           $stepClass
     *
     * @return array
     */
    protected function buildSteps($processName, $steps, $container, $stepClass)
    {
        $stepReferences = array();

        foreach ($steps as $stepName => $stepConfig) {
            // update target reference to service id
            foreach ($stepConfig['next_steps'] as $nextStepName => $nextStep) {
                if ('step' === $nextStep['type']) {
                    $stepConfig['next_steps'][$nextStepName]['target'] = new Reference(sprintf('free_agent_workflow.process.%s.step.%s', $processName, $nextStep['target']));
                } else if ('process' === $nextStep['type']) {
                    $stepConfig['next_steps'][$nextStepName]['target'] = new Reference(sprintf('free_agent_workflow.process.%s', $nextStep['target']));
                }
            }

            $definition = new Definition($stepClass, array(
                $stepName,
                $stepConfig['label'],
                $stepConfig['next_steps'],
                $stepConfig['validations'],
                $stepConfig['actions'],
                $stepConfig['roles'],
            ));

            $definition->setPublic(false)
                       ->addTag(sprintf('free_agent_workflow.process.%s.step', $processName), array('alias' => $stepName));

            $stepReference = sprintf('free_agent_workflow.process.%s.step.%s', $processName, $stepName);
            $container->setDefinition($stepReference, $definition);

            $stepReferences[$stepName] = new Reference($stepReference);
        }

        return $stepReferences;
    }
}
