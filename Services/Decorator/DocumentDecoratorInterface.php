<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;

/**
 * Decorator for document
 *
 * Wraps a document and monitors all communication with it. It also provides a few handy methods.
 */
interface DocumentDecoratorInterface extends ModelDecoratorInterface
{

  /**
   * Gets the document that is wrapped by this decorator
   *
   * @return object
   */
    public function getDocument();

    /**
     * Overrides the document manager that is used in this decorator
     *
     * @param Doctrine\Common\Persistence\ObjectManager $dm The document manager that is to be used in this decorator
     *
     * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecorator
     */
    public function setDocumentManager(\Doctrine\Common\Persistence\ObjectManager $dm);

    /**
     * Gets the document manager that is used in this decorator
     *
     * @return Doctrine\Common\Persistence\ObjectManager
     */
    public function getDocumentManager(): \Doctrine\Common\Persistence\ObjectManager;

    /**
     * Gets all available document and field actions on this document for a given user
     *
     * This method is primarily meant to facilitate client-side authorization by providing a map of all available actions on a document. The map has the structure ['document_action1' => 'document_action1', 'document_action3' => 'document_action3', 'fields' => ['field_name1' => ['field_action1' => 'field_action1']]].
     *
     * @param object|string $user            (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
     * @param array         $documentActions (optional) A list of document actions that should be evaluated. Default actions are the standard CRUD actions.
     * @param array         $fieldActions    (optional) A list of field actions that should be evaluated. Default actions are 'get' and 'set'.
     *
     * @return array A map of available actions
     */
    public function getAvailableActions($user = null, array $documentActions = ['create', 'read', 'update', 'delete'], array $fieldActions = ['get', 'set']);

    /**
     * Returns TRUE if the document is new. Returns FALSE if the document is persisted.
     *
     * @return bool
     */
    public function isNew(): bool;

    /**
     * Saves the wrapped document
     *
     * Before the save is performed, the decorator fires the event 'logauth_doctrine_mongo.event.document_decorator.before_save' and passes Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeSaveEvent.
     * If the abort flag in the event is then found to be TRUE the document is not saved and the method returns FALSE.
     * If the save succeeds the method returns the document decorator.
     *
     * @param bool $andFlush (optional) Determines whether the document decorator should be flushed after persisting the document. Default value is TRUE.
     *
     * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface|FALSE
     */
    public function save(bool $andFlush = true);

    /**
     * Deletes the wrapped document
     *
     * Before the deletion is performed, the decorator fires the event 'logauth_doctrine_mongo.event.document_decorator.before_delete' and passes Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeDeleteEvent.
     * If the abort flag in the event is then found to be TRUE the document is not deleted and the method returns FALSE.
     * If the deletion succeeds the method returns the document decorator.
     *
     * @param bool $andFlush (optional) Determines whether the document decorator should be flushed after removing the document. Default value is TRUE.
     *
     * @return Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface|FALSE
     */
    public function delete(bool $andFlush = true);
}
