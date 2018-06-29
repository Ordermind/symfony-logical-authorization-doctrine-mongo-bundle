<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\User;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Security\Core\User\UserInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Annotation\Doctrine\Permissions;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface as LogicalAuthorizationUserInterface;

/**
 * TestUser
 *
 * @ODM\Document(repositoryClass="Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Repository\User\TestUserRepository", collection="testusers")
 * @Permissions({
 *   "create": {
 *     "role": "ROLE_ADMIN"
 *   },
 *   "read": {
 *     "role": "ROLE_ADMIN",
 *     "flag": "user_is_author"
 *   },
 *   "update": {
 *     "OR": {
 *       "role": "ROLE_ADMIN",
 *       "flag": "user_is_author"
 *     }
 *   },
 *   "delete": {
 *     "no_bypass": {
 *       "flag": "user_is_author"
 *     },
 *     "AND": {
 *       "role": "ROLE_ADMIN",
 *       "flag": {
 *         "NOT": "user_is_author"
 *       }
 *     }
 *   }
 * })
 */
class TestUser implements UserInterface, LogicalAuthorizationUserInterface, \Serializable
{
  /**
   * @var string
   *
   * @ODM\Field(name="id", type="guid")
   * @ODM\Id
   */
  private $id;

  /**
   * @var string
   *
   * @ODM\Field(name="username", type="string")
   * @Permissions({
   *   "get": {
   *     "OR": {
   *       "role": "ROLE_ADMIN",
   *       "flag": "user_is_author"
   *     }
   *   },
   *   "set": {
   *     "role": "ROLE_ADMIN"
   *   }
   * })
   */
  private $username;

  /**
   * @var string
   *
   * @ODM\Field(name="password", type="string")
   */
  private $password;

  /**
   * @var string
   * @Permissions({
   *   "set": {
   *     "no_bypass": true,
   *     "flag": "user_is_author"
   *   }
   * })
   */
  private $oldPassword;

  /**
   * @var array
   *
   * @ODM\Field(name="roles", type="collection")
   * @Permissions({
   *   "get": {
   *     "role": "ROLE_ADMIN"
   *   },
   *   "set": {
   *     "AND": {
   *       "role": "ROLE_ADMIN",
   *       "flag": {
   *         "NOT": "user_is_author"
   *       }
   *     }
   *   }
   * })
   */
  private $roles;

  /**
   * @var string
   *
   * @ODM\Field(name="email", type="string")
   */
  private $email;

  /**
   * @var bool
   *
   * @ODM\Field(name="bypassAccess", type="boolean")
   */
  private $bypassAccess;

  public function __construct($username = '', $password = '', $roles = [], $email = '', $bypassAccess = false) {
    if($username) {
      $this->setUsername($username);
    }
    if($password) {
      $this->setPassword($password);
    }
    $this->setRoles($roles);
    if($email) {
      $this->setEmail($email);
    }
    $this->setBypassAccess($bypassAccess);
  }


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
   * Set username
   *
   * @param string $username
   *
   * @return TestUser
   */
  public function setUsername($username)
  {
    $this->username = $username;

    return $this;
  }

  /**
   * Get username
   *
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Set password
   *
   * @param string $password
   *
   * @return TestUser
   */
  public function setPassword($password)
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Get password
   *
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Set old password
   *
   * @param string $oldPassword
   *
   * @return TestUser
   */
  public function setOldPassword($password)
  {
    $encoder = new BCryptPasswordEncoder(static::bcryptStrength);
    $this->oldPassword = $encoder->encodePassword($password, $this->getSalt());

    return $this;
  }

  /**
   * Get old password
   *
   * @return string
   */
  public function getOldPassword()
  {
    return $this->oldPassword;
  }

  /**
   * Set roles
   *
   * @return array
   */
  public function setRoles($roles) {
    if(array_search('ROLE_USER', $roles) === false) {
      array_unshift($roles, 'ROLE_USER');
    }
    $this->roles = $roles;
  }

  /**
   * Get roles. Please use getFilteredRoles() instead.
   *
   * @return array
   */
  public function getRoles() {
    return $this->roles;
  }

  /**
   * Get filtered roles.
   *
   * @return array
   */
  public function getFilteredRoles() {
    $roles = $this->roles;
    if(($key = array_search('ROLE_USER', $roles)) !== false) {
      unset($roles[$key]);
    }
    return $roles;
  }

  /**
   * Set email
   *
   * @param string $email
   *
   * @return TestUser
   */
  public function setEmail($email)
  {
    $this->email = $email;

    return $this;
  }

  /**
   * Get email
   *
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set bypassAccess
   *
   * @param boolean $bypassAccess
   *
   * @return TestUser
   */
  public function setBypassAccess(bool $bypassAccess)
  {
    $this->bypassAccess = $bypassAccess;

    return $this;
  }

  /**
   * Get bypassAccess
   *
   * @return bool
   */
  public function getBypassAccess(): bool
  {
    return $this->bypassAccess;
  }

  public function getSalt() {
    return null; //bcrypt doesn't require a salt.
  }

  public function eraseCredentials() {

  }

  public function serialize() {
    return serialize(array(
      $this->id,
      $this->username,
      $this->password,
    ));
  }

  public function unserialize($serialized) {
    list (
      $this->id,
      $this->username,
      $this->password,
    ) = unserialize($serialized);
  }
}

