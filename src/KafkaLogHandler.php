<?php

namespace Tasmidur\KafkaLogger;

use Junges\Kafka\Config\Sasl;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;


/**
 *
 */
class KafkaLogHandler extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    protected array $config;
    /**
     * @var string|mixed
     */
    private string $fallback;

    private string $brokers;

    private string $topic;


    /**
     * @param string $topic
     * @param string $brokers
     * @param array $config
     * @param string $fallback
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(string $topic, string $brokers, array $config, string $fallback = 'daily', int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->config = $config;
        $this->fallback = $fallback;
        $this->topic = $topic;
        $this->brokers = $brokers;
    }

    /**
     * @param array $record
     * @return void
     */
    protected function write(array $record): void
    {
        $data = (string)$record['formatted'];
        try {

            $kafka = Kafka::publishOn(
                topic: $this->topic,
                broker: $this->brokers
            );
            if (!empty($this->config['sasl_config'])) {
                $kafka->withSasl(new Sasl(
                    username: $this->config['sasl_config']['username'],
                    password: $this->config['sasl_config']['password'],
                    mechanisms: $this->config['sasl_config']['mechanisms'],
                    securityProtocol: $this->config['sasl_config']['security_protocol'] ?? 'SASL_SSL'
                ));
            }
            $message = new Message(
                body: $data,
                key: $this->config['client_name']
            );

            $kafka->withConfigOptions(
                [
                    'compression.type' => 'gzip',
                    'compression.codec' => 'none'
                ]
            )->withMessage($message);
            $kafka->send();
        } catch (\Throwable $e) {
            $method = strtolower($record['level_name']);
            app('log')->channel($this->fallback)->$method(sprintf('%s (%s fallback: %s)', $data, $record['channel'], $e->getMessage()));
        }

    }
}
