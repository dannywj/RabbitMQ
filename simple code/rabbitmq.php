<?php

/**
 * 消息队列演示类
 * Created by DannyWang
 * wangjue@mia.com
 * 2016/11/30
 */
class RabbitMQ extends CI_Controller {
    const OPEN_SLEEP = false;

    public function __construct() {
        set_time_limit(80);
        parent::__construct();
        $this->load->library('monolog_client');
        $this->config->load("amqp");
    }

    #region ======direct模式======
    // 生产者：direct模式
    public function direct() {
        $this->load->library('amqp/publisher', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_direct");
        $this->publisher->inint($amqpConf['exchange'], $amqpConf['exchange_type'], $amqpConf['durable']);

        for ($i = 0; $i < 100000; $i++) {
            $info = "Rabbit[{$i}]";
            $this->log('begin enqueue direct:--->' . $info);
            $result = $this->publisher->send($info, $amqpConf['routing_key']);
            if (!$result) {
                $this->log('enqueue error');
            } else {
                //$this->log('enqueue success!');
            }
            if (self::OPEN_SLEEP) {
                sleep(1);
            }
        }

    }

    // 消费者1：direct模式
    public function directConsume1() {
        function callback_direct($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq directConsume-1] begin process:===' . $msgBody, 'activation_queue_debug');
            $CI->monolog_client->info('[rabbitmq directConsume-1] queue process success', 'activation_queue_debug');
            print_r('finish direct consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq directConsume-1] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_direct");
        $this->consumer->init($amqpConf['exchange'], $amqpConf['routing_key'], $amqpConf['queue_name']);
        while (true) {
            $this->consumer->run('callback_direct', false);
        }
    }

    // 消费者2：direct模式
    public function directConsume2() {
        function callback_direct2($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq directConsume-2] begin process:===' . $msgBody, 'activation_queue_debug');
            $CI->monolog_client->info('[rabbitmq directConsume-2] queue process success', 'activation_queue_debug');
            print_r('finish direct consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq directConsume-2] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_direct");
        $this->consumer->init($amqpConf['exchange'], $amqpConf['routing_key'], $amqpConf['queue_name']);
        while (true) {
            $this->consumer->run('callback_direct2', false);
        }
    }

    // 消费者3：direct模式
    public function directConsume3() {
        function callback_direct3($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq directConsume-3] begin process:===' . $msgBody, 'activation_queue_debug');
            $CI->monolog_client->info('[rabbitmq directConsume-3] queue process success', 'activation_queue_debug');
            print_r('finish direct consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq directConsume-3] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_direct");
        $this->consumer->init($amqpConf['exchange'], $amqpConf['routing_key'], $amqpConf['queue_name']);
        while (true) {
            $this->consumer->run('callback_direct3', false);
        }
    }
    #endregion

    #region ======fanout模式======
    // 生产者：fanout模式
    public function fanout() {
        $this->load->library('amqp/publisher', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_fanout1");
        $this->publisher->inint($amqpConf['exchange'], $amqpConf['exchange_type'], $amqpConf['durable']);

        for ($i = 1; $i <= 100000; $i++) {
            $info = "Rabbit[{$i}]";
            $this->log('begin enqueue fanout:--->' . $info);
            $result = $this->publisher->send($info, $amqpConf['routing_key']);
            //var_dump($result);
            if (!$result) {
                $this->log('enqueue error');
            } else {
                //$this->log('enqueue success!');
            }
            if (self::OPEN_SLEEP) {
                sleep(1);
            }
        }

    }

    // 消费者1：fanout模式
    public function fanoutConsume1() {
        function callback($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq fanoutConsume-1] begin process:===' . $msgBody, 'activation_queue_debug');
            $CI->monolog_client->info('[rabbitmq fanoutConsume-1] queue process success', 'activation_queue_debug');
            print_r('finish fanout consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq fanoutConsume-1] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        // 指定配置文件，使用队列1
        $amqpConf = $this->config->item("amqp_test_fanout1");
        $this->log('begin fanout consume1');
        $this->log($amqpConf);
        // 指定交换器类型为fanout
        $this->consumer->init($amqpConf['exchange'], $amqpConf['routing_key'], $amqpConf['queue_name'], $amqpConf['exchange_type']);
        $this->log('finish init');
        while (true) {
            $this->consumer->run('callback', false);
        }
    }

    // 消费者2：fanout模式
    public function fanoutConsume2() {
        function callback_fanout2($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq fanoutConsume-2] begin process:===' . $msgBody, 'activation_queue_debug');
            $CI->monolog_client->info('[rabbitmq fanoutConsume-2] queue process success', 'activation_queue_debug');
            print_r('finish fanout consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq fanoutConsume-2] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        // 指定配置文件，使用队列2
        $amqpConf = $this->config->item("amqp_test_fanout2");
        $this->consumer->init($amqpConf['exchange'], $amqpConf['routing_key'], $amqpConf['queue_name'], $amqpConf['exchange_type']);
        while (true) {
            $this->consumer->run('callback_fanout2', false);
        }
    }
    #endregion

    #region ======topic模式======
    // 生产者：topic模式
    public function topicSystemInfo() {
        $this->load->library('amqp/publisher', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_topic");
        $this->publisher->inint($amqpConf['exchange'], $amqpConf['exchange_type'], $amqpConf['durable']);

        for ($i = 1; $i <= 100000; $i++) {
            $info = "Rabbit[{$i}]";
            $this->log('begin enqueue topic:--->' . $info);
            $key = $_GET['key'];

            if ($key == 'system_info') {
                // 定义路由键为“系统信息日志”
                $routing_key = "log.system.info";
            }
            if ($key == 'user_error') {
                // 定义路由键为“用户异常日志”
                $routing_key = "log.user.error";
            }

            $result = $this->publisher->send($info, $routing_key);
            if (!$result) {
                $this->log('enqueue error');
            } else {
                //$this->log('enqueue success!');
            }
            if (self::OPEN_SLEEP) {
                sleep(1);
            }
        }
    }

    // 消费者1：topic模式
    // 处理所有日志消息
    public function topicConsumeAllLog() {
        function callback_topic_all_log($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq topicConsume-all_log] begin process:===' . $msgBody, 'activation_queue_debug');
            print_r('finish topic consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq topicConsume-all_log] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_topic");
        $this->log('begin topic log all');
        // 模糊匹配路由键规则
        $routing_key = "log.#";
        $queue_name = "test_topic_all_log";
        $this->consumer->init($amqpConf['exchange'], $routing_key, $queue_name, $amqpConf['exchange_type']);
        $this->log('finish init');
        while (true) {
            $this->consumer->run('callback_topic_all_log', false);
        }
    }

    // 消费者2：topic模式
    public function topicConsumeError() {
        function callback_topic2($envelope, $queue) {
            $CI =& get_instance();
            $CI->load->library('monolog_client');
            $msgBody = $envelope->getBody();
            $CI->monolog_client->info('[rabbitmq topicConsume-error] begin process:===' . $msgBody, 'activation_queue_debug');
            print_r('finish topic consume<br/>');
            $queue->ack($envelope->getDeliveryTag());
            $CI->monolog_client->info('[rabbitmq topicConsume-error] finish ack', 'activation_queue_debug');
        }

        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_topic");
        // 模糊匹配路由键规则（error类型的日志）
        $routing_key = "log.*.error";
        $queue_name = "test_topic_error_log";
        $this->consumer->init($amqpConf['exchange'], $routing_key, $queue_name, $amqpConf['exchange_type']);
        while (true) {
            $this->consumer->run('callback_topic2', false);
        }
    }
    #endregion

    #region ======监控======
    // Monitor
    /*
    public function getQueueTotalMessage() {
        // 设置队列的属性为：PASSIVE 被动的，才能不声明队列，只查询队列是否存在
        $this->queue->setFlags(AMQP_PASSIVE);
        $total = $this->queue->declareQueue();
        return $total;
    }
     * */
    public function monitor() {
        $this->load->library('amqp/consumer', array('connect_key' => 'test'));
        $amqpConf = $this->config->item("amqp_test_direct");
        $this->consumer->init($amqpConf['exchange'], $amqpConf['routing_key'], $amqpConf['queue_name']);
        $total = $this->consumer->getQueueTotalMessage();
        echo "The queue [" . $amqpConf['queue_name'] . "] total message is:{$total}";
    }
    #endregion
    // 日志记录
    private function log($info) {
        if (is_array($info)) {
            $info = json_encode($info);
        }
        $this->monolog_client->info('[rabbitmq]:' . $info, 'activation_queue_debug');
        echo $info . "<br/>\r\n";
    }
}