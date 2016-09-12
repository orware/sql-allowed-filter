SQL Allowed Filter Class for PHP
=================

This is a simple library for providing a simple Yes/No answer on whether or not a provided SQL Query is Allowed to be Executed Based on a set of provided filters aiming to allow/disallow certain query types, tables, or fields from being used in a provided query.

Installation with Composer
--------------------------

```shell
curl -s http://getcomposer.org/installer | php
php composer.phar require orware/sql-allowed-filter
```

OR

```shell
composer require orware/sql-allowed-filter
```

Usage
-----


```php
use Orware\Sql\AllowedFilter;

$filter = new AllowedFilter();

$filter->setQuery("select * from test");

// Uses default filters (defaults are pretty open to allow anything so $result should be true):
$result = $filter->canExecuteQuery();

```

