<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecorator;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;

/**
 * {@inheritdoc}
 */
class DocumentDecoratorFactory implements DocumentDecoratorFactoryInterface
{

  /**
   * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface
   */
    protected $laModel;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface $laModel LogicalAuthorizationModel service
   */
    public function __construct(LogicalAuthorizationModelInterface $laModel) {
      $this->laModel = $laModel;
    }

  /**
   * {@inheritdoc}
   */
    public function getDocumentDecorator(ObjectManager $dm, EventDispatcherInterface $dispatcher, $document)
    {
        return new DocumentDecorator($dm, $dispatcher, $this->laModel, $document);
    }
}
