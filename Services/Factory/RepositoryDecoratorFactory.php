<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecorator;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

/**
 * {@inheritdoc}
 */
class RepositoryDecoratorFactory implements RepositoryDecoratorFactoryInterface
{

  /**
   * @var Doctrine\Common\Persistence\ManagerRegistry
   */
    protected $managerRegistry;

  /**
   * @var Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory\DocumentDecoratorFactoryInterface
   */
    protected $documentDecoratorFactory;

  /**
   * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
    protected $dispatcher;

  /**
   * @var Ordermind\LogicalAuthorizationBundle\Services\HelperInterface
   */
    protected $helper;

  /**
   * {@inheritdoc}
   */
    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

  /**
   * {@inheritdoc}
   */
    public function setDocumentDecoratorFactory(DocumentDecoratorFactoryInterface $documentDecoratorFactory)
    {
        $this->documentDecoratorFactory = $documentDecoratorFactory;
    }

  /**
   * {@inheritdoc}
   */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

  /**
   * {@inheritdoc}
   */
    public function setHelper(HelperInterface $helper)
    {
        $this->helper = $helper;
    }

  /**
   * {@inheritdoc}
   */
    public function getRepositoryDecorator(string $class): RepositoryDecoratorInterface
    {
        $dm = $this->managerRegistry->getManagerForClass($class);

        return new RepositoryDecorator($dm, $this->documentDecoratorFactory, $this->dispatcher, $this->helper, $class);
    }
}
