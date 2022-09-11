<?php

namespace Tasmidur\KafkaMonologHandler;

use Illuminate\Support\Facades\Log;
use Junges\Kafka\Config\Sasl;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\Handler;
use Monolog\Logger;
use const Widmogrod\Monad\IO\throwIO;
use const Widmogrod\Monad\Writer\log;


/**
 * class KafkaLogHandler
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

    /**
     * @var string
     */
    private string $brokers;

    /**
     * @var string
     */
    private string $topic;

    /**
     * @var string
     */
    private string $key;


    /**
     * @param string $topic
     * @param string $key
     * @param string $brokers
     * @param array $config
     * @param NormalizerFormatter $formatter
     * @param string $fallback
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(string $topic, string $key, string $brokers, array $config, string $fallback = 'daily', int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->config = $config;
        $this->fallback = $fallback;
        $this->topic = $topic;
        $this->brokers = $brokers;
        $this->key = $key;
    }

    /**
     * @param array $record
     * @return void
     */
    protected function write(array $record): void
    {
        if (!empty($this->config['formatter'])) {
            $elasticSearchLogFomater = $this->config['formatter'];
            $record = $elasticSearchLogFomater->format($record);
        }

        $record['topic_name'] = $record['_index'] ?? $this->topic;

        /** ES index and type override avoid the collision*/
        if (array_key_exists("_index", $record)) {
            unset($record['_index']);
        }
        if (array_key_exists("_type", $record)) {
            unset($record['_type']);
        }

        try {
            $kafka = Kafka::publishOn(
                topic: $this->topic,
                broker: $this->brokers
            );
            if ($this->config['is_sasl_apply'] && !empty($this->config['sasl_config'])) {
                $kafka->withSasl(new Sasl(
                    username: $this->config['sasl_config']['username'],
                    password: $this->config['sasl_config']['password'],
                    mechanisms: $this->config['sasl_config']['mechanisms'],
                    securityProtocol: $this->config['sasl_config']['security_protocol'] ?? 'SASL_SSL'
                ));
            }

            if (!empty($this->key)) {
                $kafka->withKafkaKey($this->key);
            }
            $message = new Message(
                body: $record
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
            app('log')->channel($this->fallback)->$method(sprintf('%s (%s fallback: %s)', $record['formatted'], $record['channel'], $e->getMessage()));
        }

    }
}
