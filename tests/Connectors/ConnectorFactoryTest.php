<?php namespace PoulsenTest\Connections;

/**
 * Author: niikunihiro
 * Date: 2015/05/06
 * Time: 1:06
 */

use PoulsenTest\TestCase;
use Poulsen\Connectors\ConnectorFactory;
use PDOException;

class ConnectorFactoryTest extends TestCase {

    private $config = array(
        'default' => 'mysql',
        'connections' => array(
            'mysql' => array(
                'driver'    => 'mysql',
                'host'      => 'localhost',
                'database'  => 'niikunihiro',
                'username'  => 'nielsen',
                'password'  => 'poulsen',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
            )
        )
    );

    /**
     * @test
     */
    public function initWithArray()
    {
        $ConnectorFactory = new ConnectorFactory($this->config['connections']['mysql']);
        $actual = $ConnectorFactory->make();
        $this->assertInstanceOf('Poulsen\Connectors\MySQL', $actual);
    }

    /**
     * @test
     */
    public function make()
    {
        $ConnectorFactory = new ConnectorFactory();
        $properties = array(
            'config' => $this->config['connections']['mysql'],
            'connection' => 'mysql',
        );
        $this->setPrivateProperties($ConnectorFactory, $properties);

        $actual = $ConnectorFactory->make();
        $this->assertInstanceOf('Poulsen\Connectors\MySQL', $actual);
    }

    /**
     * @test
     * @expectedException PDOException
     * @expectedExceptionMessage no support
     */
    public function makeThrowPDOException()
    {
        $ConnectorFactory = new ConnectorFactory();
        $properties = array(
            'config' => $this->config,
            'connection' => 'hoge',
        );
        $this->setPrivateProperties($ConnectorFactory, $properties);
        $ConnectorFactory->make();
    }
}
