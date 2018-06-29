<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecorator;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface;
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
    public function getDocumentDecorator(DocumentManager $dm, EventDispatcherInterface $dispatcher, $document): DocumentDecoratorInterface
    {
        return new DocumentDecorator($dm, $dispatcher, $this->laModel, $document);
    }
}
