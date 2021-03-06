<?php namespace PoulsenTest\Query;

/**
 * Author: niikunihiro
 * Date: 2015/04/30
 * Time: 1:28
 */

use Mockery AS m;
use Poulsen\Query\Builder;
use PoulsenTest\TestCase;
use Exception;

/**
 * Class BuilderTest
 * @package PoulsenTest\Query
 */
class BuilderTest extends TestCase {

    /** @var Builder  */
    private $DB;

    public function setUp()
    {
        $mock = m::mock('Poulsen\Manager');
        $this->DB = new Builder('tests', $mock);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function table()
    {
        $table_name = 'tests';
        $this->DB->table($table_name);
        $actual = $this->getPrivateProperty($this->DB, 'table');
        $this->assertEquals($table_name, $actual);
    }

    /**
     * @test
     */
    public function selectDefault()
    {
        $this->DB->select();
        $actual = $this->getPrivateProperty($this->DB, 'columns');
        $this->assertEquals('*', $actual);
    }

    /**
     * @test
     */
    public function selectWithArray()
    {
        $this->DB->select(array('id', 'name'));
        $actual = $this->getPrivateProperty($this->DB, 'columns');
        $this->assertEquals('id, name', $actual);
    }

    /**
     * @test
     */
    public function selectWithTextArgs()
    {
        $this->DB->select('id', 'name', 'email');
        $actual = $this->getPrivateProperty($this->DB, 'columns');
        $this->assertEquals('id, name, email', $actual);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Invalid argument
     */
    public function where引数が配列で要素が3つでないときに例外を投げる()
    {
        $this->DB->where(array(1, 2));
    }

    /**
     * @test
     */
    public function whereWithArray()
    {
        $arg = array('name', '=', 'niikunihiro', 'AND');
        $this->DB->where($arg);
        $actual = $this->getPrivateProperty($this->DB, 'wheres');
        $this->assertEquals('AND name = ?', $actual[0]);
        $actual = $this->getPrivateProperty($this->DB, 'bindings');
        $this->assertEquals('niikunihiro', $actual[0]);
    }

    /**
     * @test
     */
    public function whereWith3Args()
    {
        $arg = array('name', '=', 'niikunihiro');
        $this->DB->where($arg[0], $arg[1], $arg[2]);
        $actual = $this->getPrivateProperty($this->DB, 'wheres');
        $this->assertEquals('AND name = ?', $actual[0]);
        $actual = $this->getPrivateProperty($this->DB, 'bindings');
        $this->assertEquals('niikunihiro', $actual[0]);
    }

    /**
     * @test
     */
    public function whereWithClosure()
    {
        $this->DB->where('name', '<>', 'niikunihiro')->where(function($query)
        {
            $query->where('name', '=', 'poulsen');
            $query->where('name', '=', 'nielsen', 'OR');
        }, null, null, 'OR')
        ->where('id', '<>', 2);

        $actual = $this->DB->wheres;
        $expected = array(
            'AND name <> ?',
            'OR (name = ? OR name = ?)',
            'AND id <> ?',
        );
        $this->assertEquals($expected, $actual);

        $actual = $this->DB->bindings;
        $expected = array('niikunihiro', 'poulsen', 'nielsen', 2);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function orWhereWithClosure()
    {
        $this->DB->where('name', '=', 'poulsen')
        ->orWhere(function($query)
        {
            $query->whereIn('role_id', array(1, 2, 3));
            $query->where('updated_at', '>', '2015-05-08 11:00:00');
        })
        ;

        $actual = $this->DB->wheres;
        $expected = array(
            'AND name = ?',
            'OR (role_id IN(?, ?, ?) AND updated_at > ?)',
        );
        $this->assertEquals($expected, $actual);

        $actual = $this->DB->bindings;
        $expected = array('poulsen', '1', '2', '3', '2015-05-08 11:00:00');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Invalid argument
     */
    public function whereInWhenSecondArgWithString()
    {
        $this->DB->whereIn('name', 'data');
    }

    /**
     * @test
     */
    public function whereInのデフォルト()
    {
        $this->DB->whereIn('name', array('niikunihiro', 'nielsen', 'poulsen'));
        $actual = $this->getPrivateProperty($this->DB, 'wheres');
        $this->assertSame('AND name IN(?, ?, ?)', $actual[0]);
    }

    /**
     * @test
     */
    public function whereIn複数条件を確認()
    {
        $this->DB->whereIn('name', array('niikunihiro', 'nielsen', 'poulsen'), false);
        $this->DB->whereIn('roles', array('super', 'admin'), true, 'OR');
        $expected_1st = 'AND name IN(?, ?, ?)';
        $expected_2nd = 'OR roles NOT IN(?, ?)';
        $actual = $this->getPrivateProperty($this->DB, 'wheres');
        $this->assertEquals($expected_1st,  $actual[0]);
        $this->assertEquals($expected_2nd, $actual[1]);

        $actual = $this->getPrivateProperty($this->DB, 'bindings');
        $this->assertEquals(array('niikunihiro', 'nielsen', 'poulsen', 'super', 'admin'),  $actual);
    }

    /**
     * @test
     */
    public function whereNotIn()
    {
        $this->DB->whereNotIn('roles', array('super', 'admin'));
        $expected = 'AND roles NOT IN(?, ?)';
        $actual = $this->getPrivateProperty($this->DB, 'wheres');
        $this->assertEquals($expected, $actual[0]);

        $actual = $this->getPrivateProperty($this->DB, 'bindings');
        $this->assertEquals(array('super', 'admin'), $actual);
    }

    /**
     * @test
     */
    public function whereRaw()
    {
        $this->DB->whereRaw('name', 'IS NULL');
        $actual = $this->getPrivateProperty($this->DB, 'wheres');

        $this->assertEquals('AND name IS NULL', $actual[0]);

    }

    /**
     * @test
     */
    public function take()
    {
        $this->DB->take(100);
        $actual = $this->getPrivateProperty($this->DB, 'take');
        $this->assertSame(100, $actual);
    }

    /**
     * @test
     */
    public function skip()
    {
        $this->DB->skip(30);
        $actual = $this->getPrivateProperty($this->DB, 'skip');
        $this->assertSame(30, $actual);
    }

    /**
     * @test
     */
    public function orderByDefaultSort()
    {
        $this->DB->orderBy('id');
        $actual = $this->getPrivateProperty($this->DB, 'orderArr');
        $this->assertEquals('id ASC', $actual[0]);
    }

    /**
     * @test
     */
    public function orderByDesc()
    {
        $this->DB->orderBy('id', 'DESC');
        $actual = $this->getPrivateProperty($this->DB, 'orderArr');
        $this->assertEquals('id DESC', $actual[0]);
    }

    /**
     * @test
     * @expectedException \OutOfRangeException
     */
    public function orderByWhenSortWithHogeThrowException()
    {
        $this->DB->orderBy('id', 'Hoge');
    }

    /**
     * @test
     */
    public function joinDefaultType()
    {
        $this->DB->join('test_details', 'test_id', '=', 'id');
        $actual = $this->getPrivateProperty($this->DB, 'join');
        $this->assertEquals('INNER JOIN test_details ON test_details.test_id = tests.id', $actual[0]);
    }

    /**
     * @test
     */
    public function joinRawDefaultType()
    {
        $this->DB->joinRaw('test_details', 'test_details.test_id', '=', 'tests.id');
        $actual = $this->getPrivateProperty($this->DB, 'join');
        $this->assertEquals('INNER JOIN test_details ON test_details.test_id = tests.id', $actual[0]);
    }

    /**
     * @test
     */
    public function joinTypeIsLeft()
    {
        $this->DB->join('test_details', 'test_id', '=', 'id', 'LEFT');
        $actual = $this->getPrivateProperty($this->DB, 'join');
        $this->assertEquals('LEFT JOIN test_details ON test_details.test_id = tests.id', $actual[0]);
    }

    /**
     * @test
     */
    public function joinRawTypeIsRight()
    {
        $this->DB->joinRaw('test_details', 'test_details.test_id', '=', 'tests.id', 'RIGHT');
        $actual = $this->getPrivateProperty($this->DB, 'join');
        $this->assertEquals('RIGHT JOIN test_details ON test_details.test_id = tests.id', $actual[0]);
    }

    /**
     * @test
     */
    public function set引数が配列のとき()
    {
        $data = array('name', 'nielsen');
        $this->DB->set($data);
        $actual = $this->getPrivateProperty($this->DB, 'setArr');
        $this->assertEquals($data, $actual[0]);
    }

    /**
     * @test
     */
    public function set引数が文字列で2つのとき()
    {
        $this->DB->set('name', 'nielsen');
        $actual = $this->getPrivateProperty($this->DB, 'setArr');
        $this->assertEquals(array('name', 'nielsen'), $actual[0]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid argument
     */
    public function set引数が文字列で1つのとき例外を投げる()
    {
        $this->DB->set('name');
    }

    /**
     * @test
     */
    public function values()
    {
        $this->DB->values('name', 'poulsen');
        $actual = $this->getPrivateProperty($this->DB, 'values');
        $this->assertEquals(array('key' => 'name', 'value' => 'poulsen'), $actual[0]);
    }

    /**
     * @test
     */
    public function buildSelectWhere()
    {
        $format = 'SELECT %s FROM tests';
        $this->DB->select('id', 'name')->where('name', '=', 'niikunihiro');
        $actual = $this->callPrivateMethod($this->DB, 'buildSelect', array($format));
        $expected =<<<SQL
SELECT id, name FROM tests
WHERE 1 AND name = ?
SQL;
        $this->assertEquals($expected, $actual[0]);
        $this->assertEquals(array('niikunihiro'), $actual[1]);
    }

    /**
     * @test
     */
    public function buildSelectJoin()
    {
        $this->DB->select('id', 'name')
                 ->join('test_details', 'test_id', '=', 'id')
                 ->where('name', '=', 'niikunihiro')
        ;

        $actual = $this->callPrivateMethod($this->DB, 'buildSelect', array('SELECT %s FROM tests'));
        $expected =<<<SQL
SELECT id, name FROM tests
INNER JOIN test_details ON test_details.test_id = tests.id
WHERE 1 AND name = ?
SQL;
        $this->assertEquals($expected, $actual[0]);
        $this->assertEquals(array('niikunihiro'), $actual[1]);
    }

    /**
     * @test
     */
    public function buildSelectAll()
    {
        $this->DB->select('id', 'name')
                 ->join('test_details', 'test_id', '=', 'id')
                 ->where('name', '=', 'niikunihiro')
                 ->whereIn('id', array(1, 2, 3))
                 ->whereNotIn('id', array(10))
                 ->whereRaw('name', 'IS NOT NULL')
                 ->orderBy('id', 'desc')
                 ->take(30)
                 ->skip(30)
        ;

        $actual = $this->callPrivateMethod($this->DB, 'buildSelect', array('SELECT %s FROM tests'));
        $expected =<<<SQL
SELECT id, name FROM tests
INNER JOIN test_details ON test_details.test_id = tests.id
WHERE 1 AND name = ? AND id IN(?, ?, ?) AND id NOT IN(?) AND name IS NOT NULL
ORDER BY id DESC
LIMIT 30
OFFSET 30
SQL;
        $this->assertEquals($expected, $actual[0]);
        $this->assertEquals(array('niikunihiro', '1', '2', '3', '10'), $actual[1]);
    }

    /**
     * @test
     */
    public function buildSet()
    {
        $Dt = new \DateTime;
        $now = $Dt->format('Y-m-d H:i:s');
        $this->DB->set('name', 'poulsen')
                 ->set('updated_at', $now)
        ;

        $actual = $this->callPrivateMethod($this->DB, 'buildSet', array('UPDATE tests'));
        $expected = 'UPDATE tests SET name = ?, updated_at = ?';
        $this->assertEquals($expected, $actual[0]);
        $this->assertEquals(array('poulsen', $now), $actual[1]);
    }

    /**
     * @test
     */
    public function getSelectIdNameFromTests()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('select');
        $this->DB = new Builder('tests', $mock);
        $this->DB->select('id', 'name')->get();

        $expected =<<<SQL
SELECT id, name FROM tests
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
    }

    /**
     * @test
     */
    public function getSelectIdNameFromTestsWhereOrderByLimitOffset()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('select');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')
                 ->select('id', 'name')
                 ->where('name', 'like', 'nielsen%')
                 ->whereIn('name', array('niikunihiro', 'poulsen'))
                 ->whereNotIn('id', array('1', '2'))
                 ->whereRaw('name', 'IS NOT NULL')
                 ->orderBy('id', 'DESC')
                 ->take(30)
                 ->skip(30)
                 ->get()
        ;

        $expected =<<<SQL
SELECT id, name FROM tests
WHERE 1 AND name like ? AND name IN(?, ?) AND id NOT IN(?, ?) AND name IS NOT NULL
ORDER BY id DESC
LIMIT 30
OFFSET 30
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
        $expected = array(
            'nielsen%',
            'niikunihiro',
            'poulsen',
            '1',
            '2',
        );
        $this->assertEquals($expected, $this->DB->logs[0]['bindings']);
    }

    /**
     * @test
     */
    public function getSelectDefaultFromTestsJoinSample()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('select');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')
                 ->join('samples', 'test_id', '=', 'id')
                 ->get();

        $expected =<<<SQL
SELECT * FROM tests
INNER JOIN samples ON samples.test_id = tests.id
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
    }

    /**
     * @test
     */
    public function countFromTests()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('fetch');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')->count();
        $expected =<<<SQL
SELECT count(*) AS count FROM tests
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
    }

    /**
     * @test
     */
    public function firstFromTests()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('fetch');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')->where('id', '=', 1)->first();
        $expected =<<<SQL
SELECT * FROM tests
WHERE 1 AND id = ?
LIMIT 1
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
        $this->assertEquals(array('1'), $this->DB->logs[0]['bindings']);
    }

    /**
     * @test
     */
    public function insert()
    {
        $Dt = new \DateTime;
        $now = $Dt->format('Y-m-d H:i:s');
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('insert');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')
                 ->values('name', 'poulsen')
                 ->values('created_at', $now)
                 ->values('updated_at', $now)
                 ->insert()
        ;

        $expected =<<<SQL
INSERT INTO tests (name, created_at, updated_at) VALUES (?, ?, ?)
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
        $this->assertEquals(array('poulsen', $now, $now), $this->DB->logs[0]['bindings']);
    }

    /**
     * @test
     */
    public function updateNotSetReturnFalse()
    {
        $mock = m::mock('Poulsen\Manager');
        $this->DB = new Builder(null, $mock);
        $actual = $this->DB->table('tests')->update();

        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function updateTests()
    {
        $Dt = new \DateTime;
        $now = $Dt->format('Y-m-d H:i:s');
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('update');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')
                 ->set('name', 'poulsen')
                 ->set('updated_at', $now)
                 ->where('id', '=', 2)
                 ->update()
        ;

        $expected =<<<SQL
UPDATE tests SET name = ?, updated_at = ?
WHERE 1 AND id = ?
SQL;
        $this->assertEquals($expected, $this->DB->logs[0]['query']);
        $this->assertEquals(array('poulsen', $now, '2'), $this->DB->logs[0]['bindings']);
    }

    /**
     * @test
     */
    public function deleteNoWhereReturnFalse()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('delete');
        $this->DB = new Builder(null, $mock);
        $actual = $this->DB->table('tests')->delete();
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function delete()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('delete');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')
                 ->where('id', '=', 1)
                 ->delete()
        ;

        $expected =<<<SQL
DELETE FROM tests
WHERE 1 AND id = ?
SQL;
        $this->assertSame($expected, $this->DB->logs[0]['query']);
        $this->assertEquals(array('1'), $this->DB->logs[0]['bindings']);
    }

    /**
     * @test
     */
    public function reset()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('select');
        $this->DB = new Builder(null, $mock);
        $this->DB->table('tests')
                 ->join('samples', 'test_id', '=', 'id')
                 ->select('id', 'name')
                 ->where('name', 'like', 'nielsen%')
                 ->whereIn('name', array('niikunihiro', 'poulsen'))
                 ->whereNotIn('id', array('1', '2'))
                 ->whereRaw('name', 'IS NOT NULL')
                 ->orderBy('id', 'DESC')
                 ->take(30)
                 ->skip(30)
                 ->get()
        ;

        $this->assertEquals('', $this->getPrivateProperty($this->DB, 'table'));
        $this->assertEquals('*', $this->getPrivateProperty($this->DB, 'columns'));
        $this->assertEmpty($this->getPrivateProperty($this->DB, 'wheres'));
        $this->assertEmpty($this->getPrivateProperty($this->DB, 'orderArr'));
        $this->assertNull($this->getPrivateProperty($this->DB, 'take'));
        $this->assertNull($this->getPrivateProperty($this->DB, 'skip'));
        $this->assertEmpty($this->getPrivateProperty($this->DB, 'setArr'));
        $this->assertEmpty($this->getPrivateProperty($this->DB, 'join'));
        $this->assertEmpty($this->getPrivateProperty($this->DB, 'values'));
    }

    /**
     * @test
     */
    public function chromePhp()
    {
        $actual = $this->callPrivateMethod($this->DB, 'chromePhp', array('query' => '', 'bindings' => array()));
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function statement()
    {
        $mock = m::mock('Poulsen\Manager');
        $mock->shouldReceive('statement')->andReturn(0);
        $actual = Builder::statement('DROP TABLE tests', $mock);
        $this->assertSame(0, $actual);
    }

    /**
     * @test
     */
    public function notExistPropertyReturnNull()
    {
        $this->assertNull($this->DB->hoge);
    }
}