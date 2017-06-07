<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional\Services;

class LogicalAuthorizationXMLTest extends LogicalAuthorizationBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryDecorator' => 'repository.test_document_roleauthor_xml',
      'testDocumentHasAccountNoInterfaceRepositoryDecorator' => 'repository.test_document_hasaccount_xml',
      'testDocumentNoBypassRepositoryDecorator' => 'repository.test_document_nobypass_xml',
      'testDocumentOverriddenPermissionsRepositoryDecorator' => 'repository.test_document_overridden_permissions_xml',
      'testDocumentVariousPermissionsRepositoryDecorator' => 'repository.test_document_various_permissions_xml',
    );

    parent::setUp();
  }
}
