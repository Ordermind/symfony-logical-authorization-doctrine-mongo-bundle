<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Criteria;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory\DocumentDecoratorFactoryInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\UnknownResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\SingleDocumentResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\MultipleDocumentResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\LazyDocumentCollectionResultEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\BeforeCreateEvent;

/**
 * {@inheritdoc}
 */
class RepositoryDecorator implements RepositoryDecoratorInterface
{

  /**
   * @var Doctrine\Common\Persistence\ObjectManager
   */
    protected $dm;

  /**
   * @var Doctrine\Common\Persistence\ObjectRepository
   */
    protected $repository;

  /**
   * @var Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory\DocumentDecoratorFactoryInterface
   */
    protected $documentDecoratorFactory;

  /**
   * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
    protected $dispatcher;

  /**
   * @var Ordermind\LogicalAuthorizationBundle\Services\HelperInterface
   */
    protected $helper;

  /**
   * @internal
   *
   * @param Doctrine\Common\Persistence\ObjectManager                                     $dm                  The document manager to use in this decorator
   * @param Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Factory\DocumentDecoratorFactoryInterface $documentDecoratorFactory The factory to use for creating new document decorators
   * @param Symfony\Component\EventDispatcher\EventDispatcherInterface                    $dispatcher          The event dispatcher to use in this decorator
   * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper LogicalAuthorizaton helper service
   * @param string                                                                        $class               The document class to use in this decorator
   */
    public function __construct(ObjectManager $dm, DocumentDecoratorFactoryInterface $documentDecoratorFactory, EventDispatcherInterface $dispatcher, HelperInterface $helper, $class)
    {
        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);
        $this->documentDecoratorFactory = $documentDecoratorFactory;
        $this->dispatcher = $dispatcher;
        $this->helper = $helper;
    }

  /**
   * {@inheritdoc}
   */
    public function getClassName()
    {
        $repository = $this->getRepository();

        return $repository->getClassName();
    }

  /**
   * {@inheritdoc}
   */
    public function setDocumentManager(ObjectManager $dm)
    {
        $this->dm = $dm;

        return $this;
    }

  /**
   * {@inheritdoc}
   */
    public function getDocumentManager()
    {
        return $this->dm;
    }

  /**
   * {@inheritdoc}
   */
    public function getRepository()
    {
        return $this->repository;
    }

  /**
   * {@inheritdoc}
   */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $repository = $this->getRepository();
        $result = $repository->find($id, $lockMode, $lockVersion);
        $event = new SingleDocumentResultEvent($repository, 'find', [$id, $lockMode, $lockVersion], $result);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.single_document_result', $event);
        $result = $event->getResult();

        return $this->wrapDocument($result);
    }

  /**
   * {@inheritdoc}
   */
    public function findAll()
    {
        $repository = $this->getRepository();
        $result = $repository->findAll();
        $event = new MultipleDocumentResultEvent($repository, 'findAll', [], $result);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.multiple_document_result', $event);
        $result = $event->getResult();

        return $this->wrapDocuments($result);
    }

  /**
   * {@inheritdoc}
   */
    public function findBy(array $criteria, array $sort = null, $limit = null, $skip = null)
    {
        $repository = $this->getRepository();
        $result = $repository->findBy($criteria, $sort, $limit, $skip);
        $event = new MultipleDocumentResultEvent($repository, 'findBy', [$criteria, $sort, $limit, $skip], $result);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.multiple_document_result', $event);
        $result = $event->getResult();

        return $this->wrapDocuments($result);
    }

  /**
   * {@inheritdoc}
   */
    public function findOneBy(array $criteria)
    {
        $repository = $this->getRepository();
        $result = $repository->findOneBy($criteria);
        $event = new SingleDocumentResultEvent($repository, 'findOneBy', [$criteria], $result);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.single_document_result', $event);
        $result = $event->getResult();

        return $this->wrapDocument($result);
    }

  /**
   * {@inheritdoc}
   */
    public function matching(Criteria $criteria)
    {
        $repository = $this->getRepository();
        $result = $repository->matching($criteria);
        $event = new LazyDocumentCollectionResultEvent($repository, 'matching', [$criteria], $result);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.lazy_document_collection_result', $event);
        $result = $event->getResult();

        return $result;
    }

  /**
   * {@inheritdoc}
   */
    public function create()
    {
        $class = $this->getClassName();

        $event = new BeforeCreateEvent($class);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.before_create', $event);
        if ($event->getAbort()) {
            return null;
        }

        $params = func_get_args();
        if ($params) {
            $reflector = new \ReflectionClass($class);
            $document = $reflector->newInstanceArgs($params);
        } else {
            $document = new $class();
        }

        $documentDecorator = $this->wrapDocument($document);

        $this->setAuthor($documentDecorator);

        return $documentDecorator;
    }

  /**
   * {@inheritdoc}
   */
    public function wrapDocuments($documents)
    {
        if (!is_array($documents)) {
            return $this->wrapDocument($documents);
        }

        foreach ($documents as $i => $document) {
            $documents[$i] = $this->wrapDocument($document);
        }

        return $documents;
    }

  /**
   * {@inheritdoc}
   */
    public function wrapDocument($document)
    {
        if (!is_object($document) || get_class($document) !== $this->getClassName()) {
            return $document;
        }

        return $this->documentDecoratorFactory->getDocumentDecorator($this->getDocumentManager(), $this->getDispatcher(), $document);
    }

  /**
   * Catch-all for method calls on the repository
   *
   * Traps all method calls on the repository and fires the event 'logauth_doctrine_mongo.event.repository_decorator.unknown_result' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\RepositoryDecoratorEvents\UnknownResultEvent, allowing tampering with the result before returning it to the caller.
   *
   * @param string $method    The method used for the call
   * @param array  $arguments The arguments used for the call
   *
   * @return mixed
   */
    public function __call($method, array $arguments)
    {
        $repository = $this->getRepository();
        $result = call_user_func_array([$repository, $method], $arguments);
        $event = new UnknownResultEvent($repository, $method, $arguments, $result);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.repository_decorator.unknown_result', $event);
        $result = $event->getResult();

        return $this->wrapDocuments($result);
    }

    protected function getDispatcher()
    {
        return $this->dispatcher;
    }

    protected function setAuthor(DocumentDecoratorInterface $documentDecorator)
    {
        $document = $documentDecorator->getDocument();
        if(!($document instanceof ModelInterface)) return $documentDecorator;

        $author = $this->helper->getCurrentUser();
        if(!($author instanceof UserInterface)) return $documentDecorator;

        $document->setAuthor($author);
    }
}
