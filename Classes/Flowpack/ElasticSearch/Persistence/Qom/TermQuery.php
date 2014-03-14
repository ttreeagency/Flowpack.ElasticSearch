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
 * Performs a term query
 *
 * @see http://www.elasticsearch.org/guide/reference/query-dsl/term-query/
 * @api
 */
class TermQuery extends Constraint {

	/**
	 * @var Constraint
	 */
	protected $constraint;

	/**
	 * @var float
	 */
	protected $boost;

	/**
	 *
	 * @param Constraint $constraint
	 */
	public function __construct(Constraint $constraint) {
		$this->constraint = $constraint;
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
	 * @param float $boost
	 * @return TermQuery
	 */
	public function setBoost($boost) {
		$this->boost = $boost;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getBoost() {
		return $this->boost;
	}

}