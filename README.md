FreeAgentWorkflowBundle
=======================

[![Build Status](https://secure.travis-ci.org/jeremyFreeAgent/FreeAgentWorkflowBundle.png)](http://travis-ci.org/jeremyFreeAgent/FreeAgentWorkflowBundle)

Simple workflow bundle for Symfony2


What is it ?
------------
### Workflow
A **Workflow** is a configuration that contains an array of *Step*. Foreach *Step* you must define :

- an array of **Action** to run task when the *Step* is reached
- an array of **Validation** to tell if the *Step* is reachable
- an array of possible next *Step*

### Action
An **Action** define what to do with the ***run()*** method.

### Validation
An **Validation** define what to validate and return the result with the ***validate()*** method.

Set up
------
### Create your **Workflow** configuration
In your ***config.yml*** :

```yaml
free_agent_workflow:
    workflows:
        example:
            default_step: draft
            validations:
                - free_agent_workflow.validation.pre_validation
                - free_agent_workflow.validation.pre_validation
            actions:
                - free_agent_workflow.action.post_action
                - free_agent_workflow.action.post_action
                - free_agent_workflow.action.post_action
            steps:
                draft:
                    label: Draft
                    actions:
                        - free_agent_workflow.action.example
                    validations:
                        - free_agent_workflow.validation.example
                        - free_agent_workflow.validation.example
                    possible_next_steps:
                        - removed
                        - validated
                removed:
                    label: Removed
                    actions:
                        - free_agent_workflow.action.example
                    validations:
                        - free_agent_workflow.validation.example
                        - free_agent_workflow.validation.example
                    possible_next_steps:
                        - draft
                validated:
                    label: Validated
                    actions:
                        - free_agent_workflow.action.example
                    validations:
                        - free_agent_workflow.validation.example
                    possible_next_steps:
                        - published
                        - removed
                        - draft
                published:
                    label: Published
                    actions:
                        - free_agent_workflow.action.example
                    validations:
                        - free_agent_workflow.validation.example
                        - free_agent_workflow.validation.example
                    possible_next_steps:
                        - unpublished
                        - removed
                        - draft
                unpublished:
                    label: Unpublished
                    actions:
                        - free_agent_workflow.action.example
                    validations:
                        - free_agent_workflow.validation.example
                        - free_agent_workflow.validation.example
                    possible_next_steps:
                        - published
                        - removed
                        - draft
        example_two:
            steps:
                draft:
                    label: Example
                    actions:
                        - free_agent_workflow.action.example
                    validations:
                        - free_agent_workflow.validation.example
                        - free_agent_workflow.validation.example
                    possible_next_steps:
                        - removed
```
### Actions & Validations
You need also to set up your **Actions** and **Validations** services.

Usage
-----
```php
<?php
$manager = $this->getContainer()->get('free_agent_workflow.workflow.manager');
$manager->setModel($model);
if ($manager->canReachStep('draft')) {
    $manager->reachStep('draft', 'This is my draft', time());
    $model = $manager->getModel();
} else {
    $errors = $manager->getValidationErrors('draft');
}
```

TODOs
-----

* Better code for Step
* Better tests
