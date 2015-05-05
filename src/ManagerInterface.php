<?php namespace Poulsen;

/**
 * Author: niikunihiro
 * Date: 2015/04/29
 * Time: 14:36
 */

/**
 * Interface ManagerInterface
 * @package Poulsen
 */
interface ManagerInterface {

    public function begin();
    public function commit();
    public function rollback();
    public function select($query, $bindings);

}