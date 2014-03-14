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

use Flowpack\ElasticSearch\Exception;

/**
 * Performs a term query
 *
 * @see http://www.elasticsearch.org/guide/reference/query-dsl/bool-query/
 * @api
 */
class BooleanQuery extends Constraint {

	/**
	 * @var array
	 */
	protected $mustConstraint;

	/**
	 * @var array
	 */
	protected $shouldConstraint;

	/**
	 * @var array
	 */
	protected $mustNotConstraint;

	/**
	 * @var bool
	 */
	protected $disableCoord = FALSE;

	/**
	 * @var integer
	 */
	protected $minimumShouldMatch;

	/**
	 *
	 * @param Constraint|array $mustConstraint
	 * @param Constraint|array $shouldConstraint
	 * @param Constraint|array $mustNotConstraint
	 * @throws Exception
	 */
	public function __construct($mustConstraint = NULL, $shouldConstraint = NULL, $mustNotConstraint = NULL) {
		if ($mustConstraint !== NULL && !($mustConstraint instanceof Constraint || is_array($mustConstraint))) {
			throw new Exception('MustConstraint must be an array or a Contraint object', 1394792265);
		}
		$this->mustConstraint = is_array($mustConstraint) ? $mustConstraint : array($mustConstraint);
		if ($shouldConstraint !== NULL && !($shouldConstraint instanceof Constraint || is_array($shouldConstraint))) {
			throw new Exception('MustConstraint must be an array or a Contraint object', 1394792275);
		}
		$this->shouldConstraint = is_array($shouldConstraint) ? $shouldConstraint : array($shouldConstraint);;
		if ($mustNotConstraint !== NULL && !($mustNotConstraint instanceof Constraint || is_array($mustNotConstraint))) {
			throw new Exception('MustConstraint must be an array or a Contraint object', 1394792285);
		}
		$this->mustNotConstraint = is_array($mustNotConstraint) ? $mustNotConstraint : array($mustNotConstraint);;
	}

	/**
	 * @return array
	 */
	public function getMustConstraint() {
		return $this->mustConstraint;
	}

	/**
	 * @param Constraint|array $constraint
	 * @return BooleanQuery
	 */
	public function must($constraint) {
		$this->mustConstraint = is_array($constraint) ? $constraint : array($constraint);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getShouldConstraint() {
		return $this->shouldConstraint;
	}

	/**
	 * @param Constraint|array $constraint
	 * @return BooleanQuery
	 */
	public function should($constraint) {
		$this->shouldConstraint = is_array($constraint) ? $constraint : array($constraint);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMustNotConstraint() {
		return $this->mustNotConstraint;
	}

	/**
	 * @param Constraint|array $constraint
	 * @return BooleanQuery
	 */
	public function mustNot($constraint) {
		$this->mustNotConstraint = is_array($constraint) ? $constraint : array($constraint);

		return $this;
	}

	/**
	 * @param boolean $disableCoord
	 * @return BooleanQuery
	 */
	public function setDisableCoord($disableCoord) {
		$this->disableCoord = $disableCoord;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getDisableCoord() {
		return $this->disableCoord;
	}

	/**
	 * @param integer $minimumShouldMatch
	 * @return BooleanQuery
	 */
	public function setMinimumShouldMatch($minimumShouldMatch) {
		$this->minimumShouldMatch = (integer)$minimumShouldMatch;

		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMinimumShouldMatch() {
		return (integer)$this->minimumShouldMatch;
	}
}