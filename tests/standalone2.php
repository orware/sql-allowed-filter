<?php
require dirname(__DIR__).'/vendor/autoload.php';

use Orware\Sql\AllowedFilter;
use Orware\Sql\Filters;

function testIsSpridenTableAllowed()
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
        				 FROM employee as a)
        				SELECT s.spriden_id
        				FROM spriden as s, gobtpac
        				WHERE spriden_pidm = gobtpac_pidm
        				AND spriden_change_ind is null');

    $result = $filter->areTablesAllowed();

}

testIsSpridenTableAllowed();

function testIsGobtpacTableAllowedViaJoin()
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
        				UNION
        				SELECT s.spriden_id
        				FROM spriden as s
        				INNER JOIN
        					gobtpac
        				ON
        					spriden_pidm = gobtpac_pidm
        					AND spriden_change_ind is null
        				');

    $result = $filter->areTablesAllowed();

}

testIsGobtpacTableAllowedViaJoin();

function testVendorQuery()
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

    	$result = $filter->areTablesAllowed();

    }

testVendorQuery();

function testSalaryPerSecondQuery()
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
order by 2,3,4
        					");

    	$result = $filter->getTablesFromQuery();
    }

    testSalaryPerSecondQuery();