Overview
========

This Symfony2 bundle allow to define and manage some simple workflow.


Installation
============

Update your `deps` and `deps.lock` files:

```
// deps
...
[FreeAgentWorkflowBundle]
    git=https://github.com/lexik/FreeAgentWorkflowBundle.git
    target=/bundles/Lexik/Bundle/WorkflowBundle

// deps.lock
...
FreeAgentWorkflowBundle <commit>
```

Register the namespaces with the autoloader:

```php
<?php
// app/autoload.php
$loader->registerNamespaces(array(
    // ...
    'Lexik' => __DIR__.'/../vendor/bundles',
    // ...
));
```

Register the bundle with your kernel:

```php
<?php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Lexik\Bundle\WorkflowBundle\FreeAgentWorkflowBundle(),
    // ...
);
```

How does it work ?
==================

To define your workflow you will have to discribe some processes, a process consists of a sequence of connected steps.
A step contains some validations, actions and roles. A step can't be reached if the current user in session does not have roles defined on this step.
Validations are executed when you try to reach the step, if those validations are successful we consider the step has been reached and we run all actions defined on the reached step.
If validations fail, you will stay on the current step except if the "onInvalid" step if defined, in this case you won't stay on the current step but we will try to reach the "onInvalid" step.
The workflow work on a "model" object, a model is a class that implements `FreeAgent\WorkflowBundle\Model\ModelInterface`.
Each times a model try to reach a step we store a row in the database to keep the steps history.


Workflow definition
-------------------

Let's we need to define a simple workflow to create and publish a post.
First we have to create a draft, then an admin must validate this draft and after that it can be published.
Once the post is published any user can unpublish it, and if the post is not published an admin can delete it.
And let's say that if the validation to reach the published step fail we will go back to the draft step (this is just to use the "onInvalid" option).

```yaml
# app/config/config.yml
free_agent_workflow:
    processes:
        post_publication:
            start: draft_created
            end:   [ deleted ]
            steps:
                draft_created:
                    label: "Draft created"
                    roles: [ ROLE_USER ]
                    validations:
                        - my.validaion.service.id:methodName
                        - ...
                    actions:
                        - my.action.service.id:methodName
                        - ...
                    next_states:
                        validate: { type: step, target: validted_by_admin } # you can omit "type: step" as "step" is the default value of the "type" node. You can also use "type: process" (soon).
                
                validted_by_admin:
                    label: "Post validated"
                    roles: [ ROLE_ADMIN ]
                    validations:
                        - my.validaion.service.id:methodName
                        - ...
                    actions:
                        - my.action.service.id:methodName
                        - ...
                    next_states:
                        publish: { target: published }
                
                published:
                    label: "Post published"
                    roles: [ ROLE_USER ]
                    validations:
                        - my.validaion.service.id:methodName
                        - ...
                    actions:
                        - my.action.service.id:methodName
                        - ...
                    onInvalid: draft_created # will try to reach the "draft_created" step in case validations to reach "published" fail.
                    next_states:
                        unpublish: { target: unpublished }
                
                unpublished:
                    label: "Post unpublished"
                    roles: [ ROLE_USER ]
                    validations:
                        - my.validaion.service.id:methodName
                        - ...
                    actions:
                        - my.action.service.id:methodName
                        - ...
                    next_states:
                        delete:  { target: deleted }
                        publish: { target: published }
                
                deleted:
                    label: "Post deleted"
                    roles: [ ROLE_ADMIN ]
                    validations:
                        - my.validaion.service.id:methodName
                        - ...
                    actions:
                        - my.action.service.id:methodName
                        - ...
                    next_states: ~
```

Model object
------------

The workflow handle some "model" objects. A "model" object is basically an instance of `FreeAgent\WorkflowBundle\Model\ModelInterface`.
This interface provide 2 methods:

* getWorkflowIdentifier(): returns a unique identifier used to store model's state in the database.
* getWorkflowData(): returns an array of data to store with a model state.

Here an example of a `PostModel` class we could use in the `post_publication` process.

```php
<?php
namespace Project\Bundle\SuperBundle\Workflow\Model;

use FreeAgent\WorkflowBundle\Model\ModelInterface;
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

To validate a step, just create your own class with methods to check the model object and define this class as a servive.
Each method used for validation will receive the model object the workflow is currently working on.

```php
<?php
namespace Project\Bundle\SuperBundle\Workflow\Validators

use FreeAgent\WorkflowBundle\Model\ModelInterface;

class PostPublicationValidator
{
    public function checkDraft(ModelInterface $model)
    {
        // check wahtever you need.
        // if something goes wrong and $model is not valid just throw a ValidationException.
        
        if ( ! $model->getPost()->getContent() ) {
            throw new FreeAgent\WorkflowBundle\Exception\ValidationException('error message');
        }
    }
}
```

Step actions
------------

If you need to execute some logic once a step is reached, you can add some actions on the step.
To do this you just need to create a class and define it as a service.
Each method called will receive the model object the workflow is currently working on and the reached step.

```php
<?php
namespace Project\Bundle\SuperBundle\Workflow\Validators

use FreeAgent\WorkflowBundle\Model\ModelInterface;
use FreeAgent\WorkflowBundle\Flow\Step;

class PostPublicationActions
{
    public function setDraftStatus(ModelInterface $model, Step $step)
    {
        $model->getPost()->setStatus('draft');
        // ...
    }
}
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
$processHandler = $container->get('free_agent_workflow.handler.post_publication');

// start the process
$modelState = $processHandler->start($model);

// reach a next state
$modelState = $processHandler->reachNextState($model, 'validate'); // here 'validate' is the key defined in the draft_created next states.

if ( ! $modelState->getSuccessful() ) {
    var_dump($modelState->getErrors());
}
```

Note that the `start()` and `reachNextState()` methods return an instance of `FreeAgent\WorkflowBundle\Entity\ModelState`.
This entity represent a state for a given model and process.
