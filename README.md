# Poulsen Simple Query Builder [![Build Status](https://travis-ci.org/niikunihiro/poulsen.svg?branch=master)](https://travis-ci.org/niikunihiro/poulsen)

PHP 5.3 で動くクエリービルダー

## 要件

- PHP5.3以上

## サポート

- MySQL

## インストール

Composer経由でインストール出来るようにする予定

## 使い方

src/Config/database.php でアクセス情報を設定する

```php

return array(
    'default' => 'mysql',
    'connections' => array(
        'mysql' => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'database',
            'username'  => 'username',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ),
    ),
);
```

#### SELECT文

```php
require_once __DIR__ .'/vendor/autoload.php';
use Poulsen\Query\Builder;

$DB = new Builder;
$result = $DB->table('users')->where('name', '=', 'poulsen')->first();
```
