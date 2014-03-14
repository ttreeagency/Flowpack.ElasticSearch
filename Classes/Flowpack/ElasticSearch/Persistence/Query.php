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

use Doctrine\ORM\Mapping as ORM;
use Flowpack\ElasticSearch\Domain\Model\AbstractType;
use Flowpack\ElasticSearch\Domain\Model\Index;
use Flowpack\ElasticSearch\Service\QueryBuilder;
use Flowpack\ElasticSearch\Transfer\Response;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Generic\Qom\Comparison;
use TYPO3\Flow\Persistence\Generic\Qom\Constraint;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Persistence\QueryResultInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * A special ElasticSearch query
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Query extends \TYPO3\Flow\Persistence\Generic\Query {

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var Index
	 */
	protected $index;

	/**
	 * Elastic Search document type
	 *
	 * @var string
	 */
	protected $documentTypeName;

	/**
	 * @var string
	 */
	protected $entityClassName;

	/**
	 * @var Constraint
	 */
	protected $filter;

	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @Flow\Inject
	 * @var QueryBuilder
	 */
	protected $queryBuilder;

	/**
	 * @param Index $index
	 * @param ReflectionService $reflectionService
	 */
	public function __construct(Index $index, ReflectionService $reflectionService) {
		$this->setIndex($index);
		parent::__construct($index->getName(), $reflectionService);
	}

	/**
	 * @return Index
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * @param Index $index
	 * @return void
	 */
	public function setIndex(Index $index) {
		$this->index = $index;
	}

	/**
	 * @return string
	 */
	public function getDocumentTypeName() {
		return $this->documentTypeName;
	}

	/**
	 * @param string $documentTypeName
	 */
	public function setDocumentTypeName($documentTypeName) {
		$this->documentTypeName = $documentTypeName;
	}

	/**
	 * @return string
	 */
	public function getEntityClassName() {
		return $this->entityClassName;
	}

	/**
	 * @param string $entityClassName
	 */
	public function setEntityClassName($entityClassName) {
		$this->entityClassName = $entityClassName;
	}

	/**
	 * @return Comparison
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @return QueryResult
	 */
	public function execute() {
		return new QueryResult($this);
	}

	/**
	 * @return array
	 */
	public function getRawResult() {
		$this->response = $this->findDocumentType()->search($this->queryBuilder->buildParameters($this));
		return $this->response->getTreatedContent();
	}

	/**
	 * @return array
	 */
	public function getResult() {
		$result = $this->getRawResult();

		$query = $this->persistenceManager->createQueryForType($this->entityClassName);

		$documentIdentifiers = array();
		foreach ($result['hits']['hits'] as $document) {
			$documentIdentifiers[] = $document['_id'];
		}

		$query->matching($query->in('Persistence_Object_Identifier', $documentIdentifiers));

		return $query->execute()->toArray();
	}

	/**
	 * @param Constraint $filter
	 * @return \Flowpack\ElasticSearch\Persistence\Query
	 */
	public function filter(Constraint $filter) {
		$this->filter = $filter;

		return $this;
	}

	/**
	 * @return int
	 */
	public function count() {
		$result = $this->getRawResult();

		return $result['hits']['total'];
	}

	/**
	 * @param Constraint $constraint
	 * @return Qom\MatchQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createMatchQuery(Constraint $constraint) {
		return new Qom\MatchQuery($constraint);
	}

	/**
	 * @param Constraint $constraint
	 * @return Qom\MatchPhraseQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createMatchPhraseQuery(Constraint $constraint) {
		return new Qom\MatchPhraseQuery($constraint);
	}

	/**
	 * @param Constraint $constraint
	 * @return Qom\MatchPhrasePrefixQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createMatchPhrasePrefixQuery(Constraint $constraint) {
		return new Qom\MatchPhrasePrefixQuery($constraint);
	}

	/**
	 * @param array $fields
	 * @param null $query
	 * @return Qom\MultiMatchQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createMultiMatchQuery(array $fields = array(), $query = NULL) {
		return new Qom\MultiMatchQuery($fields, $query);
	}

	/**
	 * @param array $fields
	 * @param null $query
	 * @return Qom\MultiMatchPhraseQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createMultiMatchPhraseQuery(array $fields = array(), $query = NULL) {
		return new Qom\MultiMatchPhraseQuery($fields, $query);
	}

	/**
	 * @param array $fields
	 * @param null $query
	 * @return Qom\MultiMatchPhrasePrefixQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createMultiMatchPhrasePrefixQuery(array $fields = array(), $query = NULL) {
		return new Qom\MultiMatchPhrasePrefixQuery($fields, $query);
	}

	/**
	 * @param Constraint $constraint
	 * @return Qom\TermQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createTermQuery(Constraint $constraint) {
		return new Qom\TermQuery($constraint);
	}

	/**
	 * @param Constraint|array $mustConstraint
	 * @param Constraint|array $shouldConstraint
	 * @param Constraint|array $mustNotConstraint
	 * @return Qom\BooleanQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createBooleanQuery($mustConstraint = NULL, $shouldConstraint = NULL, $mustNotConstraint = NULL) {
		return new Qom\BooleanQuery($mustConstraint, $shouldConstraint, $mustNotConstraint);
	}

	/**
	 * @param Constraint $positiveConstraint
	 * @param Constraint $negativeConstraint
	 * @param float $negativeBoost
	 * @return Qom\BoostingQuery
	 * @todo try to override QomFactory to handle this object creation
	 */
	public function createBoostingQuery(Constraint $positiveConstraint = NULL, Constraint $negativeConstraint = NULL, $negativeBoost = 0.2) {
		return new Qom\BoostingQuery($positiveConstraint, $negativeConstraint, $negativeBoost);
	}

	/**
	 * @return AbstractType
	 */
	public function findDocumentType() {
		return $this->index->findType($this->documentTypeName);
	}

	/**
	 * @param array $orderings
	 * @return \Flowpack\ElasticSearch\Persistence\Query
	 */
	public function setOrderings(array $orderings) {
		return parent::setOrderings($orderings);
	}

	/**
	 * @param string $propertyName
	 * @param mixed $operand
	 * @param bool $caseSensitive
	 * @return Constraint
	 */
	public function equals($propertyName, $operand, $caseSensitive = TRUE) {
		return parent::equals($propertyName, $operand, $caseSensitive);
	}

	/**
	 * @param Constraint $constraint
	 * @return \Flowpack\ElasticSearch\Persistence\Query
	 */
	public function matching($constraint) {
		return parent::matching($constraint);
	}
}