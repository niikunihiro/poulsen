<?php namespace Poulsen;

/**
 * Author: niikunihiro
 * Date: 2015/04/29
 * Time: 17:49
 */

use Poulsen\Connectors\ConnectorFactory;
use PDO;

/**
 * Class Manager
 * @package Poulsen
 */
class Manager implements ManagerInterface {

    /** @var PDO  */
    private $DB;

    /**
     * @param ConnectorFactory $connectorFactory
     */
    public function __construct(ConnectorFactory $connectorFactory)
    {
        $Instance = $connectorFactory->make();
        $this->DB = $Instance->connect();
    }

    /**
     * @return bool
     */
    public function begin()
    {
        return $this->DB->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->DB->commit();
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        return $this->DB->rollBack();
    }

    /**
     * @param $query
     * @param array $bindings
     * @return array
     */
    public function select($query, $bindings = array())
    {
        $PdoStatement = $this->DB->prepare($query);
        $PdoStatement->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
        $PdoStatement->execute($bindings);
        return $PdoStatement->fetchAll();
    }

    /**
     * @param $query
     * @param array $bindings
     * @return array
     */
    public function fetch($query, $bindings = array())
    {
        $PdoStatement = $this->DB->prepare($query);
        $PdoStatement->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
        $PdoStatement->execute($bindings);
        return $PdoStatement->fetch();
    }

    /**
     * @param $query
     * @param $bindings
     * @return string INSERT したテーブルの id を返す
     */
    public function insert($query, $bindings)
    {
        $PdoStatement = $this->DB->prepare($query);
        $PdoStatement->execute($bindings);
        return $this->DB->lastInsertId();
    }

    /**
     * @param $query
     * @param $bindings
     * @return bool 更新成功時は真、失敗時は偽を返す
     */
    public function update($query, $bindings)
    {
        $PdoStatement = $this->DB->prepare($query);
        return $PdoStatement->execute($bindings);
    }

    /**
     * @param $query
     * @param $bindings
     * @return bool
     */
    public function delete($query, $bindings)
    {
        $PdoStatement = $this->DB->prepare($query);
        return $PdoStatement->execute($bindings);
    }

    /**
     * @param $query
     * @return int
     */
    public function statement($query)
    {
        return $this->DB->exec($query);
    }
}