<?php

namespace FreeAgent\WorkflowBundle\Tests;

use Symfony\Component\Yaml\Parser;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    protected function getConfig()
    {
        $yaml = <<<EOF
processes:
    document_proccess:
        start: step_create_doc
        end:   [ step_validate_doc, step_remove_doc ]
        steps:
            step_create_doc:
                roles: [ ROLE_ADMIN, ROLE_USER ]
                validations:
                    - workflow.validator.check_wahtever_you_need
                next_steps:
                    validate:
                        target: step_validate_doc
                    remove:
                        target: step_remove_doc
                actions:
                    - workflow.action.send_email
            step_validate_doc:
                roles: [ ROLE_ADMIN, ROLE_USER ]
                validations:
                    - workflow.validator.check_content_is_not_empty
            step_remove_doc:
                roles: [ ROLE_ADMIN ]
                validations:
                    - workflow.validator.check_doc_not_published
EOF;
        $parser = new Parser();

        return  $parser->parse($yaml);
    }

    /**
     * @return array
     */
    protected function getSimpleConfig()
    {
        $yaml = <<<EOF
processes:
    document_proccess:
        start:
        steps: []
EOF;
        $parser = new Parser();

        return  $parser->parse($yaml);
    }
}
