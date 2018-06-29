<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Event that is fired from a DocumentDecorator when a method call is attempted on a document
 */
interface BeforeMethodCallEventInterface
{

  /**
   * Gets the document on which the call is made
   *
   * @return object
   */
    public function getDocument();

  /**
   * Returns TRUE if the document is new or FALSE if the document is already persisted
   *
   * @return bool
   */
    public function isNew(): bool;

  /**
   * Gets the metadata for the document
   *
   * @return Doctrine\Common\Persistence\Mapping\ClassMetadata
   */
    public function getMetadata(): ClassMetadata;

  /**
   * Gets the method that is used for the call
   *
   * @return string
   */
    public function getMethod(): string;

  /**
   * Gets the arguments for the call
   *
   * @return array
   */
    public function getArguments(): array;

  /**
   * Gets the abort flag value for this call
   *
   * @return bool
   */
    public function getAbort(): bool;

  /**
   * Sets the abort flag value for this delete call. If the abort flag is set to true the call won't be passed to the document
   *
   * @param bool $abort The new value for the abort flag
   */
    public function setAbort(bool $abort);
}
