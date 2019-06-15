<?php

use PHPUnit\Framework\TestCase;
use PageBlocker\PageBlockerDAO;

class PageBlockerDAOTest extends TestCase
{
    private $mysqli_mock;
    private $pageBlocker;

    private $table;
    private $block_time;
    private $attempt_length;

    public static function setUpBeforeClass()
    {
        date_default_timezone_set("Asia/Manila");
    }

    public function setUp()
    {
        $this->table = "sklt_page_blocker";
        $this->block_time = 60 * 30; // 30 minutes;
        $this->attempt_length = 5;

        $this->mysqli_mock = $this->createMock(\mysqli::class);
        $this->pageBlocker = new PageBlockerDAO($this->mysqli_mock, $this->table, "uri", "ip");
    }

    /**
     * @test
     */
    public function get_table_method_should_return_what_argument_passed_when_instantiated()
    {
        $this->assertEquals($this->table, $this->pageBlocker->getTable());
    }

    /**
     * @test
     */
    public function create_table_if_not_exist_method_should_return_true_if_query_result_was_not_false()
    {
        $this->mysqli_mock->method('query')
                          ->willReturn(true);

        $this->assertTrue($this->pageBlocker->createTableIfNotExist());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function create_table_if_not_exist_method_should_throw_exception_if_query_result_was_false()
    {
        $this->mysqli_mock->method('query')
                          ->willReturn(false);

        $this->pageBlocker->createTableIfNotExist();
    }

    /**
     * @test
     */
    public function get_all_method_should_return_array_if_result_was_not_false()
    {
        $obj = new \stdClass;
        $obj->num_rows = 0;

        $this->mysqli_mock->method('query')
                          ->willReturn($obj);

        $this->assertTrue(is_array($this->pageBlocker->getAll()));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function get_all_method_should_throw_exception_if_result_was_false()
    {
        $this->mysqli_mock->method('query')
                          ->willReturn(false);

        $this->pageBlocker->getAll();
    }

    /**
     * @test
     */
    public function get_method_should_return_array_if_result_was_not_false()
    {
        $obj = new \stdClass;
        $obj->num_rows = 0;

        $this->mysqli_mock->method('query')
                          ->willReturn($obj);

        $this->assertTrue(is_array($this->pageBlocker->get()));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function get_method_should_throw_exception_if_result_was_false()
    {
        $this->mysqli_mock->method('query')
                          ->willReturn(false);

        $this->pageBlocker->get();
    }

    /**
     * @test
     */
    public function add_method_should_return_true_if_result_was_not_false()
    {
        $this->mysqli_mock->method('query')
                          ->willReturn(true);

        $this->assertTrue($this->pageBlocker->add());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function add_method_should_throw_exception_if_result_was_false()
    {
        $obj = new \stdClass;
        $obj->num_rows = 0;

        $this->mysqli_mock->method('query')
                          ->willReturn(false);

        $this->pageBlocker->add();
    }

    /**
     * @test
     */
    public function reset_method_should_return_true_if_result_was_not_false()
    {
        $this->mysqli_mock->method('query')
                          ->willReturn(true);

        $this->assertTrue($this->pageBlocker->reset());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function reset_method_should_throw_exception_if_result_was_false()
    {
        $obj = new \stdClass;
        $obj->num_rows = 0;

        $this->mysqli_mock->method('query')
                          ->willReturn(false);

        $this->pageBlocker->reset();
    }

    /**
     * @test
     */
    public function is_authorized_method_should_return_true_if_result_was_empty()
    {
        $obj = new \stdClass;
        $obj->num_rows = 0;

        $this->mysqli_mock->method('query')
                          ->willReturn($obj);

        $this->assertTrue($this->pageBlocker->isAuthorized($this->block_time, $this->attempt_length));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function is_authorized_method_should_throw_exception_if_result_was_false()
    {
        $obj = new \stdClass;
        $obj->num_rows = 0;

        $this->mysqli_mock->method('query')
                          ->willReturn(false);

        $this->pageBlocker->isAuthorized($this->block_time, $this->attempt_length);
    }

    /**
     * @test
     */
    public function is_authorized_method_should_return_true_if_last_inserted_log_datetime_already_past_on_datenow()
    {
        $fetch_object = new \stdClass;
        $fetch_object->created_at = date('Y-m-d H:i:s');

        $obj = new ResultObjectClass($fetch_object);
        $obj->num_rows = 1;

        $this->mysqli_mock->method('query')
                          ->willReturn($obj);

        $this->assertTrue($this->pageBlocker->isAuthorized($this->block_time, $this->attempt_length));
    }

    /**
     * @test
     */
    public function is_authorized_method_should_return_true_if_result_rows_is_smaller_than_default_attempt_length()
    {
        $past_datetime = strtotime("+1 hour");
        $past_datetime = date("Y-m-d H:i:s", $past_datetime);

        $fetch_object = new \stdClass;
        $fetch_object->created_at = $past_datetime;

        $obj = new ResultObjectClass($fetch_object);
        $obj->num_rows = 1;

        $this->mysqli_mock->method('query')
                          ->willReturn($obj);

        $this->assertTrue($this->pageBlocker->isAuthorized($this->block_time, $this->attempt_length));
    }

    /**
     * @test
     */
    public function is_authorized_method_should_return_false_if_result_rows_is_greater_than_default_attempt_length()
    {
        $past_datetime = strtotime("+1 hour");
        $past_datetime = date("Y-m-d H:i:s", $past_datetime);

        $fetch_object = new \stdClass;
        $fetch_object->created_at = $past_datetime;

        $obj = new ResultObjectClass($fetch_object);
        $obj->num_rows = 6;

        $this->mysqli_mock->method('query')
                          ->willReturn($obj);
        $this->assertFalse($this->pageBlocker->isAuthorized($this->block_time, $this->attempt_length));

    }

    public function tearDown()
    {
        $this->mysqli_mock->close();
    }
}

class ResultObjectClass
{
    protected $to_be_return_of_fetch_object;
    protected $current_num_row = 0;

    public function __construct($to_be_return_of_fetch_object)
    {
        $this->to_be_return_of_fetch_object = $to_be_return_of_fetch_object;
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }

    public function fetch_object()
    {
        if ($this->num_rows >= ++$this->current_num_row)
        {
            return $this->to_be_return_of_fetch_object;
        }

        return null;
    }

    public function close()
    {
        $this->current_num_row = 0;
    }
}
