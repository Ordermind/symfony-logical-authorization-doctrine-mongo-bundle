<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Selectable;

/**
 * Decorator for repository
 *
 * Wraps a repository and monitors all communication with it. It also provides a few handy methods.
 */
interface RepositoryDecoratorInterface extends ObjectRepository, Selectable
{

  /**
   * Gets the document class name that is associated with this decorator
   *
   * @return string
   */
    public function getClassName();

  /**
   * Overrides the document manager that is used in this decorator
   *
   * @param Doctrine\Common\Persistence\ObjectManager $dm The document manager that is to be used in this decorator
   *
   * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecorator
   */
    public function setDocumentManager(\Doctrine\Common\Persistence\ObjectManager $dm);

  /**
   * Gets the document manager that is used in this decorator
   *
   * @return Doctrine\Common\Persistence\ObjectManager
   */
    public function getDocumentManager();

  /**
   * Gets the repository that is wrapped by this decorator
   *
   * @return Doctrine\Common\Persistence\ObjectRepository
   */
    public function getRepository();

  /**
   * Finds a document by its identifier
   *
   * This method forwards the call to the wrapped repository and fires the event 'ordermind_logical_authorization_doctrine_mongo.event.repository_decorator.single_document_result' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\SingleDocumentResultEvent, allowing tampering with the result before returning it to the caller. If no result is found, NULL is returned.
   *
   * @param mixed   $id          The identifier
   * @param integer $lockMode    (optional) One of the constants in either \Doctrine\DBAL\LockMode::* (for ORM) or \Doctrine\MongoDB\LockMode::* (for ODM) if a specific lock mode should be used during the search. Default value is NULL.
   * @param integer $lockVersion (optional) The lock version. Default value is NULL.
   *
   * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface|NULL
   */
    public function find($id, $lockMode = null, $lockVersion = null);

  /**
   * Finds all documents for this repository decorator
   *
   * This method forwards the call to the wrapped repository and fires the event 'ordermind_logical_authorization_doctrine_mongo.event.repository_decorator.multiple_document_result' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\MultipleDocumentResultEvent, allowing tampering with the result before returning it to the caller.
   *
   * @return array
   */
    public function findAll();

  /**
   * Finds documents for this repository decorator filtered by a set of criteria
   *
   * This method forwards the call to the managed repository and fires the event 'ordermind_logical_authorization_doctrine_mongo.event.repository_decorator.multiple_document_result' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\MultipleDocumentResultEvent, allowing tampering with the result before returning it to the caller.
   *
   * @param array $criteria Query criteria
   * @param array $sort     (optional) Sort array for Cursor::sort(). Default value is NULL.
   * @param array $limit    (optional) Limit for Cursor::limit(). Default value is NULL.
   * @param array $skip     (optional) Skip for Cursor::skip(). Default value is NULL.
   *
   * @return array
   */
    public function findBy(array $criteria, array $sort = null, $limit = null, $skip = null);

  /**
   * Finds a document for this repository decorator filtered by a set of criteria
   *
   * This method forwards the call to the managed repository and fires the event 'ordermind_logical_authorization_doctrine_mongo.event.repository_decorator.single_document_result' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\SingleDocumentResultEvent, allowing tampering with the result before returning it to the caller. If no result is found, NULL is returned.
   *
   * @param array $criteria Query criteria
   *
   * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface|NULL
   */
    public function findOneBy(array $criteria);

  /**
   * Finds documents for this repository decorator filtered by a set of criteria
   *
   * This method forwards the call to the managed repository and fires the event 'ordermind_logical_authorization_doctrine_mongo.event.repository_decorator.lazy_document_collection_result' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\LazyDocumentCollectionResultEvent, allowing tampering with the result before returning it to the caller.
   *
   * @param Doctrine\Common\Collections\Criteria $criteria Query criteria
   *
   * @return Doctrine\Common\Collections\ArrayCollection
   */
    public function matching(\Doctrine\Common\Collections\Criteria $criteria);

  /**
   * Creates a new document decorator
   *
   * Before the creation is performed, the decorator fires the event 'ordermind_logical_authorization_doctrine_mongo.event.repository_decorator.before_create' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\BeforeCreateEvent.
   * If the abort flag in the event is then found to be TRUE the document is not created and the method returns NULL.
   * Any parameters that are provided to this method will be passed on to the document constructor.
   * If the document implements Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface and the current user implements Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface, it will automatically set the document's author to the current user.
   * If the current user is not authorized to create the target document, it will not be created and NULL will be returned. Otherwise the created document decorator will be returned.
   *
   * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface|NULL
   */
    public function create();

  /**
   * Wraps an array of documents in document decorators
   *
   * This method runs wrapDocument() for each of the documents in the array.
   *
   * @param array $documents The documents to be wrapped in document decorators
   *
   * @return array
   */
    public function wrapDocuments($documents);

  /**
   * Wraps a document in a document decorator
   *
   * If the class of the supplied document is not the same as the class from getClassName() the document is not wrapped but returned as is.
   *
   * @param mixed $document The document to be wrapped in a document decorator
   *
   * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface|mixed
   */
    public function wrapDocument($document);
}
