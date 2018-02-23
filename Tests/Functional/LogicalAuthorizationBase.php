<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;

abstract class LogicalAuthorizationBase extends WebTestCase {
  protected static $superadmin_user;
  protected static $admin_user;
  protected static $authenticated_user;
  protected $user_credentials = [
    'authenticated_user' => 'userpass',
    'admin_user' => 'adminpass',
    'superadmin_user' => 'superadminpass',
  ];
  protected $load_services = array();
  protected $testDocumentRepositoryDecorator;
  protected $testDocumentRoleAuthorRepositoryDecorator;
  protected $testDocumentHasAccountNoInterfaceRepositoryDecorator;
  protected $testDocumentNoBypassRepositoryDecorator;
  protected $testDocumentOverriddenPermissionsRepositoryDecorator;
  protected $testDocumentVariousPermissionsRepositoryDecorator;
  protected $testUserRepositoryDecorator;
  protected $testDocumentOperations;
  protected $client;

  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    require_once __DIR__.'/../AppKernel.php';
    $kernel = new \AppKernel('test', true);
    $kernel->boot();
    $this->client = static::createClient();

    $this->load_services['testDocumentRepositoryDecorator'] = 'repository.test_document';
    $this->load_services['testUserRepositoryDecorator'] = 'repository.test_user';
    $this->load_services['testDocumentOperations'] = 'test_document_operations';
    $container = $kernel->getContainer();
    foreach($this->load_services as $property_name => $service_name) {
      $this->$property_name = $container->get($service_name);
    }

