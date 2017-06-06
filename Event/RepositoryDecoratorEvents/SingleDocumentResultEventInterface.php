<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents;

/**
 * Event for single document result
 *
 * This event is fired when a repository returns a single document.
 */
interface SingleDocumentResultEventInterface extends AbstractResultEventInterface
{
}
