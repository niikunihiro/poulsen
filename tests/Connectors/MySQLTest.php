<?php namespace PoulsenTest\Connections;

/**
 * Author: niikunihiro
 * Date: 2015/05/05
 * Time: 1:34
 */

use PoulsenTest\TestCase;
use Poulsen\Connectors\MySQL;
use PDOException;
use Mockery AS m;

/**
 * Class MySQLTest
 * @package PoulsenTest\Connections
 */
class MySQLTest extends TestCase {

    private $config = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'niikunihiro',
        'username'  => 'nielsen',
        'password'  => 'poulsen',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'strict'    => false,
    ];

    /**
     * @test
     */
    public function getDns()
    {
        $Connection = new MySQL($this->config);
        $actual = $Connection->getDns();
        $expected = 'mysql:host=localhost;dbname=niikunihiro';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getDnsSetPort()
    {
        $this->config['port'] = 3333;
        $Connection = new MySQL($this->config);
        $actual = $Connection->getDns();
        $expected = 'mysql:host=localhost;port=3333;dbname=niikunihiro';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException PDOException
     */
    public function connectFail()
    {
        $mock = m::mock('PDO');
        $mock->shouldReceive('_construct')->andThrow('PDOException');
        $Connection = new MySQL($this->config);
        $Connection->connect();
    }

    public function tearDown()
    {
        m::close();
    }
}
