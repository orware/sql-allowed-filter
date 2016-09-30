<?php
require dirname(__DIR__).'/vendor/autoload.php';

use Orware\Sql\AllowedFilter;
use Orware\Sql\Filters;

function testGetQueryType()
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
}

testGetQueryType();

function testIsQueryTypeAllowedUsingQueryWithClause()
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
}

testIsQueryTypeAllowedUsingQueryWithClause();