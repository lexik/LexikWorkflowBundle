<?php

namespace FreeAgent\WorkflowBundle\Tests;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\ORM\EntityManager;

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
#                validations:
#                    - workflow.validator.check_wahtever_you_need
                next_steps:
                    validate:
                        target: step_validate_doc
                    remove:
                        target: step_remove_doc
#                actions:
#                    - workflow.action.send_email
            step_validate_doc:
                roles: [ ROLE_ADMIN, ROLE_USER ]
#                validations:
#                    - workflow.validator.check_content_is_not_empty
            step_remove_doc:
                roles: [ ROLE_ADMIN ]
#                validations:
#                    - workflow.validator.check_doc_not_published
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

    /**
     * Create the database schema.
     *
     * @param EntityManager $em
     */
    protected function createSchema(EntityManager $em)
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

//    /**
//     * Load test fixtures.
//     *
//     * @param EntityManager $om
//     */
//    protected function loadFixtures(EntityManager $em)
//    {
//        $purger = new ORMPurger();
//        $executor = new ORMExecutor($em, $purger);
//
//        $executor->execute(array(new CurrencyData()), false);
//    }

    /**
     * EntityManager mock object together with annotation mapping driver and
     * pdo_sqlite database in memory
     *
     * @return EntityManager
     */
    protected function getMockSqliteEntityManager()
    {
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        // xml driver
        $prefixes = array(
            'FreeAgent\WorkflowBundle\Entity' => __DIR__.'/../Resources/config/doctrine',
        );
        $xmlDriver = new \Symfony\Bridge\Doctrine\Mapping\Driver\XmlDriver(array_values($prefixes));
        $xmlDriver->setNamespacePrefixes(array_flip($prefixes));

        // configuration mock
        $config = $this->getMock('Doctrine\ORM\Configuration');
        $config->expects($this->any())
            ->method('getMetadataCacheImpl')
            ->will($this->returnValue($cache));
        $config->expects($this->any())
            ->method('getQueryCacheImpl')
            ->will($this->returnValue($cache));
        $config->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(sys_get_temp_dir()));
        $config->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'));
        $config->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true));
        $config->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($xmlDriver));
        $config->expects($this->any())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\ORM\Mapping\ClassMetadataFactory'));

        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $em = EntityManager::create($conn, $config);

        return $em;
    }

    protected function getMockSecurityContext()
    {
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $decisionManager = $this->getMock('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface');

        $context = new SecurityContext($authManager, $decisionManager);
        $context->setToken($token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface'));

        return $context;
    }
}
