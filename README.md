LexikWorkflowBundle
===================

[![Build Status](https://secure.travis-ci.org/lexik/LexikWorkflowBundle.png)](http://travis-ci.org/lexik/LexikWorkflowBundle)

This Symfony2 bundle allow to define and manage some simple workflow and use event dispatcher for actions and validations.

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

Next, be sure to enable these bundles in your `app/AppKernel.php` file:

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

How does it work?
=================

First of all, what's a workflow? According to wikipedia definition "a workflow consists of a sequence of connected steps". You can see below the workflow terms used by the bundle:

* to define your workflow you will have to discribe some processes ;
* a process is compound of steps, and you advance through the process step by step ;
* a step contains some validations and actions, validations are executed when you try to reach the step, if those validations are successful the step has been reached and actions are executed.

The workflow work on a "model" object, a model is a class that implements `Lexik\Bundle\WorkflowBundle\Model\ModelInterface`. Each times a model try to reach a step we store a row in the database to keep the steps history.

Workflow definition
-------------------

Let's we need to define a simple workflow to create and publish a post. First we have to create a draft, then an admin must validate this draft and after that it can be published.

Once the post is published any user can unpublish it, and if the post is not published an admin can delete it. And let's say that if the validation to reach the published step fail we will go back to the draft step.

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
                        validate: { type: step, target: validated_by_admin } # you can omit "type: step" as "step" is the default value of the "type" node. You can also use "type: process" (soon).

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

The workflow handle some "model" objects. A "model" object is basically an instance of `Lexik\Bundle\WorkflowBundle\Model\ModelInterface`. This interface provide 2 methods:

* `getWorkflowIdentifier()` returns an unique identifier used to store model state in the database.
* `getWorkflowData()` returns an array of data to store with a model state.

Here's an example of a `PostModel` class we could use in the `post_publication` process:

```php
<?php

namespace Project\Bundle\SuperBundle\Workflow\Model;

use Lexik\Bundle\WorkflowBundle\Model\ModelInterface;
use Project\Bundle\SuperBundle\Entity\Post;

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
     * Returns a unique identifier.
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
}
```

Step validations
----------------

As you just read on the bundle introduction, we use a lot event dispatcher for actions and validations. To validate a step can be reached, you just need to listen the `<process_name>.<step_name>.access_validation` event.

You will get a `Lexik\Bundle\WorkflowBundle\Event\StepAccessValidationEvent` object on which you can get the step, the model and an object that manage step violations. You can add some violation to avoid access to the step.

In case of the step is not reached due to validation error you can listen the `<process_name>.<step_name>.validation_fail` event.

Let's see a simple example, here I listen events for the step `published` from the `post_publication` process.

```php
<?php

namespace Project\Bundle\SuperBundle\Workflow\Listener;

use Lexik\Bundle\WorkflowBundle\Event\StepEvent;
use Lexik\Bundle\WorkflowBundle\Event\StepAccessValidationEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostPublicationProcessSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post_publication.published.access_validation' => array(
                'handleAccessValidationPublished',
            ),
            'post_publication.published.validation_fail' => array(
                'handleValidationFail',
            ),
        );
    }

    public function handleAccessValidationPublished(StepAccessValidationEvent $event)
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

Step model status
-----------------

You can easily update the status of your model through `model_status` option. It's a shortcut action that call a method of your model with a constant as argument and flush it.

```yaml
model_status: [ setStatus, Project\Bundle\SuperBundle\Entity\Post::STATUS_PUBLISHED ]
```

Step actions
------------

If you need to execute some logic once a step is successfuly reached, you just need to listen the `<process_name>.<step_name>.reached` event.

You will get a `Lexik\Bundle\WorkflowBundle\Event\StepEvent` object on wich you can get the step, the model and the last model state.

Let's see a simple example, here I listen events for the step `published` from the `post_publication` process.

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
                'handleSuccessfulyPublished',
            ),
        );
    }

    public function handleSuccessfulyPublished(StepEvent $event)
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

Step roles
----------

You can define the roles the current user must have to be able to reach a step. Roles are checked just before step validations.

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

// reach a next state
$modelState = $processHandler->reachNextState($model, 'validate'); // here 'validate' is the key defined in the draft_created next states.

// $model->getStatus() === Project\Bundle\SuperBundle\Entity\Post::STATUS_VALIDATED

if ( ! $modelState->getSuccessful() ) {
    var_dump($modelState->getErrors());
}
```

Note that the `start()` and `reachNextState()` methods return an instance of `Lexik\Bundle\WorkflowBundle\Entity\ModelState`.
This entity represent a state for a given model and process.
