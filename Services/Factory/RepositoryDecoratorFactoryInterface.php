<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory;

/**
 * Factory for Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecoratorInterface
 */
interface RepositoryDecoratorFactoryInterface
{

  /**
   * Sets the manager registry
   *
   * @param Doctrine\Common\Persistence\ManagerRegistry $managerRegistry The manager registry to use for this repository decorator factory
   */
    public function setManagerRegistry(\Doctrine\Common\Persistence\ManagerRegistry $managerRegistry);

    /**
     * Sets the document decorator factory
     *
     * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory\DocumentDecoratorFactoryInterface $documentDecoratorFactory The document decorator factory to use for this repository decorator factory
     */
    public function setDocumentDecoratorFactory(\Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory\DocumentDecoratorFactoryInterface $documentDecoratorFactory);

    /**
     * Sets the event dispatcher
     *
     * @param Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher to use for this repository decorator factory
     */
    public function setDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher);

    /**
     * Sets the helper service
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper The helper service to use for this repository decorator factory
     */
    public function setHelper(\Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper);

    /**
     * Gets a new repository decorator
     *
     * @param string $class The document class to use for the new repository decorator
     *
     * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecoratorInterface A new repository decorator
     */
    public function getRepositoryDecorator(string $class): \Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecoratorInterface;
}
