<?php

namespace Mix\Pool;

use Apps\Common\Core\ConnectionPoolInterface;
use Mix\Core\Component;
use Mix\Core\ComponentInterface;

/**
 * ConnectionPool组件
 * @author 刘健 <coder.liu@qq.com>
 */
class ConnectionPool extends Component implements ConnectionPoolInterface
{

    // 协程模式
    public static $coroutineMode = ComponentInterface::COROUTINE_MODE_REFERENCE;

    // 最多可空闲连接数
    public $maxIdle;

    // 最大连接数
    public $maxActive;

    // 连接队列
    protected $_queue;

    // 活跃连接集合
    protected $_actives;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize(); // TODO: Change the autogenerated stub
        // 创建协程队列
        $this->_queue = new \Swoole\Coroutine\Channel($this->maxIdle);
    }

    // 获取连接
    public function getConnection()
    {
        // 队列有连接，从队列取
        if ($this->getQueueCount() > 0) {
            return $this->pop();
        }
        // 达到最大连接数，从队列取
        if ($this->getCurrentCount() >= $this->maxActive) {
            return $this->pop();
        }
        // 创建连接
        $connection          = $this->createConnection();
        $id                  = spl_object_hash($connection);
        $this->_actives[$id] = $connection;
        return $connection;
    }

    // 释放连接
    public function release($connection)
    {
        $id = spl_object_hash($connection);
        $this->push($connection);
        unset($this->_actives[$id]);
    }

    // 获取连接池的统计信息
    public function getStats()
    {
        return [
            'current_count' => $this->getCurrentCount(),
            'queue_count'   => $this->getQueueCount(),
            'active_count'  => $this->getActiveCount(),
        ];
    }

    // 放入连接
    protected function push($connection)
    {
        if ($this->getQueueCount() < $this->maxIdle) {
            return $this->_queue->push($connection);
        }
        return false;
    }

    // 弹出连接
    protected function pop()
    {
        return $this->_queue->pop();
    }

    // 获取队列中的连接数
    protected function getQueueCount()
    {
        $count = $this->_queue->stats()['queue_num'];
        return $count < 0 ? 0 : $count;
    }

    // 获取活跃的连接数
    protected function getActiveCount()
    {
        return count($this->_actives);
    }

    // 获取当前总连接数
    protected function getCurrentCount()
    {
        return $this->getQueueCount() + $this->getActiveCount();
    }

}