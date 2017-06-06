<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents;

/**
 * Event that is fired from a RepositoryDecorator when a document is about to be created
 */
interface BeforeCreateEventInterface
{

  /**
   * Gets the class of the document that is about to be created
   *
   * @return string
   */
    public function getDocumentClass();

  /**
   * Gets the abort flag value for this creation
   *
   * @return bool
   */
    public function getAbort();

  /**
   * Sets the abort flag value for this creation
   *
   * If the abort flag is set to true the document won't be created.
   *
   * @param bool $abort The new value for the abort flag
   */
    public function setAbort($abort);
}
