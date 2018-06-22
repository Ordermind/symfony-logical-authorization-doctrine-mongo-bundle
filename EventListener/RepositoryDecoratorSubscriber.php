<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\Common\Collections\Collection;

use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\AbstractResultEventInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\UnknownResultEventInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\SingleDocumentResultEventInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\MultipleDocumentResultEventInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\LazyDocumentCollectionResultEventInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\BeforeCreateEventInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;

class RepositoryDecoratorSubscriber implements EventSubscriberInterface {
  protected $laModel;
  protected $config;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface $laModel LogicalAuthorizationModel service for checking model permissions
   * @param array $config The logauth_doctrine_mongo.config parameter
   */
  public function __construct(LogicalAuthorizationModelInterface $laModel, array $config) {
    $this->laModel = $laModel;
    $this->config = $config;
  }

  /**
    * {@inheritdoc}
    */
  public static function getSubscribedEvents() {
    return array(
      'logauth_doctrine_mongo.event.repository_decorator.unknown_result' => array(
        array('onUnknownResult'),
      ),
      'logauth_doctrine_mongo.event.repository_decorator.single_document_result' => array(
        array('onSingleDocumentResult'),
      ),
      'logauth_doctrine_mongo.event.repository_decorator.multiple_document_result' => array(
        array('onMultipleDocumentResult'),
      ),
      'logauth_doctrine_mongo.event.repository_decorator.before_create' => array(
        array('onBeforeCreate'),
      ),
      'logauth_doctrine_mongo.event.repository_decorator.lazy_document_collection_result' => array(
        array('onLazyDocumentCollectionResult'),
      ),
    );
  }

  /**
   * Event subscriber callback for modifying an unknown result from a repository decorator if access is not granted
   *
   * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\UnknownResultEventInterface $event The subscribed event
   */
  public function onUnknownResult(UnknownResultEventInterface $event) {
    $this->onResult($event);
  }

  /**
   * Event subscriber callback for modifying a single document result from a repository decorator if access is not granted
   *
   * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\SingleDocumentResultEventInterface $event The subscribed event
   */
  public function onSingleDocumentResult(SingleDocumentResultEventInterface $event) {
    $this->onResult($event);
  }

  /**
   * Event subscriber callback for modifying a multiple document result from a repository decorator if access is not granted
   *
   * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\MultipleDocumentResultEventInterface $event The subscribed event
   */
  public function onMultipleDocumentResult(MultipleDocumentResultEventInterface $event) {
    $this->onResult($event);
  }

  /**
   * Event subscriber callback for aborting the creation of a document by a repository decorator if access is not granted
   *
   * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\BeforeCreateEventInterface $event The subscribed event
   */
  public function onBeforeCreate(BeforeCreateEventInterface $event) {
    $class = $event->getDocumentClass();
    if(!$this->laModel->checkModelAccess($class, 'create')) {
      $event->setAbort(true);
    }
  }

  /**
   * Event subscriber callback for modifying a lazy document collection result from a repository decorator if access is not granted
   *
   * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\LazyDocumentCollectionResultEventInterface $event The subscribed event
   */
  public function onLazyDocumentCollectionResult(LazyDocumentCollectionResultEventInterface $event) {
    if(empty($this->config['check_lazy_loaded_documents'])) return;

    $this->onResult($event);
  }

  protected function onResult(AbstractResultEventInterface $event) {
    $repository = $event->getRepository();
    $result = $event->getResult();
    $class = $repository->getClassName();
    if(is_array($result)) {
      $filtered_result = $this->filterDocuments($result, $class);
    }
    elseif($result instanceof Collection) {
      $filtered_result = $this->filterDocumentCollection($result, $class);
    }
    else {
      $filtered_result = $this->filterDocumentByPermissions($result, $class);
    }

    $event->setResult($filtered_result);
  }
  protected function filterDocuments($documents, $class) {
    foreach($documents as $i => $document) {
      $documents[$i] = $this->filterDocumentByPermissions($document, $class);
    }
    $documents = array_filter($documents);
    return $documents;
  }
  protected function filterDocumentCollection($collection, $class) {
    foreach($collection as $i => $document) {
      if(is_null($this->filterDocumentByPermissions($document, $class))) {
        $collection->remove($i);
      };
    }
    return $collection;
  }
  protected function filterDocumentByPermissions($document, $class) {
    if(!is_object($document) || get_class($document) !== $class) return $document;

    if(!$this->laModel->checkModelAccess($document, 'read')) {
      return null;
    }

    return $document;
  }
}
