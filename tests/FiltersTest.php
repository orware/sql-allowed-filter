<?php
use Orware\Sql\Filters;

class FiltersTest extends \PHPUnit_Framework_TestCase
{
    public function memoryUsage($method, $stage = '')
    {
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, $method . ' ' . $stage . ': ' . memory_get_peak_usage(true) ." (peak)\n");
        fwrite(STDOUT, $method . ' ' . $stage . ': ' . memory_get_usage(true) ." (current)\n");
    }

    public function log($string, $newline = true)
    {
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, $string);

        if ($newline) {
            fwrite(STDOUT, "\n");
        }
    }

	public function testCanCreateEmptyFilters()
    {
		$filters = '';

        $filters = new Filters($filters);

        //$filtersArray = $filters->getFilters();
		//$this->log(print_r($filtersArray, true));

        $this->assertEquals(true, $filters->isEmpty());
    }

    public function testCanCreateFilters()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

		$filtersArray = $filters->getFilters();
        //$this->log(print_r($filters->getFilters(), true));

        $result = false;
        if (!empty($filtersArray["databases"]) &&
        	!empty($filtersArray["queryTypes"]) &&
        	!empty($filtersArray["tables"]) &&
        	!empty($filtersArray["fields"])) {
			$result = true;
        }

        $this->assertEquals(true, $result);
    }

	public function testDatabasesSeparatedUsingPipes()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

		$filtersArray = $filters->getFilters();
        //$this->log(print_r($filters->getFilters(), true));

        $result = false;
        if (array_search('prod8', $filtersArray['databases']) !== false) {
			$result = true;
        }

        $this->assertEquals(true, $result);
    }

    public function testTablesWithExtraSpaces()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

		$filtersArray = $filters->getFilters();
        //$this->log(print_r($filters->getFilters(), true));

        $result = false;
        if (array_search('goremal', $filtersArray['tables']) !== false) {
			$result = true;
        }

        $this->assertEquals(true, $result);
    }

    public function testFieldsWithExtraSpaces()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER , GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

		$filtersArray = $filters->getFilters();
        //$this->log(print_r($filters->getFilters(), true));

        $result = false;
        if (array_search('GOBPTAC_LDAP_USER', $filtersArray['fields']) !== false) {
			$result = true;
        }

        $this->assertEquals(true, $result);
    }
}
