<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents;

use Symfony\Component\EventDispatcher\Event;

/**
 * {@inheritdoc}
 */
class BeforeDeleteEvent extends Event implements BeforeDeleteEventInterface
{

  /**
   * @var mixed
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
   * @param mixed $document The document that is about to be deleted
   * @param bool  $isNew A flag for the persistence status of the document
   */
    public function __construct($document, $isNew)
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
    public function isNew()
    {
        return $this->isNew;
    }

  /**
   * {@inheritdoc}
   */
    public function getAbort()
    {
        return $this->abort;
    }

  /**
   * {@inheritdoc}
   */
    public function setAbort($abort)
    {
        $this->abort = (bool) $abort;
    }
}
