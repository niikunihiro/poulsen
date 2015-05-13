<?php
/**
 * Author: niikunihiro
 * Date: 2015/04/29
 * Time: 21:27
 */


if (!function_exists('array_pluck'))
{
    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string  $key
     * @return array
     */
    function array_pluck($array, $key)
    {
        return array_map(function($v) use ($key)
        {
            return is_object($v) ? $v->$key : $v[$key];
        }, $array);
    }
}

if (!function_exists('with')) {
    /**
     * インスタンスを生成してチェーンでインスタンスメソッドを呼び出すときに使う
     * PHPのバージョンが5.4以上だと標準でできるので、そちらを使う
     * 例：
     * with(new Class)->method()
     * @param $object
     * @return object
     */
    function with($object)
    {
        return $object;
    }
}