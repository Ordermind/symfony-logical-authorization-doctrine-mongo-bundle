<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Tests\Fixtures\Services;

use Doctrine\Common\Collections\Criteria;

use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecoratorInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class TestDocumentOperations {
  private $repositoryDecorator;

  public function setRepositoryDecorator(RepositoryDecoratorInterface $repositoryDecorator) {
    $this->repositoryDecorator = $repositoryDecorator;
  }

  public function getUnknownResult($bypassAccess = false) {
    if($bypassAccess) {
      $documents = $this->repositoryDecorator->getRepository()->customMethod();
      return $this->repositoryDecorator->wrapDocuments($documents);
    }
    return $this->repositoryDecorator->customMethod();
  }

  public function getSingleDocumentResult($id, $bypassAccess = false) {
    if($bypassAccess) {
      $document = $this->repositoryDecorator->getRepository()->find($id);
      return $this->repositoryDecorator->wrapDocument($document);
    }
    return $this->repositoryDecorator->find($id);
  }

  public function getMultipleDocumentResult($bypassAccess = false) {
    if($bypassAccess) {
      $documents = $this->repositoryDecorator->getRepository()->findAll();
      return $this->repositoryDecorator->wrapDocuments($documents);
    }
    return $this->repositoryDecorator->findAll();
  }

  public function getLazyLoadedDocumentResult($bypassAccess = false) {
    if($bypassAccess) {
      return $this->repositoryDecorator->getRepository()->matching(Criteria::create());
    }
    return $this->repositoryDecorator->matching(Criteria::create());
  }

  public function createTestDocument($user = null, $bypassAccess = false) {
    if($user && $user instanceof DocumentDecoratorInterface) {
      $this->repositoryDecorator->setDocumentManager($user->getDocumentManager());
      $user = $user->getDocument();
    }

    if($bypassAccess) {
      $class = $this->repositoryDecorator->getClassName();
      $document = new $class();
      $documentDecorator = $this->repositoryDecorator->wrapDocument($document);
    }
    else {
      $documentDecorator = $this->repositoryDecorator->create();
    }

    if($documentDecorator) {
      if($bypassAccess) {
        $document = $documentDecorator->getDocument();
        if($user instanceof UserInterface) {
          $document->setAuthor($user);
        }
        $dm = $documentDecorator->getDocumentManager();
        $dm->persist($document);
        $dm->flush();
      }
      else {
        if($user instanceof UserInterface) {
          $documentDecorator->setAuthor($user);
        }
        $documentDecorator->save();
      }
    }

    return $documentDecorator;
  }

  public function callMethodGetter(DocumentDecoratorInterface $documentDecorator, $bypassAccess = false) {
    if($bypassAccess) {
      return $documentDecorator->getDocument()->getField1();
    }
    return $documentDecorator->getField1();
  }

  public function callMethodSetter(DocumentDecoratorInterface $documentDecorator, $bypassAccess = false) {
    if($bypassAccess) {
      $documentDecorator->getDocument()->setField1('test');
    }
    else {
      $documentDecorator->setField1('test');
    }
    return $documentDecorator;
  }
}