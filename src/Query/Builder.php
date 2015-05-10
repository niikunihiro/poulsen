<?php namespace Poulsen\Query;
/**
 * Author: niikunihiro
 * Date: 2015/03/09
 * Time: 10:58
 */

use ChromePhp;
use Closure;
use Exception;
use PDOException;
use Poulsen\Manager AS DB;
use Poulsen\Connectors\ConnectorFactory;

/**
 * Class Builder
 * @package Poulsen\Query
 */
class Builder {

    /** @var string  */
    private $table = '';

    /** @var DB|null  */
    private $DB;
    /** @var string  */
    private $columns = '*';
    /** @var array  */
    private $wheres = array();
    /** @var array  */
    private $bindings = array();
    /** @var array  */
    private $orderArr = array();
    /** @var integer|null  */
    private $take = null;
    /** @var integer|null  */
    private $skip = null;
    /** @var array UPDATE文のSET句に入れるデータの配列 */
    private $setArr = array();
    /** @var array JOIN句のSQLを要素として入れる配列 */
    private $join = array();
    /** @var array insert時に登録するデータのリスト */
    private $values = array();
    /** @var array  */
    public $logs = array();

    /**
     * @param $table
     * @param DB|null $DB
     */
    public function __construct($table = null, DB $DB = null)
    {
        try {
            $this->DB = (is_null($DB)) ? new DB(new ConnectorFactory) : $DB;
            if (!is_null($table)) {
                $this->table = $table;
            }
        } catch (PDOException $e) {
            $this->logs[] = array('query' => $e->getMessage(), 'bindings' => array());
            exit(mb_strimwidth($e->getMessage(), 0, 40, '...'));
        }
    }

    /**
     * @param $name
     * @return null|array
     */
    public function __get($name)
    {
        if (!in_array($name, array('wheres', 'bindings'), true)) {
            return null;
        }
        return $this->{$name};
    }

    /**
     * @param $name
     * @return $this
     */
    public function table($name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * 条件等のプロパティをリセットする
     */
    private function reset()
    {
        $this->table       = '';
        $this->columns     = '*';
        $this->wheres      = array();
        $this->bindings    = array();
        $this->orderArr    = array();
        $this->take        = null;
        $this->skip        = null;
        $this->setArr      = array();
        $this->join        = array();
        $this->values      = array();
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function select($columns = array('*'))
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        $this->columns = implode(', ', $columns);
        return $this;
    }

    /**
     * WHERE条件をセット
     * @param array|string $column ['カラム', '比較演算子', '値', '論理演算子']の配列または引数4つ
     * @param null|string $operator
     * @param null|string $value
     * @param string $boolean
     * @return $this
     * @throws Exception
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if ($column instanceof Closure)
        {
            return $this->whereNested($column, $boolean);
        }

        if (is_array($column)) {
            // 配列の場合は要素が3または4
            if (!in_array(count($column), array(3, 4), true)) {
                throw new Exception('Invalid argument');
            }
            $fields = $column;

            $column   = $fields[0];
            $operator = $fields[1];
            $value    = $fields[2];
            // 要素数が3の場合は論理演算子を追加する
            $boolean = (count($fields) === 3) ? $fields[3] : 'AND';
        }

        $this->wheres[]   = sprintf('%3$s %1$s %2$s ?', $column, $operator, $boolean);
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return Builder
     * @throws Exception
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * IN条件をセット
     * @param string $column
     * @param array $data
     * @param bool $not
     * @param string $boolean
     * @return $this
     */
    public function whereIn($column, $data, $not = false, $boolean = 'AND')
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid argument');
        }

        $bindings = array();
        $placeholderArr = array();
        foreach ($data as $value) {
            $bindings[]       = $value;
            $placeholderArr[] = '?';
        }

        $format = ($not === true) ? '%3$s %1$s NOT IN(%2$s)' : '%3$s %1$s IN(%2$s)';
        $placeholder = implode(', ', $placeholderArr);

        $this->wheres[] = sprintf($format, $column, $placeholder, $boolean);
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    /**
     * NOT IN 条件をセット
     * @param string $column
     * @param array $data
     * @param string $boolean
     * @return Builder
     * @throws Exception
     */
    public function whereNotIn($column, $data, $boolean = 'AND')
    {
        return $this->whereIn($column, $data, true, $boolean);
    }

