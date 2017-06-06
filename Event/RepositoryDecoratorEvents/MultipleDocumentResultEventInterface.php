<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents;

/**
 * Event for multiple document result
 *
 * This event is fired when a repository returns an array of documents.
 */
interface MultipleDocumentResultEventInterface extends AbstractResultEventInterface
{
}
