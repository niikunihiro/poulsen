<?php namespace PoulsenTest;

/**
 * Author: niikunihiro
 * Date: 2015/05/02
 * Time: 22:38
 */

use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class TestCase
 * @package PoulsenTest
 */
class TestCase extends PHPUnit_Framework_TestCase {

    /**
     * @param $Instance
     * @param $property
     * @return mixed
     */
    protected function getPrivateProperty($Instance, $property)
    {
        $reflectionClass = new ReflectionClass($Instance);
        $realIntention = $reflectionClass->getProperty($property);
        $realIntention->setAccessible(true);
        return $realIntention->getValue($Instance);
    }

    /**
     * @param $Instance
     * @param $properties
     */
    protected function setPrivateProperties($Instance, $properties)
    {
        $reflectionClass = new ReflectionClass($Instance);
        foreach ($properties as $key => $property)
        {
            $realIntention = $reflectionClass->getProperty($key);
            $realIntention->setAccessible(true);
            $realIntention->setValue($Instance, $property);
        }
    }

    /**
     * @param $Instance
     * @param $method
     * @param array $args
     * @return mixed
     */
    protected function callPrivateMethod($Instance, $method, $args = array())
    {
        $reflectionClass = new ReflectionClass($Instance);
        $method = $reflectionClass->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($Instance, $args);
    }
}