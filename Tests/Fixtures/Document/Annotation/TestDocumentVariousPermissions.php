<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Annotation;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Annotation\Doctrine\Permissions;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

/**
 * TestDocumentVariousPermissions
 *
 * @ODM\Document(repositoryClass="Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Repository\Annotation\TestDocumentVariousPermissionsRepository", collection="testdocuments_various_permissions_annotation")
 * @Permissions({
 *   "create": FALSE,
 *   "read": {"flag": "user_has_account"},
 *   "update": {"role": "ROLE_ADMIN"},
 *   "delete": {"no_bypass": true, FALSE}
 * })
 */
class TestDocumentVariousPermissions implements ModelInterface
{
    /**
     * @var int
     *
     * @ODM\Field(name="id", type="integer")
     * @ODM\Id
     * @Permissions({
     *   "set": {"no_bypass": true, FALSE}
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ODM\Field(name="field1", type="string")
     * @Permissions({
     *   "get": {"flag": "user_has_account"},
     *   "set": {"role": "ROLE_ADMIN"}
     * })
     */
    private $field1 = '';

    /**
     * @var string
     *
     * @ODM\Field(name="field2", type="string")
     * @Permissions({
     *   "get": FALSE,
     *   "set": {"flag": "user_is_author"}
     * })
     */
    private $field2 = '';

    /**
     * @var string
     *
     * @ODM\Field(name="field3", type="string")
     */
    private $field3 = '';

    /**
     * @var \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
     * @ODM\ReferenceOne(targetDocument="Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\User\TestUser")
     * @Permissions({
     *   "set": FALSE
     * })
     */
    protected $author;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set field1
     *
     * @param string $field1
     *
     * @return TestDocumentVariousPermissions
     */
    public function setField1($field1)
    {
        $this->field1 = $field1;

        return $this;
    }

    /**
     * Get field1
     *
     * @return string
     */
    public function getField1()
    {
        return $this->field1;
    }

    /**
     * Set field2
     *
     * @param string $field2
     *
     * @return TestDocumentVariousPermissions
     */
    public function setField2($field2)
    {
        $this->field2 = $field2;

        return $this;
    }

    /**
     * Get field2
     *
     * @return string
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * Set field3
     *
     * @param string $field3
     *
     * @return TestDocumentVariousPermissions
     */
    public function setField3($field3)
    {
        $this->field3 = $field3;

        return $this;
    }

    /**
     * Get field3
     *
     * @return string
     */
    public function getField3()
    {
        return $this->field3;
    }

    /**
     * Set author
     *
     * @param \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface $author
     *
     * @return document implementing ModelInterface
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get authorId
     *
     * @return \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
     */
    public function getAuthor(): ?UserInterface {
        return $this->author;
    }

}


