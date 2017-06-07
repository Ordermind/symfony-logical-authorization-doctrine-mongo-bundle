<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services;

class LogicalAuthorizationYMLTest extends LogicalAuthorizationBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryDecorator' => 'repository.test_document_roleauthor_yml',
      'testDocumentHasAccountNoInterfaceRepositoryDecorator' => 'repository.test_document_hasaccount_yml',
      'testDocumentNoBypassRepositoryDecorator' => 'repository.test_document_nobypass_yml',
      'testDocumentOverriddenPermissionsRepositoryDecorator' => 'repository.test_document_overridden_permissions_yml',
      'testDocumentVariousPermissionsRepositoryDecorator' => 'repository.test_document_various_permissions_yml',
    );

    parent::setUp();
  }
}