    /**
     * 文字列で条件をセット（バインド等なし）
     * @param string $column
     * @param string $string
     * @param string $boolean
     * @return $this
     */
    public function whereRaw($column, $string, $boolean = 'AND')
    {
        $this->wheres[] = sprintf('%1$s %2$s %3$s', $boolean, $column, $string);

        return $this;
    }

    /**
     * @param Closure $callback
     * @param string $boolean
     * @return $this
     */
    public function whereNested($callback, $boolean = 'AND')
    {
        $Builder = new Builder($this->table, $this->DB);
        call_user_func($callback, $Builder);

        $wheres = implode(' ', $Builder->wheres);
        $wheres = preg_replace('/\A(AND|OR) /i', '', $wheres);

        $this->wheres[] = sprintf('%s (%s)', $boolean, $wheres);
        $this->bindings = array_merge($this->bindings, $Builder->bindings);
        unset($Builder);

        return $this;
    }

    /**
     * 取得レコード数を指定する
     * @param int $take
     * @return $this
     */
    public function take($take)
    {
        $this->take = (int)$take;
        return $this;
    }

    /**
     * オフセット値を指定する
     * @param int $skip
     * @return $this
     */
    public function skip($skip)
    {
        $this->skip = (int)$skip;
        return $this;
    }

    /**
     * @param $key
     * @param string $sort
     * @return $this
     */
    public function orderBy($key, $sort = 'ASC')
    {
        $sort = strtoupper($sort);
        if (!in_array($sort, array('ASC', 'DESC'))) {
            throw new \OutOfRangeException;
        }
        $this->orderArr[] = sprintf('%s %s', $key, $sort);
        return $this;
    }

    /**
     * INNER JOIN table1 ON table1.col1 = table2.col2
     * @param $table
     * @param $column1
     * @param string $operator
     * @param $column2
     * @param string $type
     * @return $this
     */
    public function joinRaw($table, $column1, $operator = '=', $column2, $type = 'INNER')
    {
        $join = sprintf('%s JOIN %s ON %s %s %s',
            $type,
            $table,
            $column1,
            $operator,
            $column2
        );
        $this->join[] = $join;
        return $this;
    }

