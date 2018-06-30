<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory;

/**
 * Factory for Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface
 */
interface DocumentDecoratorFactoryInterface
{

  /**
   * Gets a new document decorator
   *
   * @param Doctrine\ODM\MongoDB\DocumentManager                       $dm         The document manager to use for the new document decorator
   * @param Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher to use for the new document decorator
   * @param object                                                     $document   The document to wrap in the manager
   *
   * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface
   */
    public function getDocumentDecorator(\Doctrine\ODM\MongoDB\DocumentManager $dm, \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher, $document): \Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface;
}
