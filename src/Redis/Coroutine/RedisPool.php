<?php

namespace Mix\Redis\Coroutine;

use Mix\Pool\ConnectionPool;
use Mix\Pool\ConnectionPoolInterface;

/**
 * Class RedisPool
 * @author 刘健 <coder.liu@qq.com>
 */
class RedisPool extends ConnectionPool implements ConnectionPoolInterface
{

    // 主机
    public $host = '';

    // 端口
    public $port = '';

    // 数据库
    public $database = '';

    // 密码
    public $password = '';

    // 创建连接
    public function createConnection()
    {
        $connection = new RedisConnection([
            'connectionPool' => $this,
            'host'           => $this->host,
            'port'           => $this->port,
            'database'       => $this->database,
            'password'       => $this->password,
        ]);
        return $connection;
    }

    /**
     * 获取连接
     * @return RedisConnection
     */
    public function getConnection()
    {
        return parent::getConnection(); // TODO: Change the autogenerated stub
    }

}
