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
 * Performs a boosting query
 *
 * @see http://www.elasticsearch.org/guide/reference/query-dsl/bool-query/
 * @api
 */
class BoostingQuery extends Constraint {

	/**
	 * @var Constraint
	 */
	protected $positiveConstraint;

	/**
	 * @var Constraint
	 */
	protected $negativeConstraint;

	/**
	 * @var float
	 */
	protected $negativeBoost;

	/**
	 * @param Constraint $positiveConstraint
	 * @param Constraint $negativeConstraint
	 * @param float $negativeBoost
	 */
	public function __construct(Constraint $positiveConstraint = NULL, Constraint $negativeConstraint = NULL, $negativeBoost = 0.2) {
		$this->positiveConstraint = $positiveConstraint;
		$this->negativeConstraint = $negativeConstraint;
		$this->negativeBoost = $negativeBoost;
	}

	/**
	 * @param float $negativeBoost
	 * @return BoostingQuery
	 */
	public function negativeBoost($negativeBoost) {
		$this->negativeBoost = $negativeBoost;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getNegativeBoost() {
		return $this->negativeBoost;
	}

	/**
	 * @param Constraint $negativeConstraint
	 * @return BoostingQuery
	 */
	public function negative($negativeConstraint) {
		$this->negativeConstraint = $negativeConstraint;

		return $this;
	}

	/**
	 * @return Constraint
	 */
	public function getNegativeConstraint() {
		return $this->negativeConstraint;
	}

	/**
	 * @param Constraint $positiveConstraint
	 * @return BoostingQuery
	 */
	public function positive($positiveConstraint) {
		$this->positiveConstraint = $positiveConstraint;

		return $this;
	}

	/**
	 * @return Constraint
	 */
	public function getPositiveConstraint() {
		return $this->positiveConstraint;
	}

}