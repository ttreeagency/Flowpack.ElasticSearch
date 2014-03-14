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

use Flowpack\ElasticSearch\Transfer\Response;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryResultInterface;

/**
 * A lazy result list that is returned by Query::execute()
 *
 * @api
 */
class QueryResult implements QueryResultInterface {

	/**
	 * @var array
	 * @Flow\Transient
	 */
	protected $rows;

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * @param Query $query
	 */
	public function __construct(Query $query) {
		$this->query = $query;
	}

	/**
	 * Loads the objects this QueryResult is supposed to hold
	 *
	 * @return void
	 */
	protected function initialize() {
		if (!is_array($this->rows)) {
			$this->rows = $this->query->getResult();
		}
	}

	/**
	 * @return Response
	 */
	public function getResponse() {
		$this->initialize();
		return $this->query->getResponse();
	}

	/**
	 * Returns a clone of the query object
	 *
	 * @return Query
	 * @api
	 */
	public function getQuery() {
		return clone $this->query;
	}

	/**
	 * Returns the first object in the result set
	 *
	 * @return object
	 * @api
	 */
	public function getFirst() {
		if (is_array($this->rows)) {
			$rows = &$this->rows;
		} else {
			$query = clone $this->query;
			$query->setLimit(1);
			$rows = $query->getResult();
		}

		return (isset($rows[0])) ? $rows[0] : NULL;
	}

	/**
	 * Returns the number of objects in the result
	 *
	 * @return integer The number of matching objects
	 * @api
	 */
	public function count() {
		return $this->query->count();
	}

	/**
	 * Returns an array with the objects in the result set
	 *
	 * @return array
	 * @api
	 */
	public function toArray() {
		$this->initialize();
		return $this->rows;
	}

	/**
	 * This method is needed to implement the \ArrayAccess interface,
	 * but it isn't very useful as the offset has to be an integer
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		$this->initialize();
		return isset($this->rows[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		$this->initialize();
		return isset($this->rows[$offset]) ? $this->rows[$offset] : NULL;
	}

	/**
	 * This method has no effect on the persisted objects but only on the result set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->initialize();
		$this->rows[$offset] = $value;
	}

	/**
	 * This method has no effect on the persisted objects but only on the result set
	 *
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->initialize();
		unset($this->rows[$offset]);
	}

	/**
	 * @return mixed
	 */
	public function current() {
		$this->initialize();
		return current($this->rows);
	}

	/**
	 * @return mixed
	 */
	public function key() {
		$this->initialize();
		return key($this->rows);
	}

	/**
	 * @return void
	 */
	public function next() {
		$this->initialize();
		next($this->rows);
	}

	/**
	 * @return void
	 */
	public function rewind() {
		$this->initialize();
		reset($this->rows);
	}

	/**
	 * @return boolean
	 */
	public function valid() {
		$this->initialize();
		return current($this->rows) !== FALSE;
	}

}