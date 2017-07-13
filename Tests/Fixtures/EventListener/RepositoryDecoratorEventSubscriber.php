<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\AbstractResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\UnknownResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\SingleDocumentResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\MultipleDocumentResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\LazyDocumentCollectionResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\BeforeCreateEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\TestDocumentAbortCreate;

class RepositoryDecoratorEventSubscriber implements EventSubscriberInterface {
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
      'logauth_doctrine_mongo.event.repository_decorator.lazy_document_collection_result' => array(
        array('onLazyDocumentCollectionResult'),
      ),
      'logauth_doctrine_mongo.event.repository_decorator.before_create' => array(
        array('onBeforeCreate'),
      ),
    );
  }

  public function onUnknownResult(UnknownResultEvent $event) {
    $this->onResult($event);
  }

  public function onSingleDocumentResult(SingleDocumentResultEvent $event) {
    $this->onResult($event);
  }

  public function onMultipleDocumentResult(MultipleDocumentResultEvent $event) {
    $this->onResult($event);
  }

  public function onLazyDocumentCollectionResult(LazyDocumentCollectionResultEvent $event) {
    $repository = $event->getRepository();
    $result = $event->getResult();
    $class = $repository->getClassName();
    foreach($result as $i => $item) {
      $result[$i] = $this->processDocument($item, $class);
    }
  }

  public function onBeforeCreate(BeforeCreateEvent $event) {
    $class = $event->getDocumentClass();
    if($class === 'Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocumentAbortCreate') {
      $event->setAbort(true);
    }
  }

  protected function onResult(AbstractResultEvent $event) {
    $repository = $event->getRepository();
    $result = $event->getResult();
    $class = $repository->getClassName();
    $result = $this->processDocuments($result, $class);
    $event->setResult($result);
  }

  protected function processDocuments($documents, $class) {
    if(!is_array($documents)) return $this->processDocument($documents, $class);
    foreach($documents as $i => $document) {
      $documents[$i] = $this->processDocument($document, $class);
    }
    return $documents;
  }

  protected function processDocument($document, $class) {
    if(!is_object($document) || get_class($document) !== $class) return $document;
    $document->setField2('hej');
    return $document;
  }
}