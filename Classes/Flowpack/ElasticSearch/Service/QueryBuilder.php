<?php
namespace Flowpack\ElasticSearch\Service;

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
use Flowpack\ElasticSearch\Persistence\Qom\BooleanQuery;
use Flowpack\ElasticSearch\Persistence\Qom\BoostingQuery;
use Flowpack\ElasticSearch\Persistence\Qom\MatchPhrasePrefixQuery;
use Flowpack\ElasticSearch\Persistence\Qom\MatchPhraseQuery;
use Flowpack\ElasticSearch\Persistence\Qom\MatchQuery;
use Flowpack\ElasticSearch\Persistence\Qom\MultiMatchPhrasePrefixQuery;
use Flowpack\ElasticSearch\Persistence\Qom\MultiMatchPhraseQuery;
use Flowpack\ElasticSearch\Persistence\Qom\MultiMatchQuery;
use Flowpack\ElasticSearch\Persistence\Qom\TermQuery;
use Flowpack\ElasticSearch\Persistence\Query;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Generic\Qom\Comparison;
use TYPO3\Flow\Persistence\Generic\Qom\Constraint;
use TYPO3\Flow\Persistence\Generic\Qom\PropertyValue;
use TYPO3\Flow\Persistence\QueryInterface;

/**
 * The ElasticSearch Query Builder
 *
 * @Flow\Scope("singleton")
 * @api
 */
class QueryBuilder  {

	/**
	 * @param Query $query
	 * @return array
	 */
	public function buildParameters(Query $query) {
		$elasticSearchQuery = array();
		if ($query->getOrderings() !== array()) {
			$elasticSearchQuery['sort'] = array();
			foreach ($query->getOrderings() as $property => $direction) {
				if ($direction === QueryInterface::ORDER_ASCENDING) {
					$elasticSearchQuery['sort'][] = array($property => array('order' => 'asc'));
				} elseif ($direction === QueryInterface::ORDER_DESCENDING) {
					$elasticSearchQuery['sort'][] = array($property => array('order' => 'desc'));
				}
			}
		}

		if ($query->getLimit() > 0) {
			$elasticSearchQuery['size'] = $query->getLimit();
		}
		if ($query->getOffset() > 0) {
			$elasticSearchQuery['from'] = $query->getOffset();
		}

		if ($query->getConstraint() !== NULL) {
			$elasticSearchQuery['query'] = array(
				$this->buildStatementForConstraint($query->getConstraint())
			);
		} else {
			$elasticSearchQuery['query'] = array(
				'match_all' => array()
			);
		}

		if ($query->getFilter() !== NULL) {
			$elasticSearchQuery['filter'] = array(
				$this->buildStatementForConstraint($query->getFilter())
			);
		}

		\TYPO3\Flow\var_dump(json_encode($elasticSearchQuery));
		

		return $elasticSearchQuery;
	}

