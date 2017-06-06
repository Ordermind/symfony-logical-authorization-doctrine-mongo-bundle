<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecorator;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocument;

class DoctrineODMDocumentDecoratorTest extends DecoratorBase {

  public function testClass() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $this->assertTrue($documentDecorator instanceof DocumentDecorator);
  }

  public function testGetDocument() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $document = $documentDecorator->getDocument();
    $this->assertTrue($document instanceof TestDocument);
  }

  public function testGetObjectManager() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $dm = $documentDecorator->getDocumentManager();
    $this->assertTrue($dm instanceof DocumentManager);
  }

  public function testSetObjectManager() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setDocumentManager($this->container->get('doctrine.odm.mongodb.document_manager'));
    $dm = $documentDecorator->getDocumentManager();
    $this->assertTrue($dm instanceof DocumentManager);
  }

  public function testSave() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $documentDecorator->save();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
  }

  public function testSaveNoFlush() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $documentDecorator->save(false);
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(0, count($result));
    $dm = $documentDecorator->getDocumentManager();
    $dm->flush();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
  }

  public function testSaveAbort() {
    $repositoryDecorator = $this->container->get('repository.test_document_abort_save');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $response = $documentDecorator->save();
    $this->assertFalse($response);
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(0, count($result));
  }

  public function testDelete() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $documentDecorator->save();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
    $documentDecorator->delete();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(0, count($result));
  }

  public function testDeleteNoFlush() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $documentDecorator->save();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
    $documentDecorator->delete(false);
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
    $dm = $documentDecorator->getDocumentManager();
    $dm->flush();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(0, count($result));
  }

  public function testDeleteAbort() {
    $repositoryDecorator = $this->container->get('repository.test_document_abort_delete');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $documentDecorator->save();
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
    $response = $documentDecorator->delete();
    $this->assertFalse($response);
    $result = $repositoryDecorator->findBy(array('field1' => 'test'));
    $this->assertEquals(1, count($result));
  }

  public function testCall() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $documentDecorator->setField1('test');
    $this->assertEquals('test', $documentDecorator->getDocument()->getField1());
  }

  public function testCallAbort() {
    $repositoryDecorator = $this->container->get('repository.test_document_abort_calls');
    $documentDecorator = $repositoryDecorator->create();
    $document = $documentDecorator->getDocument();
    $response = $documentDecorator->setField1('test');
    $this->assertNull($response);
    $this->assertEmpty($document->getField1());
    $document->setField1('test');
    $this->assertEquals('test', $document->getField1());
    $this->assertNull($documentDecorator->getField1());
  }

  public function testIsNew() {
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $this->assertTrue($documentDecorator->isNew());
    $documentDecorator->save();
    $this->assertFalse($documentDecorator->isNew());
    $loadedDocumentDecorator = $repositoryDecorator->find($documentDecorator->getId());
    $this->assertFalse($loadedDocumentDecorator->isNew());
  }

  public function testGetAvailableActions() {
    $laModel = $this->container->get('ordermind_logical_authorization.service.logical_authorization_model');
    $repositoryDecorator = $this->container->get('repository.test_document');
    $documentDecorator = $repositoryDecorator->create();
    $document = $documentDecorator->getDocument();
    $available_actions_decorator = $documentDecorator->getAvailableActions('anon.');
    $available_actions_class = $laModel->getAvailableActions(get_class($document), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $this->assertSame($available_actions_decorator, $available_actions_class);
  }
}
