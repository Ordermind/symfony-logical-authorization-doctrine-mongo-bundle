<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeMethodCallEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeSaveEvent;
use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeDeleteEvent;

/**
 * {@inheritdoc}
 */
class DocumentDecorator implements DocumentDecoratorInterface
{

  /**
   * @var Doctrine\Common\Persistence\ObjectManager
   */
    protected $dm;

  /**
   * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
    protected $dispatcher;

  /**
   * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface
   */
    protected $laModel;

  /**
   * @var object
   */
    protected $document;

  /**
   * @internal
   *
   * @param Doctrine\Common\Persistence\ObjectManager                  $dm         The document manager to use in this decorator
   * @param Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher to use in this decorator
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface $laModel LogicalAuthorizationModel service
   * @param object                                                      $document      The document to wrap in this decorator
   */
    public function __construct(ObjectManager $dm, EventDispatcherInterface $dispatcher, LogicalAuthorizationModelInterface $laModel, $document)
    {
        $this->dm = $dm;
        $this->dispatcher = $dispatcher;
        $this->laModel = $laModel;
        $this->document = $document;
    }

  /**
   * {@inheritdoc}
   */
    public function getModel()
    {
        return $this->getDocument();
    }

  /**
   * {@inheritdoc}
   */
    public function getDocument()
    {
        return $this->document;
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
    public function getDocumentManager(): ObjectManager
    {
        return $this->dm;
    }

  /**
   * {@inheritdoc}
   */
    public function getAvailableActions($user = null, array $document_actions = ['create', 'read', 'update', 'delete'], array $field_actions = ['get', 'set']): array
    {
        return $this->laModel->getAvailableActions($this->getDocument(), $document_actions, $field_actions, $user);
    }

  /**
   * {@inheritdoc}
   */
    public function isNew(): bool
    {
        $dm = $this->getDocumentManager();
        $document = $this->getDocument();

        return !$dm->contains($document);
    }

  /**
   * {@inheritdoc}
   */
    public function save(bool $andFlush = true)
    {
        $document = $this->getDocument();
        $event = new BeforeSaveEvent($document, $this->isNew());
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.document_decorator.before_save', $event);
        if ($event->getAbort()) {
            return false;
        }

        $dm = $this->getDocumentManager();
        $dm->persist($document);
        if ($andFlush) {
            $dm->flush();
        }

        return $this;
    }

  /**
   * {@inheritdoc}
   */
    public function delete(bool $andFlush = true)
    {
        $document = $this->getDocument();
        $event = new BeforeDeleteEvent($document, $this->isNew());
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.document_decorator.before_delete', $event);
        if ($event->getAbort()) {
            return false;
        }

        $dm = $this->getDocumentManager();
        $dm->remove($document);
        if ($andFlush) {
            $dm->flush();
        }

        return $this;
    }

  /**
   * Catch-all for method calls on the document
   *
   * Traps all method calls on the document and fires the event 'logauth_doctrine_mongo.event.document_decorator.before_method_call' passing Ordermind\LogicalAuthorizationDoctrineMongoBundle\Event\DocumentDecoratorEvents\BeforeMethodCallEvent.
   * If the abort flag in the event is then found to be TRUE the call is never transmitted to the document and instead the method returns NULL.
   *
   * @param string $method    The method used for the call
   * @param array  $arguments The arguments used for the call
   *
   * @return mixed|NULL
   */
    public function __call(string $method, array $arguments)
    {
        $dm = $this->getDocumentManager();
        $document = $this->getDocument();
        $metadata = $dm->getClassMetadata(get_class($document));
        $event = new BeforeMethodCallEvent($document, $this->isNew(), $metadata, $method, $arguments);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch('logauth_doctrine_mongo.event.document_decorator.before_method_call', $event);
        if ($event->getAbort()) {
            return null;
        }

        $result = call_user_func_array([$document, $method], $arguments);

        return $result;
    }

    protected function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
