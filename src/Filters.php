<?php
namespace Orware\Sql;

class Filters
{
	protected $filters = array();

	public function __construct($filters = array())
	{
		// Initialize internal $filters array keys:
		$this->filters['databases'] = array();
		$this->filters['queryTypes'] = array();
		$this->filters['tables'] = array();
		$this->filters['fields'] = array();

		if (is_string($filters)) {
			$filters = json_encode($filters);
		}

		$filters = (array) $filters;

		if (isset($filters['databases'])) {
			$filter = $filters['databases'];
			$this->setFilter('databases', $filter);
		}

		if (isset($filters['queryTypes'])) {
			$filter = $filters['queryTypes'];
			$this->setFilter('queryTypes', $filter);
		}

		if (isset($filters['tables'])) {
			$filter = $filters['tables'];
			$this->setFilter('tables', $filter);
		}

		if (isset($filters['fields'])) {
			$filter = $filters['fields'];
			$this->setFilter('fields', $filter);
		}
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function getAllowedDatabases()
	{
		return $this->filters['databases'];
	}

	public function getAllowedQueryTypes()
	{
		return $this->filters['queryTypes'];
	}

	public function getDisallowedQueryTypes()
	{
		$allQueryTypes = [
			"SELECT" => true,
			"INSERT" => true,
			"UPDATE" => true,
			"DELETE" => true
		];

		return array_diff_key($allQueryTypes, $this->getAllowedQueryTypes());
	}

	public function getAllowedTables()
	{
		return $this->filters['tables'];
	}

	public function getAllowedFields()
	{
		return $this->filters['fields'];
	}

	public function isEmpty()
	{
		return (empty($this->filters['databases']) &&
				empty($this->filters['queryTypes']) &&
				empty($this->filters['tables']) &&
				empty($this->filters['fields']));
	}

	protected function setFilter($fieldName, $filter)
	{
		if (!empty($filter)) {
			if (is_string($filter)) {
				$filter = explode($this->getDelimiter($filter), $filter);
				$tmp = (array) $filter;
			} elseif (is_array($filter)) {
				$tmp = $filter;
			} elseif (is_object($filter)) {
				$tmp = (array) $filter;
			}
		}

		$func = function($value) {
			$value = trim($value);
			$value = strtoupper($value);
		    return $value;
		};

		$tmp = array_map($func, $tmp);

		$inverted = array();
		foreach($tmp as $key) {
			$inverted[$key] = true;
		}

		$this->filters[$fieldName] = $inverted;
	}

	protected function getDelimiter($string)
	{
		if (strpos($string, ',') !== false) {
			return ',';
		}

		if (strpos($string, ';') !== false) {
			return ';';
		}

		if (strpos($string, '|') !== false) {
			return '|';
		}

		// Default
		return ',';
	}
}
