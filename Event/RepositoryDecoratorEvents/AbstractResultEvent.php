<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * {@inheritdoc}
 */
abstract class AbstractResultEvent extends Event implements AbstractResultEventInterface
{

  /**
   * @var Doctrine\Common\Persistence\ObjectRepository
   */
    protected $repository;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @internal
     *
     * @param Doctrine\Common\Persistence\ObjectRepository $repository The repository that returned the result
     * @param string                                       $method     The method that was used for the call
     * @param array                                        $arguments  The arguments for the call
     * @param mixed                                        $result     The returned result
     */
    public function __construct(ObjectRepository $repository, string $method, array $arguments, $result)
    {
        $this->repository = $repository;
        $this->method = $method;
        $this->arguments = $arguments;
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(): ObjectRepository
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
