<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents;

/**
 * Event that is fired from a DocumentDecorator when a document is attempted to be saved
 */
interface BeforeSaveEventInterface
{

  /**
   * Gets the document that is about to be saved
   *
   * @return mixed
   */
    public function getDocument();

  /**
   * Returns TRUE if the document is new or FALSE if the document is already persisted
   *
   * @return bool
   */
    public function isNew();

  /**
   * Gets the abort flag value for this save call
   *
   * @return bool
   */
    public function getAbort();

  /**
   * Sets the abort flag value for this save call. If the abort flag is set to true the document won't be saved
   *
   * @param bool $abort The new value for the abort flag
   */
    public function setAbort($abort);
}
