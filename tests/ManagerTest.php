<?php namespace PoulsenTest;

/**
 * Author: niikunihiro
 * Date: 2015/05/05
 * Time: 3:02
 */

use Mockery AS m;
use Poulsen\Manager;

/**
 * Class ManagerTest
 * @package PoulsenTest
 */
class ManagerTest extends TestCase {


    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function begin()
    {
        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('beginTransaction')->andReturn(true);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $this->assertTrue($Manager->begin());
    }

    /**
     * @test
     */
    public function commit()
    {
        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('commit')->andReturn(true);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $this->assertTrue($Manager->commit());
    }

    /**
     * @test
     */
    public function rollback()
    {
        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('rollBack')->andReturn(true);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $this->assertTrue($Manager->rollback());
    }

    /**
     * @test
     */
    public function statement()
    {
        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('exec')->andReturn(1);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $actual = $Manager->statement('');
        $this->assertSame(1, $actual);
    }

    /**
     * @test
     */
    public function insert()
    {
        $pdoStatementMock = m::mock('PDOStatement');
        $pdoStatementMock->shouldReceive('execute');

        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('prepare')->andReturn($pdoStatementMock);
        $pdoMock->shouldReceive('lastInsertId')->andReturn(10);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $actual = $Manager->insert('', []);
        $this->assertSame(10, $actual);
    }

    /**
     * @test
     */
    public function update()
    {
        $pdoStatementMock = m::mock('PDOStatement');
        $pdoStatementMock->shouldReceive('execute')->andReturn(true);

        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('prepare')->andReturn($pdoStatementMock);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $actual = $Manager->update('', []);
        $this->assertTrue($actual);
    }

    /**
     * @test
     */
    public function delete()
    {
        $pdoStatementMock = m::mock('PDOStatement');
        $pdoStatementMock->shouldReceive('execute')->andReturn(true);

        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('prepare')->andReturn($pdoStatementMock);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $actual = $Manager->delete('', []);
        $this->assertTrue($actual);
    }

    /**
     * @test
     */
    public function select()
    {
        $pdoStatementMock = m::mock('PDOStatement');
        $pdoStatementMock->shouldReceive('setFetchMode');
        $pdoStatementMock->shouldReceive('execute');
        $pdoStatementMock->shouldReceive('fetchAll')->andReturn([]);

        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('prepare')->andReturn($pdoStatementMock);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $actual = $Manager->select('', []);
        $this->assertEquals($actual, []);
    }
    /**
     * @test
     */
    public function fetch()
    {
        $obj = new \stdClass;
        $pdoStatementMock = m::mock('PDOStatement');
        $pdoStatementMock->shouldReceive('setFetchMode');
        $pdoStatementMock->shouldReceive('execute');
        $pdoStatementMock->shouldReceive('fetch')->andReturn($obj);

        $pdoMock = m::mock('PDO');
        $pdoMock->shouldReceive('prepare')->andReturn($pdoStatementMock);

        $connectorInterfaceMock = m::mock('Poulsen\Connectors\ConnectorInterface');
        $connectorInterfaceMock->shouldReceive('connect')->andReturn($pdoMock);

        $factoryMock = m::mock('Poulsen\Connectors\ConnectorFactory');
        $factoryMock->shouldReceive('make')->andReturn($connectorInterfaceMock);

        $Manager = new Manager($factoryMock);
        $actual = $Manager->fetch('', []);
        $this->assertEquals($actual, $obj);
    }
}