	/**
	 * @param mixed $operand The operand as string or object
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function buildKeyForOperand($operand) {
		if (is_string($operand)) {
			return $operand;
		} else {
			throw new \InvalidArgumentException('Non-string operand value of type ' . get_class($operand) . ' is not supported by ElasticSearch QueryIndex', 1299689062);
		}
	}

	/**
	 * @param PropertyValue $operand
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function buildNameForOperand(PropertyValue $operand) {
		if ($operand instanceof PropertyValue) {
			return $operand->getPropertyName();
		} else {
			throw new \InvalidArgumentException('Operand ' . get_class($operand) . ' has to be of type PropertyValue.', 1299690265);
		}
	}

	/**
	 * @param Comparison $constraint
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function buildStatementForComparison(Comparison $constraint) {
		if ($constraint->getOperator() === QueryInterface::OPERATOR_LIKE ||
			$constraint->getOperator() === QueryInterface::OPERATOR_EQUAL_TO) {
			$operandValue = $constraint->getOperand2();
			if (strpos($operandValue, ' ') !== FALSE) {
				$operandValue = $this->phrase($operandValue);
			} else {
				$allowWildcard = ($constraint->getOperator() === QueryInterface::OPERATOR_LIKE);
				$operandValue = $this->escape($operandValue, $allowWildcard);
			}
			$operandName = $this->buildNameForOperand($constraint->getOperand1());
			$statement = array($operandName => $operandValue);
		} else {
			throw new \InvalidArgumentException('Comparison operator ' . get_class($constraint->getOperator()) . ' is not supported by ElasticSearch QueryIndex', 1300895208);
		}

		return $statement;
	}

	/**
	 * @param MatchQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForMatchQuery(MatchQuery $constraint) {
		if (QueryInterface::OPERATOR_EQUAL_TO !== $constraint->getConstraint()->getOperator()) {
			throw new Exception('Match query support only equal operator', 1367483885);
		}

		$propertyName = $this->buildNameForOperand($constraint->getConstraint()->getOperand1());

		return array(
			'match' => array(
				$propertyName => array(
					'_name' => (string)$constraint->getName(),
					'query' => (string)$constraint->getConstraint()->getOperand2(),
					'operator' => $constraint->getOperator(),
					'analyser' => $constraint->getAnalyzer(),
					'zero_terms_query' => $constraint->getZeroTermsQuery(),
					'cutoff_frequency' => $constraint->getCutoffFrequency()
				)
			)
		);
	}

	/**
	 * @param MatchPhraseQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForMatchPhraseQuery(MatchPhraseQuery $constraint) {
		$propertyName = $this->buildNameForOperand($constraint->getConstraint()->getOperand1());
		$statement = $this->buildStatementForMatchQuery($constraint);
		$statement['match'][$propertyName]['type'] = 'phrase';

		return $statement;
	}

	/**
	 * @param MatchPhrasePrefixQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForMatchPhrasePrefixQuery(MatchPhrasePrefixQuery $constraint) {
		$propertyName = $this->buildNameForOperand($constraint->getConstraint()->getOperand1());
		$statement = $this->buildStatementForMatchQuery($constraint);
		$statement['match'][$propertyName]['type'] = 'phrase_prefix';

		return $statement;
	}

	/**
	 * @param TermQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForTermQuery(TermQuery $constraint) {
		if ($constraint->getConstraint()->getOperator() !== QueryInterface::OPERATOR_EQUAL_TO) {
			throw new Exception('Term query support only equal operator', 1367483890);
		}

		$propertyName = $this->buildNameForOperand($constraint->getConstraint()->getOperand1());

		if ($constraint->getBoost()) {
			$statement = array(
				'term' => array(
					$propertyName => array(
						'value' => $constraint->getConstraint()->getOperand2(),
						'boost' => $constraint->getBoost()
					)
				)
			);
		} else {
			$statement = array(
				'term' => array(
					$propertyName => $constraint->getConstraint()->getOperand2()
				)
			);
		}

		return $statement;
	}

	/**
	 * @param MultiMatchQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForMultiMatchQuery(MultiMatchQuery $constraint) {
		return array(
			'multi_match' => array(
				'_name' => (string)$constraint->getName(),
				'query' => (string)$constraint->getQuery(),
				'fields' => $constraint->getFields(),
				'use_dis_max' => $constraint->getUseDisMax(),
				'tie_breaker' => $constraint->getTieBreacker(),
				'operator' => $constraint->getOperator(),
				'analyser' => $constraint->getAnalyzer()
			)
		);
	}

	/**
	 * @param MultiMatchPhraseQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForMultiMatchPhraseQuery(MultiMatchPhraseQuery $constraint) {
		$statement = $this->buildStatementForMultiMatchQuery($constraint);
		$statement['multi_match']['type'] = 'phrase';

		return $statement;
	}

	/**
	 * @param MultiMatchPhrasePrefixQuery $constraint
	 * @return array
	 * @throws Exception
	 */
	protected function buildStatementForMultiMatchPhrasePrefixQuery(MultiMatchPhrasePrefixQuery $constraint) {
		$statement = $this->buildStatementForMultiMatchQuery($constraint);
		$statement['multi_match']['type'] = 'phrase_prefix';

		return $statement;
	}

	/**
	 * @param BoostingQuery $constraint
	 * @return array
	 */
	protected function buildStatementForBoostingQuery(BoostingQuery $constraint) {
		return array(
			'boosting' => array(
				'_name' => (string)$constraint->getName(),
				'positive' => $this->buildStatementForConstraint($constraint->getPositiveConstraint()),
				'negative' => $this->buildStatementForConstraint($constraint->getNegativeConstraint()),
				'negative_boost' => $constraint->getNegativeBoost()
			)
		);
	}

