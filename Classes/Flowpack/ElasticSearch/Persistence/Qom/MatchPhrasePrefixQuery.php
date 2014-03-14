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
 * Performs a match phrase query
 *
 * @see http://www.elasticsearch.org/guide/reference/query-dsl/match-query/
 * @api
 */
class MatchPhrasePrefixQuery extends \Flowpack\ElasticSearch\Persistence\Qom\MatchQuery {

	/**
	 * @var integer
	 */
	protected $maxExpansions;

	/**
	 * @param int $maxExpansions
	 * @return MatchPhrasePrefixQuery
	 */
	public function setMaxExpansions($maxExpansions) {
		$this->maxExpansions = $maxExpansions;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxExpansions() {
		return $this->maxExpansions;
	}

}