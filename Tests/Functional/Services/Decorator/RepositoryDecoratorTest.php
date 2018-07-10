<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services\Decorator;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecorator;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecorator;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Repository\Misc\TestDocumentRepository;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocument;

class RepositoryDecoratorTest extends DecoratorBase
{
    public function testClass()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $this->assertTrue($repositoryDecorator instanceof RepositoryDecorator);
    }

    public function testGetClassName()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $class = $repositoryDecorator->getClassName();
        $this->assertEquals('Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocument', $class);
    }

    public function testSetObjectManager()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $repositoryDecorator->setDocumentManager(static::$container->get('test.doctrine.odm.mongodb.document_manager'));
        $dm = $repositoryDecorator->getDocumentManager();
        $this->assertTrue($dm instanceof DocumentManager);
    }

    public function testGetObjectManager()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $em = $repositoryDecorator->getDocumentManager();
        $this->assertTrue($em instanceof DocumentManager);
    }

    public function testGetRepository()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $repository = $repositoryDecorator->getRepository();
        $this->assertTrue($repository instanceof TestDocumentRepository);
    }

    public function testFind()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $documentDecorator = $repositoryDecorator->find($documentDecorator->getId());
        $this->assertEquals('test', $documentDecorator->getField1());
    }

    public function testFindEvent()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $documentDecorator = $repositoryDecorator->find($documentDecorator->getId());
        $this->assertEquals('hej', $documentDecorator->getField2());
    }

    public function testFindAll()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->save();
        $result = $repositoryDecorator->findAll();
        $this->assertEquals(1, count($result));
    }

    public function testFindAllEvent()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->save();
        $result = $repositoryDecorator->findAll();
        $documentDecorator = reset($result);
        $this->assertEquals('hej', $documentDecorator->getField2());
    }

    public function testFindBy()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $result = $repositoryDecorator->findBy(array('field1' => 'test'));
        $this->assertEquals(1, count($result));
    }

    public function testFindByEvent()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $result = $repositoryDecorator->findBy(array('field1' => 'test'));
        $documentDecorator = reset($result);
        $this->assertEquals('hej', $documentDecorator->getField2());
    }

    public function testFindOneBy()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $documentDecorator = $repositoryDecorator->findOneBy(array('field1' => 'test'));
        $this->assertEquals('test', $documentDecorator->getField1());
    }

    public function testFindOneByEvent()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $documentDecorator = $repositoryDecorator->findOneBy(array('field1' => 'test'));
        $this->assertEquals('hej', $documentDecorator->getField2());
    }

    public function testMatching()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->eq('field1', 'test'));
        $result = $repositoryDecorator->matching($criteria);
        $this->assertEquals(1, $result->count());
    }

    public function testMatchingEvent()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->eq('field1', 'test'));
        $result = $repositoryDecorator->matching($criteria);
        foreach ($result as $document) {
            $this->assertEquals('hej', $document->getField2());
        }
    }

    public function testCall()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $result = $repositoryDecorator->findByField1('test');
        $this->assertEquals(1, count($result));
    }

    public function testCallEvent()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $documentDecorator = $repositoryDecorator->findOneByField1('test');
        $this->assertEquals('hej', $documentDecorator->getField2());
    }

    public function testCreate()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documentDecorator = $repositoryDecorator->create();
        $documentDecorator->setField1('test');
        $documentDecorator->save();
        $result = $repositoryDecorator->findBy(array('field1' => 'test'));
        $this->assertEquals(1, count($result));
    }

    public function testCreateWithParams()
    {
        $repositoryDecorator = static::$container->get('repository.test_document_constructor_params');
        $documentDecorator = $repositoryDecorator->create('test1', 'test2', 'test3');
        $documentDecorator->save();
        $result = $repositoryDecorator->findBy(array('field1' => 'test1', 'field2' => 'test2', 'field3' => 'test3'));
        $this->assertEquals(1, count($result));
        $this->assertSame('test1', $documentDecorator->getField1());
        $this->assertSame('hej', $documentDecorator->getField2());
        $this->assertSame('test3', $documentDecorator->getField3());
    }

    public function testCreateAbort()
    {
        $repositoryDecorator = static::$container->get('repository.test_document_abort_create');
        $documentDecorator = $repositoryDecorator->create();
        $this->assertNull($documentDecorator);
    }

    public function testWrapDocumentsSingleDocument()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $document = new TestDocument();
        $documentDecorators = $repositoryDecorator->wrapDocuments($document);
        $this->assertEquals(1, count($documentDecorators));
        foreach ($documentDecorators as $documentDecorator) {
            $this->assertTrue($documentDecorator instanceof DocumentDecorator);
        }
    }

    public function testWrapDocuments()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $documents = array(
      new TestDocument(),
      new TestDocument(),
    );
        $documentDecorators = $repositoryDecorator->wrapDocuments($documents);
        $this->assertEquals(2, count($documentDecorators));
        foreach ($documentDecorators as $documentDecorator) {
            $this->assertTrue($documentDecorator instanceof DocumentDecorator);
        }
    }

    public function testWrapDocumentWrongDocumentType()
    {
        $repositoryDecorator = static::$container->get('repository.test_document_constructor_params');
        $document = new TestDocument();
        $this->assertNull($repositoryDecorator->wrapDocument(null));
        $this->assertSame($document, $repositoryDecorator->wrapDocument($document));
    }

    public function testWrapDocument()
    {
        $repositoryDecorator = static::$container->get('repository.test_document');
        $document = new TestDocument();
        $documentDecorator = $repositoryDecorator->wrapDocument($document);
        $this->assertTrue($documentDecorator instanceof DocumentDecorator);
    }
}
