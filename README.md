LexikWorkflowBundle
===================

[![Build Status](https://secure.travis-ci.org/lexik/LexikWorkflowBundle.png)](http://travis-ci.org/lexik/LexikWorkflowBundle)
[![Latest Stable Version](https://poser.pugx.org/lexik/workflow-bundle/v/stable)](https://packagist.org/packages/lexik/workflow-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7209d542-4448-4844-838c-4e53151ec769/mini.png)](https://insight.sensiolabs.com/projects/7209d542-4448-4844-838c-4e53151ec769)

This Symfony2 bundle allows to define and manage simple workflows using the event dispatcher for actions and validations.

This bundle was originally a fork of [FreeAgentWorkflowBundle](https://github.com/jeremyFreeAgent/FreeAgentWorkflowBundle). The implementation differs in the way that we use event dispatcher and we store steps history for each model object.

Installation
------------

Installation with composer:

``` json
    ...
    "require": {
        ...
        "lexik/workflow-bundle": "dev-master",
        ...
    },
    ...
```

Next, be sure to enable the bundle in your `app/AppKernel.php` file:

``` php
public function registerBundles()
{
    return array(
        // ...
        new Lexik\Bundle\WorkflowBundle\LexikWorkflowBundle(),
        // ...
    );
}
```

How it works
============

First of all, what's a workflow? According to wikipedia definition, "a workflow consists of a sequence of connected steps". You can see below the workflow terms used by the bundle:

* to define your workflow you will have to describe some processes ;
* a process is defined by a series of steps, and you advance through the process step by step ;
* a step contains validations and actions, validations are executed when you try to reach the step, if those validations are successful the step has been reached and actions are executed.

The workflow works on a "model" object, a model is a class that implements `Lexik\Bundle\WorkflowBundle\Model\ModelInterface`. Each time a model tries to reach a step, we log it in the database to keep the steps history.

Workflow example
----------------

Let's define a simple workflow around a post from its creation to its publication:

* first, we have to create a draft, then an admin must validate this draft before it can be published ;
* once a post is published, any user can unpublish it ;
* if a post is not published, an admin can delete it ;
* if the publication step fails, we go back to the draft step.

```yaml
# app/config/config.yml
lexik_workflow:
    processes:
        post_publication:
            start: draft_created
            end:   [ deleted ]
            steps:
                draft_created:
                    label: "Draft created"
                    roles: [ ROLE_USER ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_DRAFT ]
                    next_states:
                        validate: { type: step, target: validated_by_admin } # you can omit "type: step" as "step" is the default value of the "type" node. Soon, you'll be able to use "type: process".

                validated_by_admin:
                    label: "Post validated"
                    roles: [ ROLE_ADMIN ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_VALIDATED ]
                    next_states:
                        publish: { target: published }

                published:
                    label: "Post published"
                    roles: [ ROLE_USER ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_PUBLISHED ]
                    on_invalid: draft_created # will try to reach the "draft_created" step in case validations to reach "published" fail.
                    next_states:
                        unpublish: { target: unpublished }

                unpublished:
                    label: "Post unpublished"
                    roles: [ ROLE_USER ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_UNPUBLISHED ]
                    next_states:
                        delete:  { target: deleted }
                        publish: { target: published }

                deleted:
                    label: "Post deleted"
                    roles: [ ROLE_ADMIN ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_DELETED ]
                    next_states: ~
```

Model object
------------

The workflow handles "model" objects. A "model" object is basically an instance of `Lexik\Bundle\WorkflowBundle\Model\ModelInterface`. This interface has 3 methods to implement:

* `getWorkflowIdentifier()` returns an unique identifier used to store a model state in the database ;
* `getWorkflowData()` returns an array of data to store with the model state ;
* `getWorkflowObject()` returns the final object, it will be passed to the security context by the default ProcessHandler.

Here's an example of a `PostModel` class we could use in the `post_publication` process:

```php
<?php

namespace Project\Bundle\SuperBundle\Workflow\Model;

use Lexik\Bundle\WorkflowBundle\Model\ModelInterface;
use Project\Bundle\SuperBundle\Entity\Post;

/**
 * This class is used to wrap a Post entity.
 * It's not required to do it like that, we could also implement ModelInterface in the Post entity .
 */
class PostModel implements ModelInterface
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function setStatus($status)
    {
        $this->post->setStatus($status);
    }

    public function getStatus()
    {
        return $this->post->getStatus();
    }

    /**
     * Returns an unique identifier.
     *
     * @return mixed
     */
    public function getWorkflowIdentifier()
    {
        return md5(get_class($this->post).'-'.$this->post->getId());
    }

    /**
     * Returns data to store in the ModelState.
     *
     * @return array
     */
    public function getWorkflowData()
    {
        return array(
            'post_id' => $this->post->getId(),
            'content' => $this->post->getContent(),
            // ...
        );
    }

    /**
     * Returns the final object.
     * If your entity implements ModelInterface itself just return $this.
     *
     * @return object
     */
    public function getWorkflowObject()
    {
        return $this->post;
    }
}
```

Step validations
----------------

As you just read on the bundle introduction, we use the event dispatcher for actions and validations. To validate that a step can be reached, you just need to listen to the `<process_name>.<step_name>.validate` event.

The listener will receive an instance of `Lexik\Bundle\WorkflowBundle\Event\ValidateStepEvent`, which will allow you to get the step, the model and an object that manages the step violations. You can add violations to block the access to the step.

In the case the step is not reached due to a validation error, a `<process_name>.<step_name>.validation_fail` event is dispatched.

Let's see a simple example, here I listen to the events `*.validate` and `*.validation_fail` for the step `published` from the `post_publication` process.

```php
<?php

namespace Project\Bundle\SuperBundle\Workflow\Listener;

use Lexik\Bundle\WorkflowBundle\Event\StepEvent;
use Lexik\Bundle\WorkflowBundle\Event\ValidateStepEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostPublicationProcessSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post_publication.published.validate' => array(
                'handleAccessValidationPublished',
            ),
            'post_publication.published.validation_fail' => array(
                'handleValidationFail',
            ),
        );
    }

    public function handleAccessValidationPublished(ValidateStepEvent $event)
    {
        if ( ! $event->getModel()->canBePublished()) {
            $event->addViolation('error message');
        }
    }

    public function handleValidationFail(StepEvent $event)
    {
        // ...
    }
}
```

```xml
<service id="project.workflow.listener.post_publication" class="Project\Bundle\SuperBundle\Workflow\Listener\PostPublicationProcessSubscriber">
    <tag name="kernel.event_subscriber" />
</service>
```

Step actions
------------

If you need to execute some logic once a step is successfully reached, you can listen to the `<process_name>.<step_name>.reached` event.

The listener will receive a `Lexik\Bundle\WorkflowBundle\Event\StepEvent` object with which you can get the step, the model and the last model state.

Let's see a simple example, here I listen to `*.reached` event for the step `published` from the `post_publication` process.

```php
<?php

namespace Project\Bundle\SuperBundle\Workflow\Listener;

use Lexik\Bundle\WorkflowBundle\Event\StepEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostPublicationProcessSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post_publication.published.reached' => array(
                'handleSuccessfullyPublished',
            ),
        );
    }

    public function handleSuccessfullyPublished(StepEvent $event)
    {
        // ...
    }
}
```

```xml
<service id="project.workflow.listener.post_publication" class="Project\Bundle\SuperBundle\Workflow\Listener\PostPublicationProcessSubscriber">
    <tag name="kernel.event_subscriber" />
</service>
```

Step Pre-validations
--------------------

In addition to step validations, you can also process some pre-validations.
A pre-validation will be executed just before step validations and only for the current step.

E.g.: let's say we have a post which is currently on step `published` and I want to reach the step `unpublished`.

```yaml
# app/config/config.yml
lexik_workflow:
    processes:
        post_publication:
            start: draft_created
            end:   [ deleted ]
            steps:
                # ...

                published:
                    label: "Post published"
                    roles: [ ROLE_USER ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_PUBLISHED ]
                    on_invalid: draft_created # will try to reach the "draft_created" step in case validations to reach "published" fail.
                    next_states:
                        unpublish: { target: unpublished }

                unpublished:
                    label: "Post unpublished"
                    roles: [ ROLE_USER ]
                    model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_UNPUBLISHED ]
                    next_states:
                        delete:  { target: deleted }
                        publish: { target: published }

                # ...
```

When you will try to reach the `unpublished` step, the process handler will trigger a pre-validation event named:

`post_publication.published.unpublish.pre_validation`

So by listening this event you will be able to do some validations before the process handler executes default validations defined on the `unpublished` step.
And these pre-validations are only executed when you try to reach `unpublished` from `published`.

Pre-validation events patterns:

* To process some pre-validations: `<process_name>.<current_step_name>.<next_state_name>.pre_validation`.
* To process some code in case pre-validations fail: `<process_name>.<current_step_name>.<next_state_name>.pre_validation_fail`.

Here a simple example for a pre-validation listener:

```php
namespace Project\Bundle\SuperBundle\Workflow\Listener;

use Lexik\Bundle\WorkflowBundle\Event\StepEvent;
use Lexik\Bundle\WorkflowBundle\Event\ValidateStepEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostPublicationProcessSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post_publication.published.unpublish.pre_validation' => array(
                'preValidate',
            ),
            'post_publication.published.unpublish.pre_validation_fail' => array(
                'preValidationFail',
            ),
        );
    }

    public function preValidate(ValidateStepEvent $event)
    {
        // do your checks
    }

    public function preValidationFail(StepEvent $event)
    {
        // process code in case the pre validation fail
    }
}
```

Conditional next step (OR)
--------------------------

To define a conditional next state, you have to define the `target` key as usual, plus the `type` key to notify the process handler that this next state is conditional.

Here an example of conditional next state:

```yaml
lexik_workflow:
    processes:
        my_process:
            steps:
                my_step_xxx:
                    label: "Step xxx"
                    next_states:
                        go_to_next_step:
                            type: step_or
                            target:
                                my_step_A: "service_id:method_name"
                                my_step_B: "service_id:other_method_name"
                                my_step_C: ~  # default choice
```

Let's say we have a model state currently on the step named `my_step_xxx`.
If we try to reach the next state named `go_to_next_step` by calling `$processHandler->reachNextState($model, 'go_to_next_step')`, the workflow will call each method defined for each target.
The first method that returns true will make the workflow proceed to the related step.

So, if `service_id:method_name` returns `true`, the next step will be `my_step_A`.
If `service_id:method_name` returns `false` and then `service_id:other_method_name` returns `true`, the next step will be `my_step_B`.
If both `service_id:method_name` and `service_id:other_method_name` return `false`, the next step will be `my_step_C`.

Model status update
-------------------

You can easily assign a status to your model using the `model_status` option. The first argument is the method that will be called on the model when the step is reached. The second argument is the value that will be passed to this method. This allows you to automatically update the status at each step of the process.

```yaml
steps:
    published:
        ...
        model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_PUBLISHED ]
        ...
```

Step user roles
---------------

You can define the roles the current user must have to be able to reach a step. Roles are checked just before step validations.

```yaml
steps:
    published:
        ...
        roles: [ ROLE_ADMIN ]
        ...
```
An event `*.bad_credentials` is dispatched when user has not the roles.

Set modelStates on your ModelInterface
--------------------------------------

If you want to retrieve all modelStates created for your ModelInterface object, you need to implement the ModelStateInterface:

```php
<?php

class FakeModel implements ModelInterface, ModelStateInterface
{
    //...

    protected $states = array();

    public function addState(ModelState $modelState)
    {
        $this->states[] = $modelState;
    }

    public function getStates()
    {
        return $this->states;
    }
```

This will allow you to call the method `getStates($objects, $processes = array(), $onlySuccess = false)` defined in the `lexik_workflow.model_storage` service.

```php
<?php

    // get your object

    // Set states on your object
    $this->get('lexik_workflow.model_storage')->setStates($post);

    // Get all your objects and set your process and only state successful
    $this->get('lexik_workflow.model_storage')->setStates($posts, ['process'], true);
```


Usage
-----

Here a simple example of how to use the workflow:

```php
<?php

// create a model object (see the PostModel class defined previously in the Model object section)
$model = new PostModel($myPost);

// get the process handler
$processHandler = $container->get('lexik_workflow.handler.post_publication');

// start the process
$modelState = $processHandler->start($model);

// $model->getStatus() === Project\Bundle\SuperBundle\Entity\Post::STATUS_DRAFT

// reach the next state
$modelState = $processHandler->reachNextState($model, 'validate'); // here 'validate' is the key defined in the draft_created next states.

// $model->getStatus() === Project\Bundle\SuperBundle\Entity\Post::STATUS_VALIDATED

if ( ! $modelState->getSuccessful() ) {
    var_dump($modelState->getErrors());
}
```

Note that the `start()` and `reachNextState()` methods return an instance of `Lexik\Bundle\WorkflowBundle\Entity\ModelState`. This entity represents a state for a given model and process.
