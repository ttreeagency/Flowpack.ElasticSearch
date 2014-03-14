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

use TYPO3\Flow\Persistence\Generic\Qom as FlowQom;

/**
 * Base class for constraints in the QOM.
 *
 * @api
 */
class Constraint extends FlowQom\Constraint {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @param string $name
	 * @return Constraint
	 */
	public function setName($name) {
		$this->name = trim($name);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
}