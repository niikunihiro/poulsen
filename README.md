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

### 基本

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


### データの取得

#### データを配列で取得

配列の各要素はオブジェクトになっています。

```php
$users = $DB->table('users')->get();
```

#### データを一件取得

一件だけ取得したデータはオブジェクトになっています。

```php
$user = $DB->table('users')->first();
```

#### カラムを指定して、データを取得

```php
$users = $DB->table('users')->select('id', 'name', 'email AS mail_address')->first();
```

#### 条件を指定する

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

#### テーブル結合

テーブル結合を行う場合は`join()`メソッドを使用します。  
`join()`メソッドは第1引数に結合するテーブル名を指定します。第2引数から第4引数まではON句の条件を指定します。

```php
$comments = $DB->table('articles')
            ->select('articles.title', 'comments.body AS comment')
            ->join('comments', 'article_id', '=', 'id')
            ->get();
```

外部結合を使いたい場合は`join()`メソッドの第5引数にLEFTまたはRIGHTを指定してください。

```php
$comments = $DB->table('articles')
            ->select('articles.title', 'comments.body AS comment')
            ->join('comments', 'article_id', '=', 'id', 'RIGHT')
            ->get();
```


### レコード挿入

`values()`メソッドでデータを設定し、`insert()`メソッドでレコードを挿入します。  
`insert()`メソッドは挿入したレコードのIDを返します

```php
$now = with(new DateTime)->format('Y-m-d H:i:s');
$id = $DB->table('users')
         ->values('username', 'poulsen')
         ->values('email', 'poulsen@example.com')
         ->values('password', '$2y$10$/L1ScTA7H7KTfS0Josftk.qPeAygzOZOB1GClEkTlMzmmuFw7/Yxa')
         ->values('created_at', $now)
         ->values('updated_at', $now)
         ->insert();
```

> `with()`関数はコンストラクターメソッドチェーンのためのショートカットに利用できます。

### レコード更新

`set()`メソッドでデータを設定し、`update()`メソッドでレコードを更新します。

```php
$now = with(new DateTime)->format('Y-m-d H:i:s');
$DB->table('users')
         ->set('rank', 3)
         ->set('updated_at', $now)
         ->update();
```

条件を指定する場合は、`where()`メソッド等を利用します。

```php
$DB->table('users')
         ->set('rank', 1)
         ->set('updated_at', $now)
         ->where('id', '=', 32)
         ->update();
```

### レコード削除

レコードを削除する場合は、`delete()`メソッドで削除します。  
※`where()`メソッド等で条件を指定しない場合は、削除を行わずに`false`を返します。

```php
$DB->table('users')
         ->where('id', '=', 32)
         ->delete();
```
