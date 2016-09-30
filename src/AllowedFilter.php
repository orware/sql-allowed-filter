<?php
namespace Orware\Sql;

class AllowedFilter
{
	protected $filters = null;
	protected $query = '';
	protected $normalizedQuery = '';

	public function __construct($query = '', Filters $filters = null)
	{
		$isValidQuery = (!empty($query) && is_string($query));
		$queryIsAFilter = ($query instanceof Filters);
		$queryIsNotAFilter = !$queryIsAFilter;
		$filtersIsNotNull = (!is_null($filters));
		$filtersIsNull = !$filtersIsNotNull;

		if ($isValidQuery) {
			$this->setQuery($query);
		}

		if ($queryIsAFilter) {
			$this->setFilters($query);
		}

		if ($filtersIsNotNull) {
			$this->setFilters($filters);
		} elseif ($queryIsNotAFilter && $filtersIsNull) {
			$filters = new Filters();
			$this->setFilters($filters);
		}
	}

	public function setFilters(Filters $filters)
	{
		$this->filters = $filters;
	}

	public function canExecuteQuery()
	{
		$isQueryTypeAllowed = $this->isQueryTypeAllowed();
		$areTablesAllowed = $this->areTablesAllowed();
		$areFieldsAllowed = $this->areFieldsAllowed();

		return $isQueryTypeAllowed && $areTablesAllowed && $areFieldsAllowed;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function setQuery($query)
	{
		if (is_string($query)) {
			$this->query = $query;
			$this->normalizeQuery($query);
		}
	}

	public function normalizeQuery($query)
	{
		$query = trim($query);
		$query = strtoupper($query);

		$query = str_replace("\n", " ", $query);
		$query = str_replace("\r", " ", $query);
		$query = str_replace("\t", " ", $query);

		// http://stackoverflow.com/questions/2901453/sql-standard-to-escape-column-names
		$query = str_replace("`", "", $query); // Used in MySQL Identifiers
		$query = str_replace('"', "", $query); // Used in SQL Identifiers
		$query = str_replace('[', "", $query); // Used in SQL Server
		$query = str_replace(']', "", $query); // Used in SQL Server

		$this->normalizedQuery = $query;
	}

	public function isDatabaseAllowed($databaseName)
	{
		$databaseName = strtoupper($databaseName);

		$allowedDatabases = $this->filters->getAllowedDatabases();

		if (isset($allowedDatabases[$databaseName])) {
			return true;
		}

		return false;
	}

	public function getQueryType()
	{
		$firstSpace = strpos($this->query, ' ');
		$queryType = strtoupper(substr($this->normalizedQuery, 0, $firstSpace));

		if ($queryType === 'WITH') {
			$allowedQueryTypes = array_keys($this->filters->getAllowedQueryTypes());
			$potentialInstancesOfAllowedQueryTypes = array();
			foreach($allowedQueryTypes as $allowedQueryType) {
				$candidates = substr_count($this->normalizedQuery, $allowedQueryType . ' ');
				if ($candidates) {
					$potentialInstancesOfAllowedQueryTypes[$allowedQueryType] = $candidates;
				}
			}

			if (isset($potentialInstancesOfAllowedQueryTypes['DELETE'])) {
				return 'DELETE';
			} elseif (isset($potentialInstancesOfAllowedQueryTypes['UPDATE'])) {
				return 'UPDATE';
			} elseif (isset($potentialInstancesOfAllowedQueryTypes['INSERT'])) {
				return 'INSERT';
			} elseif (isset($potentialInstancesOfAllowedQueryTypes['SELECT'])) {
				return 'SELECT';
			}
		}

		return $queryType;
	}

	public function isQueryTypeAllowed()
	{
		$allowedQueryTypes = $this->filters->getAllowedQueryTypes();

		if (empty($allowedQueryTypes)) {
			// All Query Types Are Allowed By Default:
			return true;
		}

		$disallowedQueryTypes = array_keys($this->filters->getDisallowedQueryTypes());

		$instancesOfDisallowedQueryTypes = 0;
		foreach($disallowedQueryTypes as $disallowedQueryType) {
			$instancesOfDisallowedQueryTypes += substr_count($this->normalizedQuery, $disallowedQueryType . ' ');
		}

		if ($instancesOfDisallowedQueryTypes > 0) {
			return false;
		}

		$queryType = $this->getQueryType();

		return (isset($allowedQueryTypes[$queryType]));
	}

	public function areTablesAllowed()
	{
		$allowedTables = $this->filters->getAllowedTables();
		if (empty($allowedTables)) {
			return true;
		}

		$disallowedTables = $this->getDisallowedTables();

		return empty($disallowedTables);
	}

	public function getDisallowedTables()
	{
		$tables = $this->getTablesFromQuery();
		$allowedTables = array_keys($this->filters->getAllowedTables());

		$disallowedTables = array_diff($tables, $allowedTables);

		return $disallowedTables;
	}

	public function findEndingParentheses($start)
	{
		$query = $this->normalizedQuery;

		do {
			$endParenthesesOccurrence = strpos($query, ')', $start);
			if ($endParenthesesOccurrence !== false) {

				$startParenthesesOccurrence = strpos($query, '(', $start + 1);

				if ($startParenthesesOccurrence < $endParenthesesOccurrence) {
					$start = $endParenthesesOccurrence;
				} elseif($startParenthesesOccurrence > $endParenthesesOccurrence) {
					$start = $endParenthesesOccurrence;
					break;
				}
			}
		} while (strpos($query, ')', $start) !== false);

		return $endParenthesesOccurrence;
	}

	public function getTablesFromQuery()
	{
		$queryType = $this->getQueryType();
		$query = $this->normalizedQuery;
		$potentialTables = array();

		if ($queryType === 'SELECT') {
			$start = 0;
			do {
				$fromOccurrence = strpos($query, 'FROM ', $start);
				if ($fromOccurrence !== false) {
					$fromOccurrence = $fromOccurrence + 4;
					$whereOccurrence = strpos($query, 'WHERE ', $fromOccurrence);
					if ($whereOccurrence === false) {
						$whereOccurrence = strlen($query) - 1;
					}

					$selectOccurrence = strpos($query, 'SELECT ', $fromOccurrence);
					if ($selectOccurrence !== false) {
						if ($selectOccurrence < $whereOccurrence) {
							$whereOccurrence = $selectOccurrence;
						}

						/*$subqueryStartOccurrence = strpos($query, '(', $fromOccurrence);
						if ($subqueryStartOccurrence !== false && $subqueryStartOccurrence < $selectOccurrence) {
							$findEndingParentheses = $this->findEndingParentheses($subqueryStartOccurrence);
							$whereOccurrence = strpos($query, 'WHERE ', $findEndingParentheses);
							if ($whereOccurrence === false) {
								$whereOccurrence = strlen($query) - 1;
							}
						}*/
					}

					$tables = substr($query, $fromOccurrence, ($whereOccurrence - $fromOccurrence));

					$tables = str_replace(array('(', ')'), '', trim($tables));

					if (!empty($tables)) {
						$tablesListWithPotentialAliases = explode(',', $tables);

						foreach($tablesListWithPotentialAliases as $potentialTable) {
							$parts = explode(' ', ltrim($potentialTable));
							$potentialTables[] = trim($parts[0]);
						}
					}

					$start = $whereOccurrence;
				}
			} while (strpos($query, 'FROM ', $start) !== false);

			$start = 0;
			do {
				$fromOccurrence = strpos($query, 'JOIN ', $start);
				if ($fromOccurrence !== false) {
					$fromOccurrence = $fromOccurrence + 4;
					$whereOccurrence = strpos($query, 'ON ', $fromOccurrence);
					if ($whereOccurrence === false) {
						$whereOccurrence = strlen($query) - 1;
					}

					$selectOccurrence = strpos($query, 'SELECT ', $fromOccurrence);
					if ($selectOccurrence !== false) {
						if ($selectOccurrence < $whereOccurrence) {
							$whereOccurrence = $selectOccurrence;
						}
					}

					$tables = substr($query, $fromOccurrence, ($whereOccurrence - $fromOccurrence));

					$tables = str_replace(array('(', ')'), '', trim($tables));

					if (!empty($tables)) {
						$tablesListWithPotentialAliases = explode(',', $tables);

						foreach($tablesListWithPotentialAliases as $potentialTable) {
							$parts = explode(' ', ltrim($potentialTable));
							$potentialTables[] = trim($parts[0]);
						}
					}

					$start = $whereOccurrence;
				}
			} while (strpos($query, 'JOIN ', $start) !== false);
		}

		// @TODO Finish Other Query Types:

		$potentialTables = array_unique($potentialTables);
		return $potentialTables;
	}

	public function areFieldsAllowed()
	{
		return false;
	}
}
