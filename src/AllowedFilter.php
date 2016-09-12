<?php
namespace Orware\Sql;

class AllowedFilter
{
	public function __construct()
	{

	}

	public function canExecuteQuery()
	{
		return false;
	}

	public function setQuery($query)
	{

	}

}
