<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services\Decorator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class DecoratorBase extends WebTestCase
{
    protected $dm;

    /**
     * This method is run before each public test method
     */
    protected function setUp()
    {
        require_once __DIR__.'/../../../AppKernel.php';

        $kernel = new \AppKernel('test', true);
        $kernel->boot();
        static::$container = $kernel->getContainer();
        $this->dm = static::$container->get('test.doctrine.odm.mongodb.document_manager');

        $repository_services = array(
      'repository.test_document',
      'repository.test_document_constructor_params',
      'repository.test_document_abort_calls',
      'repository.test_document_abort_save',
      'repository.test_document_abort_delete',
    );
        foreach ($repository_services as $repository_service) {
            $this->deleteAllDocuments($repository_service);
        }
    }

    protected function deleteAllDocuments($repository_service)
    {
        $repositoryDecorator = static::$container->get($repository_service);
        $documentDecorators = $repositoryDecorator->findAll();
        foreach ($documentDecorators as $documentDecorator) {
            $documentDecorator->delete(false);
        }
        $repositoryDecorator->getDocumentManager()->flush();
    }
}
