# Poulsen Simple Query Builder [![Build Status](https://travis-ci.org/niikunihiro/poulsen.svg?branch=master)](https://travis-ci.org/niikunihiro/poulsen)

PHP 5.3 で動くクエリービルダー

## 要件

- PHP5.3以上

## サポート

- MySQL

## インストール

Composer経由でインストールします

```composer.json
{
    "require": "niikunihiro/poulsen": "dev-master"
}
```

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

#### 基本

Builderクラスのインスタンスを生成し、table()メソッドで対象のテーブルを指定します。

```php
require_once __DIR__ .'/vendor/autoload.php';
use Poulsen\Query\Builder;

$DB = new Builder;
$query = $DB->table('users');
```

または、インスタンスの生成時にテーブルを指定する事も出来ます

```php
$query = new Builder('users');
```

テーブルを指定したBuilderクラスのインスタンスを取得したら、続けてデータの取得、挿入、更新、削除用のメソッドを指定して操作を行います。
Builderクラスのメソッドは直感的に扱えるようにチェーンメソッドで呼び出せるようになっています。


#### データの取得

##### データを配列で取得

配列の各要素はオブジェクトになっています。

```php
$users = $DB->table('users')->get();
```

##### データを一件取得

一件だけ取得したデータはオブジェクトになっています。

```php
$user = $DB->table('users')->first();
```

##### カラムを指定して、データを取得

```php
$users = $DB->table('users')->select('id', 'name', 'email AS mail_address')->first();
```

##### 条件を指定する

```php
$result = $DB->table('users')->where('name', '=', 'niikunihiro')->get();
```

#### LIKE条件を指定する

```php
$users = $DB->table('users')->where('name', 'LIKE', 'nii%')->get();
```

#### 複数条件を指定する

```php
$users = $DB->table('users')
    ->where('name', 'LIKE', 'nii%')
    ->orWhere('name', '<>', 'poulsen')
    ->get();
```
