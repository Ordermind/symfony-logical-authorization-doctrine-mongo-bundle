<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeMethodCallEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeSaveEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeDeleteEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocumentAbortCalls;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocumentAbortSave;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocumentAbortDelete;

class DocumentDecoratorEventSubscriber implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return array(
      'logauth_doctrine_mongo.event.document_decorator.before_method_call' => array(
        array('onBeforeMethodCall'),
      ),
      'logauth_doctrine_mongo.event.document_decorator.before_save' => array(
        array('onBeforeSave'),
      ),
      'logauth_doctrine_mongo.event.document_decorator.before_delete' => array(
        array('onBeforeDelete'),
      ),
    );
  }

  public function onBeforeMethodCall(BeforeMethodCallEvent $event) {
    $document = $event->getDocument();
    if($document instanceof TestDocumentAbortCalls) {
      $event->setAbort(true);
    }
  }

  public function onBeforeSave(BeforeSaveEvent $event) {
    $document = $event->getDocument();
    if($document instanceof TestDocumentAbortSave) {
      $event->setAbort(true);
    }
  }

  public function onBeforeDelete(BeforeDeleteEvent $event) {
    $document = $event->getDocument();
    if($document instanceof TestDocumentAbortDelete) {
      $event->setAbort(true);
    }
  }
}