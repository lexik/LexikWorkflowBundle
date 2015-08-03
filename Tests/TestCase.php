<?php

namespace Lexik\Bundle\WorkflowBundle\Tests;

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
                next_states:
                    validate:
                        target: step_validate_doc
                    remove:
                        target: step_remove_doc
                    validate_or_remove:
                        type: step_or
                        target:
                            step_validate_doc: "next_state_condition:isClean"
                            step_remove_doc:   ~
            step_validate_doc:
                roles: [ ROLE_ADMIN, ROLE_USER ]
            step_remove_doc:
                roles: [ ROLE_ADMIN ]
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
        //$schemaTool->dropSchema($em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * EntityManager object together with annotation mapping driver and
     * pdo_sqlite database in memory
     *
     * @return EntityManager
     */
    protected function getSqliteEntityManager()
    {
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        // xml driver
        $xmlDriver = new \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver(array(
            __DIR__.'/../Resources/config/doctrine' => 'Lexik\Bundle\WorkflowBundle\Entity',
        ));

        // configuration mock
        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array(
            __DIR__.'/../Entity',
        ), false, null, null, false);
        $config->setMetadataDriverImpl($xmlDriver);
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxy');
        $config->setAutoGenerateProxyClasses(true);
        $config->setClassMetadataFactoryName('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $config->setDefaultRepositoryClassName('Doctrine\ORM\EntityRepository');

        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $em = EntityManager::create($conn, $config);

        return $em;
    }

    /**
     * Returns a mock instance of a AuthorizationChecker.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    public function getMockAuthorizationChecker()
    {
        $checker = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationChecker')
            ->disableOriginalConstructor()
            ->getMock();

        return $checker;
    }
}
