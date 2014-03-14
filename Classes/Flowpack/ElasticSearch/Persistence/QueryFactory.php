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
use Flowpack\ElasticSearch\Domain\Model\Index;
use TYPO3\Flow\Annotations as Flow;

/**
 * A query factory for ElasticSearch queries using an index
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @Flow\Scope("singleton")
 */
class QueryFactory {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject(lazy=FALSE)
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @param Index $index
	 * @param string $documentTypeName
	 * @param string $entityClassName Entity class name
	 * @return Query
	 */
	public function create(Index $index, $documentTypeName, $entityClassName) {
		$query = new Query($index, $this->reflectionService);
		$query->setDocumentTypeName($documentTypeName);
		$query->setEntityClassName($entityClassName);

		return $query;
	}

}