	/**
	 * @param BooleanQuery $constraint
	 * @return array
	 */
	protected function buildStatementForBooleanQuery(BooleanQuery $constraint) {
		$statement = array(
			'bool' => array(
				'_name' => (string)$constraint->getName(),
				'must' => $this->buildStatementForConstraintArray($constraint->getMustConstraint()),
				'should' => $this->buildStatementForConstraintArray($constraint->getShouldConstraint()),
				'must_not' => $this->buildStatementForConstraintArray($constraint->getMustNotConstraint()),
				'disable_coord' => $constraint->getDisableCoord(),
				'minimum_should_match' => $constraint->getMinimumShouldMatch()
			)
		);

		return $statement;
	}

	/**
	 * @param array $constraints
	 * @return array
	 */
	protected function buildStatementForConstraintArray(array $constraints = array()) {
		$statement = array();
		foreach ($constraints as $constraint) {
			if ($constraintStatement = $this->buildStatementForConstraint($constraint)) {
				$statement[] = $constraintStatement;
			}
		}

		return count($statement) ? $statement : NULL;
	}

	/**
	 * @param Constraint $constraint
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function buildStatementForConstraint(Constraint $constraint = NULL) {
		if ($constraint === NULL) {
			return;
		}

		$statement = array();
		switch (get_class($constraint)) {
			case 'TYPO3\Flow\Persistence\Generic\Qom\Comparison':
				/** @var Comparison $constraint */
				$statement = $this->buildStatementForComparison($constraint);
				break;
			case 'TYPO3\Flow\Persistence\Generic\Qom\LogicalAnd':
				/** @var \TYPO3\Flow\Persistence\Generic\Qom\LogicalAnd $constraint */
				$statement['and'] = array($this->buildStatementForConstraint($constraint->getConstraint1()), $this->buildStatementForConstraint($constraint->getConstraint2()));
				break;
			case 'TYPO3\Flow\Persistence\Generic\Qom\LogicalOr':
				/** @var \TYPO3\Flow\Persistence\Generic\Qom\LogicalOr $constraint */
				$statement['or'] = array($this->buildStatementForConstraint($constraint->getConstraint1()), $this->buildStatementForConstraint($constraint->getConstraint2()));
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\MatchQuery':
				/** @var MatchQuery $constraint */
				$statement = $this->buildStatementForMatchQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\MatchPhraseQuery':
				/** @var MatchPhraseQuery $constraint */
				$statement = $this->buildStatementForMatchPhraseQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\MatchPhrasePrefixQuery':
				/** @var MatchPhrasePrefixQuery $constraint */
				$statement = $this->buildStatementForMatchPhrasePrefixQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\MultiMatchQuery':
				/** @var MultiMatchQuery $constraint */
				$statement = $this->buildStatementForMultiMatchQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\MultiMatchPhraseQuery':
				/** @var MultiMatchPhraseQuery $constraint */
				$statement = $this->buildStatementForMultiMatchPhraseQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\MultiMatchPhrasePrefixQuery':
				/** @var MultiMatchPhrasePrefixQuery $constraint */
				$statement = $this->buildStatementForMultiMatchPhrasePrefixQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\TermQuery':
				/** @var TermQuery $constraint */
				$statement = $this->buildStatementForTermQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\BooleanQuery':
				/** @var BooleanQuery $constraint */
				$statement = $this->buildStatementForBooleanQuery($constraint);
				break;
			case 'Flowpack\ElasticSearch\Persistence\Qom\BoostingQuery':
				/** @var BoostingQuery $constraint */
				$statement = $this->buildStatementForBoostingQuery($constraint);
				break;
			default:
				throw new \InvalidArgumentException('Constraint ' . get_class($constraint) . ' is not supported by ElasticSearch QueryIndex', 1299689061);
		}

		return $statement;
	}

	/**
	 * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
	 *
	 * @param string $value
	 * @param bool $allowWildcard
	 * @return string
	 * @see http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
	 */
	protected function escape($value, $allowWildcard = FALSE) {
		return preg_replace('/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~' . ($allowWildcard ? '' : '|\*|\?') . '|:|\\\)/', '\\\$1', $value);
	}

	/**
	 * Escape a value meant to be contained in a phrase for special query characters
	 *
	 * @param string $value
	 * @return string
	 */
	protected function escapePhrase($value) {
		return preg_replace('/("|\\\)/', '\\\$1', $value);
	}

	/**
	 * Convenience function for creating phrase syntax from a value
	 *
	 * @param string $value
	 * @return string
	 */
	protected function phrase($value) {
		return '"' . $this->escapePhrase($value) . '"';
	}

}