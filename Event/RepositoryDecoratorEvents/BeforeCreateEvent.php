<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents;

use Symfony\Component\EventDispatcher\Event;

/**
 * {@inheritdoc}
 */
class BeforeCreateEvent extends Event implements BeforeCreateEventInterface
{

  /**
   * @var string
   */
    protected $documentClass;

    /**
     * @var bool
     */
    protected $abort = false;

    /**
     * @internal
     *
     * @param string $documentClass The class of the document that is about to be created
     */
    public function __construct(string $documentClass)
    {
        $this->documentClass = $documentClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getAbort(): bool
    {
        return $this->abort;
    }

    /**
     * {@inheritdoc}
     */
    public function setAbort(bool $abort)
    {
        $this->abort = $abort;
    }
}
