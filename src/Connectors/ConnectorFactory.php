<?php namespace Poulsen\Connectors;

/**
 * Author: niikunihiro
 * Date: 2015/05/05
 * Time: 10:24
 */

use PDOException;

/**
 * Class ConnectorFactory
 * @package Poulsen\Connectors
 */
class ConnectorFactory {

    /** @var string  */
    private $connection;
    /** @var array  */
    private $config;

    /**
     * @param null|string|array $connection
     */
    public function __construct($connection = null)
    {
        if (is_array($connection)) {
            // arrayのときは設定値が入っている。driverをconnectorにする
            $this->config = $connection;
            $this->connection = $connection['driver'];
        } else {
            $config = $this->config();
            $this->connection = ($connection) ?: $config['default'];
            $this->config = $config['connections'][$this->connection];
        }
    }

    /**
     * @return ConnectorInterface
     */
    public function make()
    {
        switch ($this->connection)
        {
            case 'mysql':
                return new MySQL($this->config);
                break;
            default:
                throw new PDOException('no support');
        }
    }

    /**
     * @return array
     */
    private function config()
    {
        return include __DIR__ . '/../Config/database.php';
    }

}