<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services;

class LogicalAuthorizationODMAnnotationTest extends LogicalAuthorizationBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryDecorator' => 'repository.test_document_roleauthor_annotation',
      'testDocumentHasAccountNoInterfaceRepositoryDecorator' => 'repository.test_document_hasaccount_annotation',
      'testDocumentNoBypassRepositoryDecorator' => 'repository.test_document_nobypass_annotation',
      'testDocumentOverriddenPermissionsRepositoryDecorator' => 'repository.test_document_overridden_permissions_annotation',
      'testDocumentVariousPermissionsRepositoryDecorator' => 'repository.test_document_various_permissions_annotation',
    );

    parent::setUp();
  }
}
