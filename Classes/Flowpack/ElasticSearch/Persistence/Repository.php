<?php
namespace Flowpack\ElasticSearch\Persistence;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Flowpack\ElasticSearch\Annotations\Indexable;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory;
use Flowpack\ElasticSearch\Domain\Model\Client;
use Flowpack\ElasticSearch\Domain\Model\GenericType;
use Flowpack\ElasticSearch\Domain\Model\Index;
use Flowpack\ElasticSearch\Exception;
use Flowpack\ElasticSearch\Indexer\Object\Signal\SignalEmitter;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Persistence\QueryResultInterface;
use TYPO3\Flow\Persistence\RepositoryInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * The ElasticSearch Repository
 *
 * @api
 */
abstract class Repository implements RepositoryInterface {

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var SignalEmitter
	 */
	protected $signalEmitter;

	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var string
	 */
	protected $entityClassName;

	/**
	 * @var string
	 */
	protected $indexName;

	/**
	 * @var string
	 */
	protected $documentTypeName;

	/**
	 * @var array
	 */
	protected $defaultOrderings = array();

	/**
	 * @Flow\Inject
	 * @var ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @var Index
	 */
	protected $index;

	/**
	 * Initialize Object
	 * @throws Exception
	 */
	public function initializeObject() {
		if (static::ENTITY_CLASSNAME === NULL) {
			$this->entityClassName = preg_replace(array('/\\\Repository\\\/', '/Repository$/'), array('\\Model\\', ''), get_class($this));
		} else {
			$this->entityClassName = static::ENTITY_CLASSNAME;
		}
		/** @var Indexable $indexableAnnotation */
		$indexableAnnotation = $this->reflectionService->getClassAnnotation($this->entityClassName, 'Flowpack\ElasticSearch\Annotations\Indexable');
		if ($indexableAnnotation === NULL) {
			throw new Exception(sprintf('The current entity (%s) class has no Indexable annotations', $this->getEntityClassName()), 1366730858);
		}
		$this->client = $this->clientFactory->create();
		$this->index = $this->client->findIndex($indexableAnnotation->indexName ?: $this->getIndexName());
		$this->documentTypeName = $indexableAnnotation->typeName;
	}

	/**
	 * Initializes a new Repository.
	 */
	public function __construct() {
		if (static::ENTITY_CLASSNAME === NULL) {
			$this->entityClassName = preg_replace(array('/\\\Repository\\\/', '/Repository$/'), array('\\Model\\', ''), get_class($this));
		} else {
			$this->entityClassName = static::ENTITY_CLASSNAME;
		}
	}

	/**
	 * Returns the classname of the entities this repository is managing.
	 *
	 * Note that anything that is an "instanceof" this class is accepted
	 * by the repository.
	 *
	 * @return string
	 * @api
	 */
	public function getEntityClassName() {
		return $this->entityClassName;
	}

	/**
	 * Returns the elastic search index name
	 *
	 * @return string
	 */
	public function getIndexName() {
		return str_replace('\\', '_', strtolower($this->entityClassName));
	}

	/**
	 * Returns the document type name
	 *
	 * @return string
	 */
	public function getDocumentTypeName() {
		return $this->documentTypeName;
	}

	/**
	 * Adds an object to this repository.
	 *
	 * @param object $object The object to add
	 * @return void
	 * @throws IllegalObjectTypeException
	 * @throws \Exception
	 * @api
	 */
	public function add($object) {
		$this->signalEmitter->emitObjectPersisted($object);
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 * @return void
	 * @throws IllegalObjectTypeException
	 * @api
	 */
	public function remove($object) {
		$this->signalEmitter->emitObjectRemoved($object);
	}

	/**
	 * Returns all objects of this repository
	 *
	 * @return QueryResultInterface The query result
	 * @api
	 * @see \TYPO3\Flow\Persistence\QueryInterface::execute()
	 */
	public function findAll() {
		return $this->createQuery()->execute();
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param mixed $identifier The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByIdentifier($identifier) {
		return $this->persistenceManager->getObjectByIdentifier($identifier, $this->entityClassName);
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return Query
	 * @api
	 */
	public function createQuery() {
		return $this->index->createQuery($this->documentTypeName, $this->entityClassName);
	}

	/**
	 * Counts all objects of this repository
	 *
	 * @return integer
	 * @api
	 */
	public function countAll() {
		return $this->index->findType($this->documentTypeName)->count();
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 * @todo use delete by query API
	 */
	public function removeAll() {
		foreach ($this->findAll() AS $object) {
			$this->remove($object);
		}
	}

	/**
	 * Sets the property names to order results by. Expected like this:
	 * array(
	 *  'foo' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
	 *  'bar' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $defaultOrderings The property names to order by by default
	 * @return void
	 * @api
	 * @todo not implemented currently
	 */
	public function setDefaultOrderings(array $defaultOrderings) {
		$this->defaultOrderings = $defaultOrderings;
	}

	/**
	 * Schedules a modified object for persistence.
	 *
	 * @param object $object The modified object
	 * @throws IllegalObjectTypeException
	 * @throws \Exception
	 * @api
	 */
	public function update($object) {
		$this->signalEmitter->emitObjectUpdated($object);
	}

	/**
	 * Magic call method for repository methods.
	 *
	 * Provides three methods
	 *  - findBy<PropertyName>($value, $caseSensitive = TRUE)
	 *  - findOneBy<PropertyName>($value, $caseSensitive = TRUE)
	 *  - countBy<PropertyName>($value, $caseSensitive = TRUE)
	 *
	 * @param string $method Name of the method
	 * @param array $arguments The arguments
	 * @return mixed The result of the repository method
	 * @api
	 */
	public function __call($method, $arguments) {
		$query = $this->createQuery();
		if (substr($method, 0, 6) === 'findBy' && strlen($method) > 7) {
			$propertyName = lcfirst(substr($method, 6));
			return $query->matching($query->equals($propertyName, $arguments[0]))->execute();
		} elseif (substr($method, 0, 7) === 'countBy' && strlen($method) > 8) {
			$propertyName = lcfirst(substr($method, 7));
			return $query->matching($query->equals($propertyName, $arguments[0]))->count();
		} elseif (substr($method, 0, 9) === 'findOneBy' && strlen($method) > 10) {
			$propertyName = lcfirst(substr($method, 9));
			return $query->matching($query->equals($propertyName, $arguments[0]))->execute()->getFirst();
		}

		trigger_error('Call to undefined method ' . get_class($this) . '::' . $method, E_USER_ERROR);
	}
}