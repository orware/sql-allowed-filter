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

    public function testIsEmployeeTableAllowed()
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

    	$result = $filter->areTablesAllowed();

        $this->assertEquals(false, $result, 'EMPLOYEE table is not allowed');
    }

    public function testIsSpridenTableAllowed()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, employee, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('WITH top_rate as
        					(SELECT MAX(employee_billing_rate) as rate
        					 FROM employee)
        					SELECT s.spriden_id
        					FROM spriden as s, gobtpac
        					WHERE spriden_pidm = gobtpac_pidm
        					AND spriden_change_ind is null');

    	$result = $filter->areTablesAllowed();

        $this->assertEquals(true, $result, 'SPRIDEN table is allowed');
    }

    public function testIsGobtpacTableAllowedViaJoin()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "SPRIDEN, employee, gobtpac,goremal ,GORPAUD",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('WITH top_rate as
        					(SELECT MAX(employee_billing_rate) as rate
        					 FROM employee)
        					SELECT s.spriden_id
        					FROM spriden as s
        					INNER JOIN
        						gobtpac
        					ON
        						spriden_pidm = gobtpac_pidm
        						AND spriden_change_ind is null
        					');

    	$result = $filter->getTablesFromQuery();

        $this->assertEquals(array('EMPLOYEE', 'SPRIDEN', 'GOBTPAC'), $result, 'GOBTPAC table is allowed in JOIN');
    }

    public function testEmployeeTableNotAllowed()
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
        					SELECT s.spriden_id
        					FROM spriden as s
        					INNER JOIN
        						gobtpac
        					ON
        						spriden_pidm = gobtpac_pidm
        						AND spriden_change_ind is null
        					');

    	$result = $filter->getDisallowedTables();

        $this->assertEquals(array('EMPLOYEE'), $result, 'EMPLOYEE table is not allowed in query');
    }

    public function testVendorQueryTables()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "FTVVEND, SPRIDEN",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery('select SPRIDEN_PIDM, SPRIDEN_LAST_NAME, FTVVEND.*
							from FTVVEND
							INNER JOIN
							  SPRIDEN
							ON
							  FTVVEND_PIDM = SPRIDEN_PIDM
							  AND SPRIDEN_CHANGE_IND IS NULL
							WHERE SPRIDEN_FIRST_NAME IS NULL OR trim(SPRIDEN_FIRST_NAME) IS NULL
							ORDER BY LOWER(SPRIDEN_LAST_NAME);
        					');

    	$result = $filter->getTablesFromQuery();

        $this->assertEquals(array('FTVVEND', 'SPRIDEN'), $result, 'FTVVEND and SPRIDEN tables are allowed');
    }

    public function testSalaryPerSecondQuery()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "NV_NOEVIEW2, GOBTPAC, SPRIDEN",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery("SELECT PIDM,
					       SPRIDEN_ID,
					       GOBTPAC_LDAP_USER,
					       SPRIDEN_FIRST_NAME,
					       SPRIDEN_LAST_NAME,
					       SPRIDEN_MI,
					       NBRJOBS_SGRP_CODE,
					       -- This version of the calculation uses the SAL_ANNUAL and SAL_MONTH values:
					       ROUND(((SAL_ANNUAL / (ROUND(SAL_ANNUAL / SAL_MONTH) * HRS_PAY)) / 3600), 4) SAL_SECOND,
					       ROUND((SAL_ANNUAL / (ROUND(SAL_ANNUAL / SAL_MONTH) * HRS_PAY)), 2) SAL_HOUR,
					       ROUND(SAL_ANNUAL / SAL_MONTH) PAY_PERIODS,
					       -- The Alternative Section is For Those Folks that don't have a SAL_ANNUAL or SAL_MONTH value:
					       ROUND(((NBRJOBS_ANN_SALARY / (ROUND(NBRJOBS_ANN_SALARY / NBRJOBS_PER_PAY_SALARY) * HRS_PAY)) / 3600), 4) ALT_SAL_SECOND,
					       ROUND((NBRJOBS_ANN_SALARY / (ROUND(NBRJOBS_ANN_SALARY / NBRJOBS_PER_PAY_SALARY) * HRS_PAY)), 2) ALT_SAL_HOUR,
					       ROUND(NBRJOBS_ANN_SALARY / NBRJOBS_PER_PAY_SALARY) ALT_PAY_PERIODS,
					       -- I'm not sure how to handle the folks that don't have an HRS_PAY value:
					       -- Maybe we can default things...so if the HRS_PAY value is missing, automatically assume $60 / hr / 3600 seconds = Salary/Second
					       SAL_ANNUAL,
					       HRS_PAY,
					       SAL_MONTH,
					       NBRJOBS_ANN_SALARY,
					       NBRJOBS_PER_PAY_SALARY,
					       GOBTPAC_LDAP_USER,
					       SPRIDEN_FIRST_NAME,
					       SPRIDEN_LAST_NAME,
					       SPRIDEN_MI,
					       NBRJOBS_ECLS_CODE,
					       EMPLOYEE_TITTLE EMPLOYEE_TITLE,
					       EMPLOYEE_TYPE
					FROM NV_NOEVIEW2 noe
					INNER JOIN
					  GOBTPAC
					ON
					  noe.PIDM = GOBTPAC_PIDM
					INNER JOIN
					  SPRIDEN
					ON
					  noe.PIDM = SPRIDEN_PIDM
					  AND SPRIDEN_CHANGE_IND IS NULL
					WHERE NBRJOBS_SGRP_CODE = :sgrp_code
					AND NBRJOBS_STATUS = 'A'
					AND NBRBJOB_CONTRACT_TYPE = 'P'
					AND GOBTPAC_LDAP_USER IS NOT NULL
					ORDER BY SAL_ANNUAL DESC
        					");

    	$result = $filter->getTablesFromQuery();

        $this->assertEquals(array('NV_NOEVIEW2', 'GOBTPAC', 'SPRIDEN'), $result, 'NV_NOEVIEW2, GOBTPAC, and SPRIDEN tables are allowed');
    }

    public function testSuccessAndRetentionQuery()
    {
		$filters = [
		    "databases" => "prod8|test|test8",
		    "queryTypes" => "SELECT",
		    "tables" => "NV_NOEVIEW2, GOBTPAC, SPRIDEN",
		    "fields" => "SPRIDEN_ID, SPRIDEN_PIDM, GOBPTAC_LDAP_USER, GOBTPAC_EXTERNAL_USER, SPRIDEN_CHANGE_IND"
		];

        $filters = new Filters($filters);

        $filter = new AllowedFilter($filters);

        $filter->setQuery("select  term,
							        rtrim(crse,'1234567890') crse,
							        ethn,
							        sum(total) total,
							        round(sum(succeeded)/sum(total)*100,2) || '%' success,
							        round(sum(retained)/sum(total)*100,2) || '%' retention
							from    (select  term,
							                crse,
							                crn,
							                ethn,
							                gender,
							                total,
							                succeeded,
							                retained
							          from    ivc_sr_ethn
							          where   term = :SelTerm), other_table
							--where   :run is not null
							group by term, rtrim(crse,'1234567890'), ethn
							order by 2,3,4");

    	$result = $filter->getTablesFromQuery();

        $this->assertEquals(array('IVC_SR_ETHN', 'OTHER_TABLE'), $result, 'OTHER_TABLE should be included');
    }
}
