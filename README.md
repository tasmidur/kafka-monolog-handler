# Kafka Monolog Handler

Kafka Monolog Handler is used for storing laravel log into the Kafka message broker that's why any consumer like ELK or any other log manager can consume in an asynchronous way

## Requirements

| Dependency                                             | Requirement |
|--------------------------------------------------------|-------------|
| [php](https://github.com/arnaud-lb/php-rdkafka) | `>=8.0`     |
| [Laravel](https://github.com/arnaud-lb/php-rdkafka)    | `>=8.0`     |


This package also requires the rdkafka php extension, which you can install by following [this documentation](https://github.com/edenhill/librdkafka#installation)

## Install

Install `kafka-monolog-handler`.
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
       'kafka' => \Tasmidur\KafkaLogger\KafkaLogger::getInstance(
            topicName: env('KAFKA_LOG_FILE_TOPIC', 'system_logs'),
            brokers: env('KAFKA_BROKERS')
        ),
    ],
];
```
### With Kafka SASL Config
```php
return [
    'channels' => [
        // ...
       'kafka' => \Tasmidur\KafkaLogger\KafkaLogger::getInstance(
            topicName: env('KAFKA_LOG_FILE_TOPIC', 'system_logs'),
            brokers: env('KAFKA_BROKERS'),
            options: [
                'sasl_config' => [
                    'username' => env('KAFKA_BROKER_USERNAME'),
                    'password' => env('KAFKA_BROKER_PASSWORD'),
                    'mechanisms' => env('KAFKA_BROKER_MECHANISMS'),
                    'security_protocol' => env('KAFKA_BROKER_SECURITY_PROTOCOL')
                ]
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

KAFKA_BROKER_USERNAME=username
KAFKA_BROKER_PASSWORD=password
KAFKA_BROKER_MECHANISMS=SCRAM-SHA-512 or other
KAFKA_BROKER_SECURITY_PROTOCOL=SASL_SSL or other
```

## License

[MIT](LICENSE)
