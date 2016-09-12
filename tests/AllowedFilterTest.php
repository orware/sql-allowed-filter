<?php
use Orware\Sql\AllowedFilter;

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
		$filters = array();
        $filter = new AllowedFilter($filters);

        $filter->setQuery("select * from dual");

        $result = $filter->canExecuteQuery();

        $this->assertEquals(true, $result);
    }
}
