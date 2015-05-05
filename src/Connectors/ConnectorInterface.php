<?php namespace Poulsen\Connectors;

/**
 * Author: niikunihiro
 * Date: 2015/04/29
 * Time: 18:48
 */

/**
 * Interface ConnectorInterface
 * @package Poulsen\Connectors
 */
interface ConnectorInterface {

    public function getDns();
    public function connect();
}