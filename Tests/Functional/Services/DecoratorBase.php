<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class DecoratorBase extends WebTestCase {
  protected $dm;
  protected $container;

  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    require_once __DIR__.'/../../AppKernel.php';
    $kernel = new \AppKernel('test', true);
    $kernel->boot();
    $this->container = $kernel->getContainer();
    $this->dm = $this->container->get('test.doctrine.odm.mongodb.document_manager');

    $repository_services = array(
      'repository.test_document',
      'repository.test_document_constructor_params',
      'repository.test_document_abort_calls',
      'repository.test_document_abort_save',
      'repository.test_document_abort_delete',
    );
    foreach($repository_services as $repository_service) {
      $this->deleteAllDocuments($repository_service);
    }
  }

  protected function deleteAllDocuments($repository_service) {
    $repositoryDecorator = $this->container->get($repository_service);
    $documentDecorators = $repositoryDecorator->findAll();
    foreach($documentDecorators as $documentDecorator) {
      $documentDecorator->delete(false);
    }
    $repositoryDecorator->getDocumentManager()->flush();
  }
}
