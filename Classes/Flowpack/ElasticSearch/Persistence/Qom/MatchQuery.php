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
 * Performs a match query
 *
 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
 * @api
 * @todo add support for fuzziness, see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/common-options.html#fuzziness
 */
class MatchQuery extends Constraint {

	/**
	 * @var Constraint
	 */
	protected $constraint;

	/**
	 * @var string
	 */
	protected $operator;

	/**
	 * @var string
	 */
	protected $analyzer;

	/**
	 * @var string
	 */
	protected $zeroTermsQuery = 'none';

	/**
	 * @var float
	 */
	protected $cutoffFrequency;

	/**
	 * @param Constraint $constraint
	 * @param string $operator
	 */
	public function __construct(Constraint $constraint, $operator = 'or') {
		$this->constraint = $constraint;
		$this->operator = $operator;
	}

	/**
	 * Gets the constraint.
	 *
	 * @return Constraint the constraint; non-null
	 * @api
	 */
	public function getConstraint() {
		return $this->constraint;
	}

	/**
	 * @param string $analyser
	 * @return MatchQuery
	 */
	public function setAnalyzer($analyser) {
		$this->analyzer = $analyser;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAnalyzer() {
		return $this->analyzer;
	}

	/**
	 * @param string $operator
	 * @return MatchQuery
	 */
	public function setOperator($operator) {
		$this->operator = $operator;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOperator() {
		return $this->operator;
	}

	/**
	 * @param string $zeroTermsQuery
	 * @return MatchQuery
	 */
	public function setZeroTermsQuery($zeroTermsQuery) {
		$this->zeroTermsQuery = $zeroTermsQuery;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getZeroTermsQuery() {
		return $this->zeroTermsQuery;
	}

	/**
	 * @param float $cutoffFrequency
	 * @return MatchQuery
	 */
	public function setCutoffFrequency($cutoffFrequency) {
		$this->cutoffFrequency = $cutoffFrequency;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getCutoffFrequency() {
		return $this->cutoffFrequency;
	}
}