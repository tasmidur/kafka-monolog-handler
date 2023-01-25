# Kafka Monolog Handler

The "kafka-monolog-handler" package is a package for Laravel framework. This package allows you to send your Laravel application's log messages to a Kafka server using Monolog, a popular logging library for PHP.

The package provides a handler for Monolog that sends log messages to a Kafka topic. The package also includes a config file, where you can specify the host and port of your Kafka server, as well as the topic to which the log messages should be sent

## Requirements

| Dependency                                             | Requirement |
|--------------------------------------------------------|-------------|
| [php](https://github.com/arnaud-lb/php-rdkafka) | `>=8.0`     |
| [Laravel](https://github.com/arnaud-lb/php-rdkafka)    | `>=8.0`     |


This package also requires the rdkafka php extension, which you can install by following [this documentation](https://github.com/edenhill/librdkafka#installation)

## Install

Install [kafka-monolog-handler](https://packagist.org/packages/tasmidur/kafka-monolog-handler).
```shell
composer require tasmidur/kafka-monolog-handler
```

## Get Started

1.Modify `config/logging.php`.
### Without Kafka SASL Config
```php
return [
    'channels' => [
        // ...
       'kafka' => \Tasmidur\KafkaMonologHandler\KafkaLogger::getInstance(
            topicName: env('KAFKA_LOG_FILE_TOPIC', 'laravel_logs'),
            brokers: env('KAFKA_LOG_BROKERS')
        ),
    ],
];
```
### With Kafka SASL Config and Log Formatter like ElasticsearchFormatter
```php
return [
    'channels' => [
        // ...
       'kafka' => \Tasmidur\KafkaLogger\KafkaLogger::getInstance(
            topicName: env('KAFKA_LOG_FILE_TOPIC', 'system_logs'),
            brokers: env('KAFKA_BROKERS'),
            options: [
                'is_sasl_apply' => env('IS_SASL_APPLY'), //true = applied or false= not apply
                'sasl_config' => [
                    'username' => env('KAFKA_BROKER_USERNAME'),
                    'password' => env('KAFKA_BROKER_PASSWORD'),
                    'mechanisms' => env('KAFKA_BROKER_MECHANISMS'),
                    'security_protocol' => env('KAFKA_BROKER_SECURITY_PROTOCOL')
                ],
                'formatter' => new ElasticsearchFormatter(
                    index: env('KAFKA_LOG_FILE_TOPIC', 'laravel_logs'),
                    type: "_doc")
            ]
        ),
    ],
];
```
2.Modify `.env`.
```
LOG_CHANNEL=kafka
KAFKA_BROKERS=kafka:9092,kafka:9093
KAFKA_LOG_FILE_TOPIC=laravel-logs

IS_SASL_APPLY=false
KAFKA_BROKER_USERNAME=username
KAFKA_BROKER_PASSWORD=password
KAFKA_BROKER_MECHANISMS=SCRAM-SHA-512
KAFKA_BROKER_SECURITY_PROTOCOL=SASL_SSL
```

## License

[MIT](LICENSE)
