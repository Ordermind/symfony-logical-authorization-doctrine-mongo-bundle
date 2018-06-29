<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Annotation\Doctrine;

/**
 * @Annotation
 */
class Permissions {
  protected $permissions;

  public function __construct(array $data) {
    $this->permissions = $data['value'];
  }

  /**
   * Gets the permission tree for this document
   *
   * @return array|string|bool The permission tree for this document
   */
  public function getPermissions() {
    return $this->permissions;
  }
}
