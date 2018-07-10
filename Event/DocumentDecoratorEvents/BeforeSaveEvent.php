<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents;

use Symfony\Component\EventDispatcher\Event;

/**
 * {@inheritdoc}
 */
class BeforeSaveEvent extends Event implements BeforeSaveEventInterface
{

  /**
   * @var object
   */
    protected $document;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * @var bool
     */
    protected $abort = false;

    /**
     * @internal
     *
     * @param object $document The document that is about to be saved
     * @param bool   $isNew    A flag for the persistence status of the document
     */
    public function __construct($document, bool $isNew)
    {
        $this->document = $document;
        $this->isNew = $isNew;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return $this->isNew;
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
