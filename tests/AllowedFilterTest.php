<?php
use Orware\Sql\AllowedFilter;
use Orware\Sql\Filters;

class AllowedFilterTest extends \PHPUnit_Framework_TestCase
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

    public function testCanExecuteQuery()
    {

		$filters = new Filters();
        $filter = new AllowedFilter($filters);

        $filter->setQuery("select * from dual");

        $result = $filter->canExecuteQuery();

        $this->log("Is Query Type Allowed: " . (int)$filter->isQueryTypeAllowed());
        $this->log("Are Tables Allowed: " . (int)$filter->areTablesAllowed());
        $this->log("Are Fields Allowed: " . (int)$filter->areFieldsAllowed());

        $this->assertEquals(true, $result);
    }

    public function testCanSetQuery()
    {
		$filters = new Filters();
        $filter = new AllowedFilter($filters);

        $query = "select * from dual";
        $filter->setQuery($query);

        $result = $filter->getQuery();

        $this->assertEquals($query, $result);
    }

	public function testIsDatabaseAllowed()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $this->assertEquals(true, $filter->isDatabaseAllowed('prod8'));
        $this->assertEquals(false, $filter->isDatabaseAllowed('not-here'));
    }

    public function testIsQueryTypeAllowedBasicTrue()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('select * from dual');

        $this->assertEquals(true, $filter->isQueryTypeAllowed(), 'SELECT Query is Allowed');
    }

	public function testIsQueryTypeAllowedBasicFalse()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "UPDATE",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('select * from dual');

        $this->assertEquals(false, $filter->isQueryTypeAllowed(), 'SELECT Query is Not Allowed');
    }

	public function testGetQueryType()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('WITH top_rate as
        					(SELECT MAX(employee_billing_rate) as rate
        					 FROM employee)
        					SELECT employee_id id, employee_billing_rate rate,
        						(SELECT rate from top_rate)
        					FROM employee
        					WHERE employee_billing_rate >
        						(SELECT rate from top_rate) / 2');

    	$result = $filter->getQueryType();

        $this->assertEquals('SELECT', $result, 'Query Type Should Not Be WITH type');
    }

    public function testIsQueryTypeAllowedUsingQueryWithClause()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('WITH top_rate as
        					(SELECT MAX(employee_billing_rate) as rate
        					 FROM employee)
        					SELECT employee_id id, employee_billing_rate rate,
        						(SELECT rate from top_rate)
        					FROM employee
        					WHERE employee_billing_rate >
        						(SELECT rate from top_rate) / 2');

    	$result = $filter->isQueryTypeAllowed();

        $this->assertEquals(true, $result, 'SELECT Query is Allowed');
    }
}