    /**
     * @param $table
     * @param $column1
     * @param string $operator
     * @param $column2
     * @param string $type
     * @return $this
     */
    public function join($table, $column1, $operator = '=', $column2, $type = 'INNER')
    {
        $join = sprintf('%1$s JOIN %2$s ON %2$s.%3$s %4$s %5$s.%6$s',
            $type,
            $table,
            $column1,
            $operator,
            $this->table,
            $column2
        );
        $this->join[] = $join;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     * @throws Exception
     */
    public function set($data)
    {
        if (!is_array($data)) {
            if (func_num_args() !== 2) {
                throw new \InvalidArgumentException('Invalid argument');
            }
            $data = func_get_args();
        }
        $this->setArr[] = $data;
        return $this;
    }

    /**
     * INSERT 時にデータを格納する
     * @param $key
     * @param $value
     * @return $this
     */
    public function values($key, $value)
    {
        // key と valueに分ける
        $this->values[] = array('key' => $key, 'value' => $value);
        return $this;
    }

    /**
     * SELECT文で全件取得する
     * @return bool
     */
    public function get()
    {
        $format = <<<SQL
SELECT %s FROM $this->table
SQL;

        list($query, $bindings) = $this->buildSelect($format);
        $this->reset();

        $this->logs[] = array('query' => $query, 'bindings' => $bindings);

        return $this->DB->select($query, $bindings);
    }

    /**
     * SELECT文でレコード数を取得する
     * @return mixed
     */
    public function count()
    {
        $format = <<<SQL
SELECT count(*) AS count FROM $this->table
SQL;

        list($query, $bindings) = $this->buildSelect($format);
        $this->reset();

        $this->logs[] = array('query' => $query, 'bindings' => $bindings);

        return $this->DB->fetch($query, $bindings);
    }

    /**
     * SELECT文で１件取得する
     * @return bool
     */
    public function first()
    {
        $format = <<<SQL
SELECT %s FROM $this->table
SQL;
        list($query, $bindings) = $this->buildSelect($format);
        $this->reset();

        if (strpos($query, 'LIMIT 1') === false) {
            $query .= "\n" . 'LIMIT 1';
        }

        $this->logs[] = array('query' => $query, 'bindings' => $bindings);

        return $this->DB->fetch($query, $bindings);
    }

    /**
     * INSERT文を実行する
     * @return string INSERT したテーブルの id を返す
     */
    public function insert()
    {
        $keys = array_pluck($this->values, 'key');
        $bindings = array_pluck($this->values, 'value');
        $values_count = count($this->values);

        $keys_str = implode(', ', $keys);
        $placeholder = array();
        foreach (range(1, $values_count) as $i) {
            $placeholder[] = '?';
        }
        $placeholder_str = implode(', ', $placeholder);

        $format =<<<SQL
INSERT INTO $this->table (%s) VALUES (%s)
SQL;
        $query = sprintf($format, $keys_str, $placeholder_str);

        $this->reset();

        $this->logs[] = array('query' => $query, 'bindings' => $bindings);

        return $this->DB->insert($query, $bindings);
    }

    /**
     * UPDATE文を実行する
     * @return bool
     */
    public function update()
    {
        if (empty($this->setArr)) {
            // SET句がない場合はエラー
            return false;
        }

        $query =<<<SQL
UPDATE $this->table
SQL;

        list($query, $bindings) = $this->buildSet($query);
        list($query, $build_bindings) = $this->buildWhere($query);
        $bindings = array_merge($bindings, $build_bindings);

        $this->reset();

        $this->logs[] = array('query' => $query, 'bindings' => $bindings);

        return $this->DB->update($query, $bindings);
    }

    /**
     * DELETE文を実行する
     * @return bool
     */
    public function delete()
    {
        if (empty($this->wheres)) {
            // wheres句がない場合はエラー
            return false;
        }

        $query =<<<SQL
DELETE FROM $this->table
SQL;
        list($query, $build_bindings) = $this->buildWhere($query);
        $bindings = array_merge(array(), $build_bindings);

        $this->reset();

        $this->logs[] = array('query' => $query, 'bindings' => $bindings);

        return $this->DB->delete($query, $bindings);
    }

    /**
     * @param $query
     * @param DB $DB
     * @return int
     */
    public static function statement($query, DB $DB = null)
    {
        $DB = (is_null($DB)) ? new DB(new ConnectorFactory) : $DB;
        return $DB->statement($query);
    }

    /**
     * SELECT文をビルドする
     * @param $format
     * @return array
     */
    private function buildSelect($format)
    {
        $query = sprintf($format, $this->columns);

        if (!empty($this->join)) {
            array_map(function ($join) use (&$query) {
                $query .= "\n" . $join;
            }, $this->join);
        }

        return $this->buildConditions($query);
    }

    /**
     * SELECT文のWHERE句以降のSQLをビルドする
     * @param string $query
     * @return array
     */
    private function buildConditions($query)
    {
        list($query, $bindings) = $this->buildWhere($query);

        if (!empty($this->orderArr)) {
            $query .= "\n" . 'ORDER BY ' . implode(', ', $this->orderArr);
        }

        if (!is_null($this->take)) {
            $query .= "\n" . 'LIMIT ' . $this->take;
        }

        if (!is_null($this->skip)) {
            $query .= "\n" . 'OFFSET ' . $this->skip;
        }

        return array($query, $bindings);
    }

    /**
     * WHERE句をビルドする
     * @param $query
     * @return array
     */
    private function buildWhere($query)
    {
        $bindings = array();
        $whereArr = array();

        if (!empty($this->wheres)) {
            foreach ($this->wheres as $where) {
                $whereArr[] = $where;
            }
        }

        if (!empty($this->bindings)) {
            foreach ($this->bindings as $data) {
                $bindings[] = $data;
            }
        }

        if (!empty($whereArr)) {
            $query .= "\n" . 'WHERE 1 ' . implode(' ', $whereArr);
        }

        return array($query, $bindings);
    }

    /**
     * UPDATE文のSET句をビルドする
     * @param string $query SET句の前までのUPDATE文
     * @return array
     */
    private function buildSet($query)
    {
        $bindings = array();
        $setArr   = array();

        foreach ($this->setArr as $set) {
            $setArr[] = sprintf('%s = ?', $set[0]);
            $bindings[] = $set[1];
        }

        if (!empty($setArr)) {
            $query .= ' SET ' . implode(', ', $setArr);
        }

        return array($query, $bindings);
    }

    /**
     * @codeCoverageIgnore
     * @param $log
     */
    private function chromePhp($log)
    {
        if (!class_exists('ChromePhp') || php_sapi_name() === 'cli') {
            return;
        }
        ChromePhp::log('SQL:' , $log['query'], PHP_EOL , 'DATA:' ,$log['bindings']);
    }

    public function __destruct()
    {
        array_map(array($this, 'chromePhp'), $this->logs);
    }
}