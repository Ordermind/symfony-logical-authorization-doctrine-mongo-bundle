<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Twig;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;

/**
 * {@inheritdoc}
 */
class LogicalAuthorizationDoctrineMongoExtension extends \Twig_Extension
{
    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface
     */
    protected $laModel;

    /**
     * @internal
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface $laModel
     */
    public function __construct(LogicalAuthorizationModelInterface $laModel)
    {
        $this->laModel = $laModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return array(
        new \Twig_SimpleFunction('logauth_doctrine_mongo_check_document_access', array($this, 'checkDocumentAccess')),
        new \Twig_SimpleFunction('logauth_doctrine_mongo_check_field_access', array($this, 'checkFieldAccess')),
        );
    }

    /**
     * Twig extension callback for checking document access
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined for this action on the provided document it will return TRUE.
     *
     * @param object|string $document A document object or class string.
     * @param string        $action   Examples of document actions are "create", "read", "update" and "delete".
     * @param object|string $user     (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied.
     */
    public function checkDocumentAccess($document, string $action, $user = null): bool
    {
        return $this->laModel->checkModelAccess($document, $action, $user);
    }

    /**
     * Twig extension callback for checking field access
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined for this action on the provided field and document it will return TRUE.
     *
     * @param object|string $document  A document object or class string.
     * @param string        $fieldName The name of the field.
     * @param string        $action    Examples of field actions are "get" and "set".
     * @param object|string $user      (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied.
     */
    public function checkFieldAccess($document, string $fieldName, string $action, $user = null): bool
    {
        return $this->laModel->checkFieldAccess($document, $fieldName, $action, $user);
    }
}
