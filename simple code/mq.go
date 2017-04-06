// Rabbit MQ Example
package main

import (
	"bytes"
	"fmt"
	"log"
	"os"
	"strconv"
	"time"

	"github.com/streadway/amqp"
)

var conn *amqp.Connection
var channel *amqp.Channel
var count = 0

// 队列配置
const (
	queueName  = "go_test_queue"
	routingKey = "go_test_routingkey"
	exchange   = "go_test_exchange"
	mqurl      = "amqp://guest:guest@127.0.0.1:5672/"
)

func main() {
	if len(os.Args) <= 1 {
		fmt.Println("please input args:[push,receive,count]")
		return
	}
	fname := os.Args[1]
	fmt.Println("======begin queue test======")

	switch fname {
	case "push":
		publish()
		//time.Sleep(100 * time.Second)
		break
	case "receive":
		receive()
		break
	case "count":
		getQueueMessageCount()
		break
	default:
		fmt.Println("invalid main args")
		break
	}
	//close()
	fmt.Println("======end queue test======")
}

func publish() {
	forever := make(chan bool)
	go func() {
		i := 1
		for {
			push(i)
			//time.Sleep(1 * time.Second)
			i++
		}
	}()
	<-forever
}

// 连接rabbitmq server
func mqConnect() {
	var err error
	conn, err = amqp.Dial(mqurl)
	failOnErr(err, "failed to connect tp rabbitmq")
	channel, err = conn.Channel()
	failOnErr(err, "failed to open a channel")
}

// 生产消息
func push(i int) {
	if channel == nil {
		mqConnect()
	}
	channel, err := conn.Channel()
	// 定义交换器
	err = channel.ExchangeDeclare(
		exchange, // name
		"direct", // type
		true,     // durable
		false,    // auto-deleted
		false,    // internal
		false,    // no-wait
		nil,      // arguments
	)
	failOnErr(err, "Failed to declare an exchange")

	msgContent := "Rabbit MQ!-" + strconv.Itoa(i)

	// 发布
	channel.Publish(exchange, routingKey, false, false, amqp.Publishing{
//		DeliveryMode: amqp.Persistent,
		ContentType: "text/plain",
		Body:        []byte(msgContent),
	})
	printMessage("push message -> [" + msgContent + "] ok")
}

// 消费消息
func receive() {
	if channel == nil {
		mqConnect()
	}
	// 定义队列
	q, err := channel.QueueDeclare(
		queueName, // name
		true,      // durable
		false,     // delete when usused
		false,     // exclusive
		false,     // no-wait
		nil,       // arguments
	)
	failOnErr(err, "Failed to declare a queue")

	// 进行队列绑定
	err = channel.QueueBind(
		queueName,  // queue name
		routingKey, // routing key
		exchange,   // exchange
		false,
		nil)
	failOnErr(err, "Failed to bind a queue")
	// 消费
	msgs, err := channel.Consume(q.Name, "", false, false, false, false, nil) //auto ack==false
	failOnErr(err, "")
	forever := make(chan bool)
	go func() {
		//fmt.Println(*msgs)
		for d := range msgs {
			s := bytesToString(&(d.Body))
			count++
			printMessage("receve msg is : <- " + *s + " --" + strconv.Itoa(count))
			d.Ack(false) // ack
		}
	}()

	fmt.Printf(" [*] Waiting for messages. To exit press CTRL+C\n")

	<-forever
}

func bytesToString(b *[]byte) *string {
	s := bytes.NewBuffer(*b)
	r := s.String()
	return &r
}

// 获取队列消息数
func getQueueMessageCount() {
	if channel == nil {
		mqConnect()
	}
	msgs, err := channel.QueueDeclarePassive(queueName, true, false, false, false, nil)
	failOnErr(err, "")
	printMessage("the queue:[" + queueName + "] message total count is:" + strconv.Itoa(msgs.Messages))
}

// 异常处理
func failOnErr(err error, msg string) {
	if err != nil {
		log.Fatalf("%s:%s", msg, err)
		panic(fmt.Sprintf("%s:%s", msg, err))
	}
}

// 关闭连接
func close() {
	channel.Close()
	conn.Close()
}

// 打印日志
func printMessage(str string) {
	// 获取当前时间
	now := time.Now()
	userFile := now.Format("log_20060102") + ".txt"
	// 打开文件并追加内容（不存在则创建）
	fout, err := os.OpenFile(userFile, os.O_RDWR|os.O_CREATE|os.O_APPEND, 0x777)
	defer fout.Close()
	if err != nil {
		fmt.Println(userFile, err)
		return
	}
	// 当前时间格式化
	nowTime := now.Format("2006-01-02 15:04:05")
	logInfo := "[INFO][" + nowTime + "] " + str
	fmt.Println(logInfo)
	fout.WriteString(logInfo + "\r\n")
}
