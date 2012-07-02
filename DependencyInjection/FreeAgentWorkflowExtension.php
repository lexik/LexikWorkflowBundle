<?php

namespace FreeAgent\WorkflowBundle\DependencyInjection;

use FreeAgent\WorkflowBundle\Flow\State;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\Yaml\Parser;

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

        // inject processes into ProcessHandlerFactory (not possible from a CompilerPass because definitions are loaded from Extension class...)
        if ($container->hasDefinition('free_agent_workflow.process_handler_factory')) {
            $container->findDefinition('free_agent_workflow.process_handler_factory')->replaceArgument(0, $processReferences);
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
            ));

            $definition->addMethodCall('setSecurityContext', array(new Reference('security.context')))
                       ->setFactoryService(new Reference('free_agent_workflow.process_handler_factory'))
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
            if (!empty($processConfig['import'])) {
                if (is_file($processConfig['import'])) {
                    $yaml = new Parser();
                    $config = $yaml->parse(file_get_contents($processConfig['import']));

                    $processConfig = array_merge($processConfig, $config[$processName]);
                } else {
                    throw new \InvalidArgumentException(sprintf('Can\'t load process from file "%s"', $processConfig['import']));
                }
            }

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
            $validations = $this->convertToServiceReferences($stepConfig['validations']);
            $actions = $this->convertToServiceReferences($stepConfig['actions']);

            $definition = new Definition($stepClass, array(
                $stepName,
                $stepConfig['label'],
                array(),
                $validations,
                $actions,
                $stepConfig['model_status'],
                $stepConfig['roles'],
                $stepConfig['onInvalid'],
            ));

            $this->addStepNextStates($definition, $stepConfig['next_states'], $processName);

            $definition->setPublic(false)
                       ->addTag(sprintf('free_agent_workflow.process.%s.step', $processName), array('alias' => $stepName));

            $stepReference = sprintf('free_agent_workflow.process.%s.step.%s', $processName, $stepName);
            $container->setDefinition($stepReference, $definition);

            $stepReferences[$stepName] = new Reference($stepReference);
        }

        return $stepReferences;
    }

    /**
     * Add all next states to the step definition.
     *
     * @param Definition $step
     * @param array $stepsNextStates
     * @param string $processName
     * @throws \InvalidArgumentException
     */
    protected function addStepNextStates(Definition $step, $stepsNextStates, $processName)
    {
        foreach ($stepsNextStates as $stateName => $data) {
            $target = null;

            if (State::TYPE_STEP === $data['type']) {
                $target = new Reference(sprintf('free_agent_workflow.process.%s.step.%s', $processName, $data['target']));

            } else if (State::TYPE_PROCESS === $data['type']) {
                $target = new Reference(sprintf('free_agent_workflow.process.%s', $data['target']));

            } else {
                throw new \InvalidArgumentException(sprintf('Unknown type "%s", please use "step" or "process"', $data['type']));
            }

            $step->addMethodCall('addNextState', array(
                $stateName,
                $data['type'],
                $target,
                $this->convertToServiceReferences($data['validations'])
            ));
        }
    }

    /**
     * Convert "service.id:method" string to service reference object.
     *
     * @param array $serviceMethods
     * @return array
     */
    private function convertToServiceReferences(array $serviceMethods)
    {
        $references = array();

        foreach ($serviceMethods as $serviceMethod) {
            list($serviceId, $method) = explode(':', $serviceMethod);
            $references[] = array(new Reference($serviceId), $method);
        }

        return $references;
    }
}
