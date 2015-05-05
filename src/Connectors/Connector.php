<?php namespace Poulsen\Connectors;

/**
 * Author: niikunihiro
 * Date: 2015/04/29
 * Time: 14:58
 */
use PDO;

/**
 * Class Connector
 * @package Poulsen\Connectors
 */
class Connector {

    protected $host     = '';
    protected $database = '';
    protected $port     = null;
    protected $username;
    protected $password;
    protected $charset;
    protected $collation;
    protected $prefix;
    protected $strict;

    /** @var PDO  */
    protected $pdo;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->host      = $config['host'];
        $this->database  = $config['database'];
        $this->username  = $config['username'];
        $this->password  = $config['password'];
        $this->charset   = $config['charset'];
        $this->collation = $config['collation'];
        $this->prefix    = $config['prefix'];
        $this->strict    = $config['strict'];
        if (isset($config['port'])) {
            $this->port = (integer)$config['port'];
        }
    }

    /**
     * @param $dns
     * @param $username
     * @param $password
     * @return PDO
     */
    protected function createConnection($dns, $username, $password)
    {
        return new PDO($dns, $username, $password);
    }

}