    $this->deleteAll(array(
      $this->testDocumentRepositoryDecorator,
      $this->testDocumentRoleAuthorRepositoryDecorator,
      $this->testDocumentHasAccountNoInterfaceRepositoryDecorator,
      $this->testDocumentNoBypassRepositoryDecorator,
      $this->testDocumentOverriddenPermissionsRepositoryDecorator,
      $this->testDocumentVariousPermissionsRepositoryDecorator,
    ));

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    if(!is_null($this->testDocumentRepositoryDecorator)) {
      $this->testDocumentRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testDocumentRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentRoleAuthorRepositoryDecorator)) {
      $this->testDocumentRoleAuthorRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testDocumentRoleAuthorRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentHasAccountNoInterfaceRepositoryDecorator)) {
      $this->testDocumentHasAccountNoInterfaceRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testDocumentHasAccountNoInterfaceRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentNoBypassRepositoryDecorator)) {
      $this->testDocumentNoBypassRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testDocumentNoBypassRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentOverriddenPermissionsRepositoryDecorator)) {
      $this->testDocumentOverriddenPermissionsRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testDocumentOverriddenPermissionsRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentVariousPermissionsRepositoryDecorator)) {
      $this->testDocumentVariousPermissionsRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testDocumentVariousPermissionsRepositoryDecorator = null;
    }
    if(!is_null($this->testUserRepositoryDecorator)) {
      $this->testUserRepositoryDecorator->getDocumentManager()->getConnection()->close();
      $this->testUserRepositoryDecorator = null;
    }
    $this->testDocumentOperations = null;
    $this->client = null;

    parent::tearDown();
  }

  protected function deleteAll($decorators) {
    foreach($decorators as $repositoryDecorator) {
      $documentDecorators = $repositoryDecorator->findAll();
      foreach($documentDecorators as $documentDecorator) {
        $documentDecorator->delete(false);
      }
      $repositoryDecorator->getDocumentManager()->flush();
    }
  }

  protected function addUsers() {
    //Create new nodmal user
    if(!static::$authenticated_user || get_class(static::$authenticated_user->getDocument()) !== $this->testUserRepositoryDecorator->getClassName()) {
      static::$authenticated_user = $this->testUserRepositoryDecorator->create('authenticated_user', $this->user_credentials['authenticated_user'], [], 'user@email.com');
      static::$authenticated_user->save();
    }

    //Create new admin user
    if(!static::$admin_user || get_class(static::$admin_user->getDocument()) !== $this->testUserRepositoryDecorator->getClassName()) {
      static::$admin_user = $this->testUserRepositoryDecorator->create('admin_user', $this->user_credentials['admin_user'], ['ROLE_ADMIN'], 'admin@email.com');
      static::$admin_user->save();
    }

    //Create superadmin user
    if(!static::$superadmin_user || get_class(static::$superadmin_user->getDocument()) !== $this->testUserRepositoryDecorator->getClassName()) {
      static::$superadmin_user = $this->testUserRepositoryDecorator->create('superadmin_user', $this->user_credentials['superadmin_user'], [], 'superadmin@email.com');
      static::$superadmin_user->setBypassAccess(true);
      static::$superadmin_user->save();
    }
  }

  protected function sendRequestAs($method = 'GET', $slug, array $params = array(), $user = null) {
    $headers = array();
    if($user) {
      $headers = array(
        'PHP_AUTH_USER' => $user->getUsername(),
        'PHP_AUTH_PW'   => $this->user_credentials[$user->getUsername()],
      );
    }
    $this->client->request($method, $slug, $params, array(), $headers);
  }

  /*------------Miscellaneous tests---------------*/

  public function testRouteLoadDocumentAllow() {
    $testDocumentDecorator = $this->testDocumentRepositoryDecorator->create()->save();
    $this->sendRequestAs('GET', '/test/load-test-document/' . $testDocumentDecorator->getId(), [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function testRouteLoadDocumentDisallow() {
    $testDocumentDecorator = $this->testDocumentRepositoryDecorator->create()->save();
    $this->sendRequestAs('GET', '/test/load-test-document/' . $testDocumentDecorator->getId(), [], static::$authenticated_user);
  }

  public function testRepositoryDecoratorCreateSetAuthor() {
    $documentDecorator = $this->testDocumentRoleAuthorRepositoryDecorator->create();
    $author = $documentDecorator->getAuthor();
    $this->assertNull($author);

    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $id = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue((bool) $id);
    $documentDecorator = $this->testDocumentRoleAuthorRepositoryDecorator->find($id);
    $author = $documentDecorator->getAuthor();
    $this->assertNotNull($author);
    $this->assertEquals($author->getId(), static::$admin_user->getId());
  }


  /*------------RepositoryDecorator event tests------------*/

  /*---onUnknownResult---*/

  public function testOnUnknownResultRoleAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultRoleDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $documents_count = $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnUnknownResultFlagBypassAccessAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultFlagBypassAccessDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnUnknownResultFlagHasAccountAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultFlagHasAccountDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnUnknownResultFlagIsAuthorAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultFlagIsAuthorDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  /*---onSingleDocumentResult---*/

  public function testOnSingleDocumentResultRoleAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleDocumentResultRoleDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testDocumentOperations->getSingleDocumentResult($documentDecorator->getId()));
  }

  public function testOnSingleDocumentResultFlagBypassAccessAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleDocumentResultFlagBypassAccessDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testDocumentOperations->getSingleDocumentResult($documentDecorator->getId()));
  }

  public function testOnSingleDocumentResultFlagHasAccountAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleDocumentResultFlagHasAccountDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testDocumentOperations->getSingleDocumentResult($documentDecorator->getId()));
  }

  public function testOnSingleDocumentResultFlagIsAuthorAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleDocumentResultFlagIsAuthorDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/find-single-document-result/' . $documentDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testDocumentOperations->getSingleDocumentResult($documentDecorator->getId()));
  }

  /*---onMultipleDocumentResult---*/

  public function testOnMultipleDocumentResultRoleAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleDocumentResultRoleDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $documents_count = $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getMultipleDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnMultipleDocumentResultFlagBypassAccessAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleDocumentResultFlagBypassAccessDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getMultipleDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnMultipleDocumentResultFlagHasAccountAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleDocumentResultFlagHasAccountDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getMultipleDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnMultipleDocumentResultFlagIsAuthorAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleDocumentResultFlagIsAuthorDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-multiple-document-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getMultipleDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  /*---onBeforeCreate---*/

  public function testOnBeforeCreateRoleAllow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue((bool) $document_created);
  }

  public function testOnBeforeCreateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse((bool) $document_created);
  }

  public function testOnBeforeCreateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue((bool) $document_created);
  }

  public function testOnBeforeCreateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse((bool) $document_created);
  }

  public function testOnBeforeCreateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue((bool) $document_created);
  }

  public function testOnBeforeCreateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse((bool) $document_created);
  }

  /*---onLazyDocumentCollectionResult---*/

  public function testOnLazyDocumentCollectionResultRoleAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyDocumentCollectionResultRoleDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $documents_count = $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getLazyLoadedDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnLazyDocumentCollectionResultFlagBypassAccessAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyDocumentCollectionResultFlagBypassAccessDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getLazyLoadedDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnLazyDocumentCollectionResultFlagHasAccountAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyDocumentCollectionResultFlagHasAccountDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getLazyLoadedDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnLazyDocumentCollectionResultFlagIsAuthorAllow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyDocumentCollectionResultFlagIsAuthorDisallow() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testDocumentOperations->getLazyLoadedDocumentResult();
    $this->assertEquals(1, count($documents));
  }

  /*----------DocumentDecorator event tests------------*/

  /*---onBeforeMethodCall getter---*/

  public function testOnBeforeMethodCallGetterRoleAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterRoleDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeMethodCall setter---*/

  public function testOnBeforeMethodCallSetterRoleAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterRoleDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeSave create---*/

  public function testOnBeforeSaveCreateRoleAllow() {
    $this->sendRequestAs('GET', '/test/save-document-create', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeSaveCreateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-create', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/save-document-create', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-create', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/save-document-create', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-create', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  /*---onBeforeSave update---*/

  public function testOnBeforeSaveUpdateRoleAllow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/save-document-update-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/save-document-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeDelete---*/

  public function testOnBeforeDeleteRoleAllow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteRoleDisallow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeDeleteFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeDeleteFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeDeleteFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/delete-document-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/delete-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testPermissionsOverride() {
    $this->testDocumentOperations->setRepositoryDecorator($this->testDocumentOverriddenPermissionsRepositoryDecorator);
    $documentDecorator = $this->testDocumentOperations->createTestDocument();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentOverriddenPermissionsRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testAvailableActionsAnonymous() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'fields'=> [
        'id' => [
          'get' => 'get',
        ],
        'field3' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'author' => [
          'get' => 'get',
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }

  public function testAvailableActionsAuthenticated() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'read' => 'read',
      'fields' => [
        'id' => [
          'get' => 'get',
        ],
        'field1' => [
          'get' => 'get',
        ],
        'field2' => [
          'set' => 'set',
        ],
        'field3' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'author' => [
          'get' => 'get',
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }

  public function testAvailableActionsAdmin() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'read' => 'read',
      'update' => 'update',
      'fields' => [
        'id' => [
          'get' => 'get',
        ],
        'field1' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'field2' => [
          'set' => 'set',
        ],
        'field3' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'author' => [
          'get' => 'get',
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }

  public function testAvailableActionsSuperadmin() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'create' => 'create',
      'read' => 'read',
      'update' => 'update',
      'fields' => [
        'id' => [
          'get' => 'get',
        ],
        'field1' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'field2' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'field3' => [
          'get' => 'get',
          'set' => 'set',
        ],
        'author' => [
          'get' => 'get',
          'set' => 'set',
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }
}
