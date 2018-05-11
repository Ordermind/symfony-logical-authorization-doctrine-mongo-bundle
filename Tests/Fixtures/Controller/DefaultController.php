<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Document\Misc\TestDocument;

class DefaultController extends Controller {

  /**
    * @Route("/count-unknown-result", name="count_unknown_result")
    * @Method({"GET"})
    */
  public function countUnknownResultAction(Request $request) {
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/find-single-document-result/{id}", name="find_single_document_result")
    * @Method({"GET"})
    */
  public function findSingleDocumentResultAction(Request $request, $id) {
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $result = $operations->getSingleDocumentResult($id);
    return new JsonResponse((bool) $result);
  }

  /**
    * @Route("/count-multiple-document-result", name="count_multiple_document_result")
    * @Method({"GET"})
    */
  public function countMultipleDocumentResultAction(Request $request) {
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $result = $operations->getMultipleDocumentResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-documents-lazy", name="test_count_documents_lazy")
    * @Method({"GET"})
    */
  public function countDocumentsLazyLoadAction(Request $request) {
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $collection = $operations->getLazyLoadedDocumentResult();
    return new Response(count($collection));
  }

  /**
    * @Route("/create-document", name="create_document")
    * @Method({"GET"})
    */
  public function createDocumentAction(Request $request) {
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $documentDecorator = $operations->createTestDocument();
    if(is_object($documentDecorator) && $documentDecorator instanceof DocumentDecoratorInterface) {
      return new JsonResponse($documentDecorator->getId());
    }

    return new JsonResponse(false);
  }

  /**
    * @Route("/call-method-getter", name="call_method_getter")
    * @Method({"GET"})
    */
  public function callMethodGetterAction(Request $request) {
    $author = $this->get('repository.test_user')->getRepository()->find($request->get('author'));
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $documentDecorator = $operations->createTestDocument($author, true);
    $operations->callMethodSetter($documentDecorator, true);

    return new Response($operations->callMethodGetter($documentDecorator));
  }

  /**
    * @Route("/call-method-setter", name="call_method_setter")
    * @Method({"GET"})
    */
  public function callMethodSetterAction(Request $request) {
    $author = $this->get('repository.test_user')->getRepository()->find($request->get('author'));
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $documentDecorator = $operations->createTestDocument($author, true);
    $operations->callMethodSetter($documentDecorator);

    return new Response($operations->callMethodGetter($documentDecorator, true));
  }

  /**
    * @Route("/save-document-create", name="save_document_create")
    * @Method({"GET"})
    */
  public function saveDocumentCreateAction(Request $request) {
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $operations->createTestDocument();
    $result = $operations->getMultipleDocumentResult(true);
    return new Response(count($result));
  }

  /**
    * @Route("/save-document-update", name="save_document_update")
    * @Method({"GET"})
    */
  public function saveDocumentUpdateAction(Request $request) {
    $author = $this->get('repository.test_user')->getRepository()->find($request->get('author'));
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $documentDecorator = $operations->createTestDocument($author, true);
    $operations->callMethodSetter($documentDecorator, true);
    $documentDecorator->save();
    $documentDecorator->getDocumentManager()->detach($documentDecorator->getDocument());
    $persistedDocumentDecorator = $operations->getSingleDocumentResult($documentDecorator->getDocument()->getId(), true);
    return new Response($operations->callMethodGetter($persistedDocumentDecorator, true));
  }

  /**
    * @Route("/delete-document", name="delete_document")
    * @Method({"GET"})
    */
  public function deleteDocumentAction(Request $request) {
    $author = $this->get('repository.test_user')->getRepository()->find($request->get('author'));
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $documentDecorator = $operations->createTestDocument($author, true);
    $documentDecorator->delete();
    $result = $operations->getMultipleDocumentResult(true);
    return new Response(count($result));
  }

  /**
    * @Route("/get-available-actions", name="get_available_actions")
    * @Method({"GET"})
    */
  public function getAvailableActionsAction(Request $request) {
    $user = $this->get('test.logauth.service.helper')->getCurrentUser();
    $operations = $this->get('test_document_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $documentDecorator = $operations->createTestDocument($user, true);
    $result = $documentDecorator->getAvailableActions();
    return new JsonResponse($result);
  }

  /**
   * @Route("/repository-decorator-create", name="test_repository_decorator_create")
   * @Method({"GET"})
   */
  public function repositoryDecoratorCreateAction(Request $request) {
    $documentDecorator = $this->get('repository.test_document')->create()->save();
    return new Response('');
  }

  /**
   * @Route("/load-test-document/{id}", name="load_test_document")
   * @Method({"GET"})
   * @Permissions({
   *   "role": "ROLE_ADMIN"
   * })
   */
  public function loadTestDocumentAction(Request $request, TestDocument $testDocument = null) {
    return new Response(get_class($testDocument));
  }
}
