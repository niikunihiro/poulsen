<?php namespace Poulsen\Connectors;

/**
 * Author: niikunihiro
 * Date: 2015/04/29
 * Time: 14:49
 */

use PDOException;
use PDO;

/**
 * Class MySQL
 * @package Poulsen\Connectors
 */
class MySQL extends Connector implements ConnectorInterface {

    /**
     * @return string
     */
    public function getDns()
    {
        $dns = !is_null($this->port)
            ? 'mysql:host=%1$s;port=%3$d;dbname=%2$s'
            : 'mysql:host=%1$s;dbname=%2$s';
        return vsprintf($dns, array($this->host, $this->database, $this->port));
    }

    /**
     * @return PDO
     * @throws PDOException
     */
    public function connect()
    {
        return $this->createConnection($this->getDns(), $this->username, $this->password);
    }

}