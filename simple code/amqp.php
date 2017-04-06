<?php
#测试用

$amqp['test']['host']     = "172.0.0.1";
$amqp['test']['port']     = "5672";
$amqp['test']['login']    = "guest";
$amqp['test']['password'] = "guest";
$amqp['test']['vhost']    = "/";

$config['amqp_test_direct'] = array(
    "exchange"      => "test_direct",
    "exchange_type" => "direct",
    "durable"       => true,
    "queue_name"    => "test_direct_queue",
    "routing_key"   => "test_direct_rout",
);
$config['amqp_test_fanout1'] = array(
    "exchange"      => "test_fanout",
    "exchange_type" => "fanout",
    "durable"       => true,
    "queue_name"    => "test_fanout_queue1",
    "routing_key"   => 'bbb',
);
$config['amqp_test_fanout2'] = array(
    "exchange"      => "test_fanout",
    "exchange_type" => "fanout",
    "durable"       => true,
    "queue_name"    => "test_fanout_queue2",
    "routing_key"   => 'bbb',
);
$config['amqp_test_topic'] = array(
    "exchange"      => "test_topic",
    "exchange_type" => "topic",
    "durable"       => true,
    "queue_name"    => "test",
    "routing_key"   => 'topic',
);
