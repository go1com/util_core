<?php

namespace go1\util\schema\tests;

use go1\util\tests\MockPDO;
use go1\util\DB;
use go1\util\tests\UtilCoreTestCase;
use go1\util\user\UserHelper;
use PDO;

class DBTest extends UtilCoreTestCase
{
    public function testConnectionOptions()
    {
        putenv('FOO_DB_NAME=foo_db');
        putenv('FOO_DB_USERNAME=foo_username');
        putenv('FOO_DB_PASSWORD=foo_password');
        putenv('FOO_DB_SLAVE=slave.foo.com');
        putenv('FOO_DB_HOST=foo.com');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $foo = DB::connectionOptions('foo');
        $this->assertEquals('pdo_mysql', $foo['driver']);
        $this->assertEquals('foo_db', $foo['dbname']);
        $this->assertNotEquals('slave.foo.com', $foo['host']);
        $this->assertEquals('foo_username', $foo['user']);
        $this->assertEquals('foo_password', $foo['password']);
        $this->assertEquals(3306, $foo['port']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $foo = DB::connectionOptions('foo');
        $this->assertEquals('slave.foo.com', $foo['host']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $foo = DB::connectionOptions('foo', true);
        $this->assertEquals('slave.foo.com', $foo['host']);
    }

    public function testCacheSet()
    {
        $cache = &DB::cache(self::class, []);
        $cache['foo'] = 'bar';

        $this->assertEquals(['foo' => 'bar'], DB::cache(self::class));
    }

    public function testCacheRetrieval()
    {
        $this->assertEquals(['foo' => 'bar'], DB::cache(self::class));
    }

    public function testCacheRetrievalReset()
    {
        $this->assertEquals([], DB::cache(self::class, null, true));
    }

    public function testMerge()
    {
        DB::merge($this->go1, 'gc_user', [], $dataUser = [
            'id'         => $userId = 99,
            'first_name' => 'Nikk',
            'last_name'  => 'Nguyen',
            'mail'       => 'user@foo.com',
            'uuid'       => 'xxx',
            'instance'   => 'foo.com',
            'password'   => 'yyy',
            'created'    => time(),
            'access'     => time(),
            'login'      => time(),
            'timestamp'  => time(),
            'status'     => 1,
            'data'       => json_encode(null),
        ]);
        $originalUser = (array) UserHelper::load($this->go1, $userId);

        $this->assertEmpty(array_diff_assoc($dataUser, $originalUser));

        DB::merge(
            $this->go1, 'gc_user',
            [
                'id' => $userId,
            ],
            $changedData = [
                'mail'       => 'changed@foo.com',
                'first_name' => 'Phuc',
                'instance'   => 'bar.com',
            ]
        );
        $user = (array) UserHelper::load($this->go1, $userId);

        $this->assertEquals($changedData, array_diff($user, $originalUser));
    }

    public function testLoad()
    {
        $fooUserId = $this->createUser($this->go1, [
            'mail' => 'foo@foo.com',
            'data' => $fooData = ['foo' => 'bar'],
        ]);
        $barUserId = $this->createUser($this->go1, [
            'mail' => 'bar@foo.com',
        ]);

        $fooUserObj = DB::load($this->go1, 'gc_user', $fooUserId, DB::OBJ);
        $barUserObj = DB::load($this->go1, 'gc_user', $barUserId, DB::OBJ);

        $this->assertIsObject($fooUserObj);
        $this->assertEquals((object) $fooData, $fooUserObj->data);
        $this->assertIsObject($barUserObj);
        $this->assertEquals([], $barUserObj->data);

        $fooUserArr = DB::load($this->go1, 'gc_user', $fooUserId, DB::ARR);
        $barUserArr = DB::load($this->go1, 'gc_user', $barUserId, DB::ARR);

        $this->assertIsArray($fooUserArr);
        $this->assertEquals($fooData, $fooUserArr['data']);
        $this->assertIsArray($barUserArr);
        $this->assertEquals([], $barUserArr['data']);
    }

    public function testLoadMultiple()
    {
        $fooUserId = $this->createUser($this->go1, [
            'mail' => 'foo@foo.com',
            'data' => $fooData = ['foo' => 'bar'],
        ]);
        $barUserId = $this->createUser($this->go1, [
            'mail' => 'bar@foo.com',
        ]);

        $usersObj = DB::loadMultiple($this->go1, 'gc_user', [$fooUserId, $barUserId], DB::OBJ);

        $this->assertCount(2, $usersObj);
        $this->assertIsObject($usersObj[0]);
        $this->assertEquals((object) $fooData, $usersObj[0]->data);
        $this->assertIsObject($usersObj[1]);
        $this->assertEquals([], $usersObj[1]->data);

        $usersArr = DB::loadMultiple($this->go1, 'gc_user', [$fooUserId, $barUserId], DB::ARR);

        $this->assertCount(2, $usersArr);
        $this->assertIsArray($usersArr[0]);
        $this->assertEquals($fooData, $usersArr[0]['data']);
        $this->assertIsArray($usersArr[1]);
        $this->assertEquals([], $usersArr[1]['data']);
    }

    public function testDefaultConfig()
    {
        putenv('DEV_DB_USERNAME=dev_username');
        putenv('DEV_DB_PASSWORD=dev_password');
        putenv('DEV_DB_HOST=dev.com');
        $bar = DB::connectionOptions('bar');
        $this->assertEquals('pdo_mysql', $bar['driver']);
        $this->assertEquals('bar_dev', $bar['dbname']);
        $this->assertEquals('dev_username', $bar['user']);
        $this->assertEquals('dev_password', $bar['password']);
        $this->assertEquals('dev.com', $bar['host']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        putenv('BAR_DB_SLAVE=slave.dev.com');
        $bar = DB::connectionOptions('bar');
        $this->assertEquals('slave.dev.com', $bar['host']);
    }

    public function testDefaultRDS()
    {
        $_ENV = [];
        putenv('RDS_DB_USERNAME=dev_username');
        putenv('RDS_DB_PASSWORD=dev_password');
        putenv('RDS_DB_HOST=bar.com');
        $bar = DB::connectionOptions('s_foo');
        $this->assertEquals('pdo_mysql', $bar['driver']);
        $this->assertEquals('s_foo_dev', $bar['dbname']);
        $this->assertEquals('dev_username', $bar['user']);
        $this->assertEquals('dev_password', $bar['password']);
        $this->assertEquals('bar.com', $bar['host']);

        putenv('_DOCKER_ENV=staging');
        putenv('RDS_DB_SLAVE=slave.bar.com');
        $bar = DB::connectionOptions('s_foo', true);
        $this->assertEquals('slave.bar.com', $bar['host']);
        $this->assertEquals('s_foo_prod', $bar['dbname']);
    }

    public function testPoolConnection()
    {
        $_ENV = [];
        putenv('FOO_DB_NAME=foo_db');
        putenv('FOO_DB_USERNAME=foo_username');
        putenv('FOO_DB_PASSWORD=foo_password');
        putenv('FOO_DB_SLAVE=slave.foo.com');
        putenv('FOO_DB_HOST=foo.com');

        $o = DB::connectionPoolOptions('foo', false, true, MockPDO::class);
        $this->assertEquals('mysql:host=foo.com;dbname=foo_db;port=3306', $o['pdo']->dsn);
        $this->assertEquals('foo_username', $o['pdo']->username);
        $this->assertEquals('foo_password', $o['pdo']->password);
        $this->assertEquals([
            1002                 => 'SET NAMES utf8',
            PDO::ATTR_PERSISTENT => true,
        ], $o['pdo']->options);
    }

    public function testPoolConnectionWithSSL()
    {
        $_ENV = [];
        putenv('FOO_DB_NAME=foo_db');
        putenv('RDS_SSL_DB_USERNAME=foo_username');
        putenv('RDS_SSL_DB_PASSWORD=foo_password');
        putenv('RDS_SSL_DB_SLAVE=slave.foo.com');
        putenv('RDS_SSL_DB_HOST=foo.com');
        putenv('FOO_DB_ENABLE_SSL=true');

        $o = DB::connectionPoolOptions('foo', false, true, MockPDO::class);
        $this->assertEquals('mysql:host=foo.com;dbname=foo_db;port=3306', $o['pdo']->dsn);
        $this->assertEquals('foo_username', $o['pdo']->username);
        $this->assertEquals('foo_password', $o['pdo']->password);
        $this->assertEquals([
            1002 => 'SET NAMES utf8',
            PDO::ATTR_PERSISTENT   => true,
            PDO::MYSQL_ATTR_SSL_CA => '',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ], $o['pdo']->options);
    }

    public function testEnableSSL()
    {
        $_ENV = [];
        //NON-SSL ENV VARIABLES
        $username = 'foo_username';
        $password = 'foo_password'; 
        $host = 'foo.com';
        $port = '3306';
        putenv('FOO_DB_NAME=foo_db');
        putenv("FOO_DB_USERNAME={$username}");
        putenv("FOO_DB_PASSWORD={$password}");
        putenv("FOO_DB_HOST={$host}");
        putenv("RDS_DB_PORT={$port}");
        
        //NON-SSL SLAVE ENV VARIABLES
        $slaveHost = 'slave.foo.com';
        $slaveUsername = 'foo_slave_username';
        $slavePassword = 'foo_slave_password';
        putenv("RDS_DB_PASSWORD_SLAVE={$slavePassword}");
        putenv("RDS_DB_USERNAME_SLAVE={$slaveUsername}");
        putenv("RDS_DB_SLAVE={$slaveHost}");

        //SSL ENV VARIABLES
        $sslUsername = 'foo_ssl_username';
        $sslPassword = 'foo_ssl_password';
        $sslHost = 'foo-ssl.com';
        $sslPort = '3307';
        putenv("RDS_SSL_DB_USERNAME={$sslUsername}");
        putenv("RDS_SSL_DB_PASSWORD={$sslPassword}");
        putenv("RDS_SSL_DB_HOST={$sslHost}");
        putenv("RDS_SSL_DB_PORT={$sslPort}");

        //SSL SLAVE ENV VARIABLES
        $sslSlaveHost = 'slave-ssl.foo.com';
        $sslSlaveUsername = 'foo_ssl_slave_username';
        $sslSlavePassword = 'foo_ssl_slave_password';
        putenv("RDS_SSL_DB_PASSWORD_SLAVE={$sslSlavePassword}");
        putenv("RDS_SSL_DB_USERNAME_SLAVE={$sslSlaveUsername}");
        putenv("RDS_SSL_DB_SLAVE={$sslSlaveHost}");

        //SSL TEST
        putenv('RDS_DB_ENABLE_SSL=true');
        $o = DB::connectionOptions('foo', false, true, MockPDO::class);
        $this->assertEquals([
            1002                                   => 'SET NAMES utf8mb4',
            PDO::MYSQL_ATTR_SSL_CA                 => '',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ], $o['driverOptions']);
        $this->assertEquals($sslHost, $o['host']);
        $this->assertEquals($sslUsername, $o['user']);
        $this->assertEquals($sslPassword, $o['password']);
        $this->assertEquals($sslPort, $o['port']);

        //SLAVE SSL TEST
        $o = DB::connectionOptions('foo', true, false, MockPDO::class);
        $this->assertEquals($sslSlaveHost, $o['host']);
        $this->assertEquals($sslSlaveUsername, $o['user']);
        $this->assertEquals($sslSlavePassword, $o['password']);

        //NO SSL TEST
        putenv('RDS_DB_ENABLE_SSL=false');
        $o = DB::connectionOptions('foo', false, true, MockPDO::class);
        $this->assertEquals([
            1002                                   => 'SET NAMES utf8mb4',
            PDO::MYSQL_ATTR_SSL_CA                 => '',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ], $o['driverOptions']);
        $this->assertEquals($host, $o['host']);
        $this->assertEquals($username, $o['user']);
        $this->assertEquals($password, $o['password']);
        $this->assertEquals($port, $o['port']);

        //SLAVE NO SSL TEST
        $o = DB::connectionOptions('foo', true, false, MockPDO::class);
        $this->assertEquals($slaveHost, $o['host']);
        $this->assertEquals($slaveUsername, $o['user']);
        $this->assertEquals($slavePassword, $o['password']);
    }

    public function testConnectionPoolOptionsException()
    {
        $connectionName = 'foo';
        putenv("FOO_DB_USERNAME=${connectionName}_username");
        putenv("FOO_DB_PASSWORD=${connectionName}_password");
        // SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Temporary failure in name resolution
        putenv("FOO_DB_HOST=:?$/");  

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage(
            'SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Name does not resolve'
        );
        $_ = DB::connectionPoolOptions($connectionName, false, true, \PDO::class);
    }
}
