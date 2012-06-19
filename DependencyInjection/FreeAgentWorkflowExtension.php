<?php

namespace FreeAgent\WorkflowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

        foreach ($config['processes'] as $processName => $processConfig) {
            $stepReferences = array();

            // steps services
            foreach ($processConfig['steps'] as $stepName => $stepConfig) {
                // update target reference to service id
                foreach ($stepConfig['next_steps'] as $nextStepName => $nextStep) {
                    if ('step' === $nextStep['type']) {
                        $stepConfig['next_steps'][$nextStepName]['target'] = new Reference(sprintf('free_agent_workflow.process.%s.step.%s', $processName, $nextStepName));
                    }
                }

                $definition = new Definition('FreeAgent\WorkflowBundle\Flow\Step', array(
                    $stepName,
                    $stepConfig['label'],
                    $stepConfig['next_steps'],
                    $stepConfig['validations'],
                    $stepConfig['actions'],
                    $stepConfig['roles'],
                ));
                $definition->setPublic(false);

                $stepReference = sprintf('free_agent_workflow.process.%s.step.%s', $processName, $stepName);
                $container->setDefinition($stepReference, $definition);

                $stepReferences = new Reference($stepReference);
            }

            // process service
            $container->setDefinition(sprintf('free_agent_workflow.process.%s', $processName), new Definition('FreeAgent\WorkflowBundle\Step\Process', array(
                $processName,
                $stepReferences,
                $processConfig['start'],
                $processConfig['end'],
            )));
        }
    }
}
