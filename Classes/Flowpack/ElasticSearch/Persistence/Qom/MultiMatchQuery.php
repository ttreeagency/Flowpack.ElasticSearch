<?php
namespace Flowpack\ElasticSearch\Persistence\Qom;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Performs a multi match query
 *
 * @see http://www.elasticsearch.org/guide/reference/query-dsl/multi-match-query/
 * @api
 */
class MultiMatchQuery extends MatchQuery {

	/**
	 * @var string
	 */
	protected $query;

	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * @var bool
	 */
	protected $useDisMax = TRUE;

	/**
	 * @var float
	 */
	protected $tieBreacker = 0.0;

	/**
	 * @param array $fields
	 * @param string $query
	 * @param string $operator
	 */
	public function __construct(array $fields = array(), $query = NULL, $operator = 'or') {
		$this->fields = $fields;
		$this->query = (string)$query;
		$this->operator = $operator;
	}

	/**
	 * @param array $fields
	 * @return MultiMatchQuery
	 */
	public function setFields(array $fields) {
		$this->fields = $fields;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param string $query
	 * @return MultiMatchQuery
	 */
	public function setQuery($query) {
		$this->query = $query;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @param float $tieBreacker
	 * @return MultiMatchQuery
	 */
	public function setTieBreacker($tieBreacker) {
		$this->tieBreacker = $tieBreacker;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getTieBreacker() {
		return $this->tieBreacker;
	}

	/**
	 * @param boolean $useDisMax
	 * @return MultiMatchQuery
	 */
	public function setUseDisMax($useDisMax) {
		$this->useDisMax = $useDisMax;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getUseDisMax() {
		return $this->useDisMax;
	}
}