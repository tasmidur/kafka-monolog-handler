<?php

namespace Tasmidur\KafkaMonologHandler;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;
use function Widmogrod\Functional\curryN;

class KafkaLogger
{
    protected string $topicName;
    protected string $brokers;
    protected array $options;

    /**
     * Get the logging definition of Kafka channel
     * @param string $topicName
     * @param string $brokers
     * @param string|null $key
     * @param array $options
     * @param string|null $clientName
     * @return array
     */
    public static function getInstance(string $topicName, string $brokers, string $key = null, NormalizerFormatter $formatter = null): array
    {
        $default = [
            'driver' => 'custom',
            'via' => static::class,
            'topic' => $topicName,
            'key' => $key,
            'brokers' => $brokers,
            'client_name' => config('app.name'),
            'fallback' => 'daily',
            'formatter' => $formatter
        ];
        return $default;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(array $config): Logger
    {

        $logger = new Logger('kafka');
        throw_if(empty($config['brokers']), new \Exception('Brokers is provided', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
        if (!empty($config['sasl_config'])) {
            throw_if(empty($config['sasl_config']['username']), new \Exception('Username is invalid', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
            throw_if(empty($config['sasl_config']['password']), new \Exception('Password is invalid', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
            throw_if(empty($config['sasl_config']['mechanisms']), new \Exception('Auth Mechanisms is invalid', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
            throw_if(empty($config['sasl_config']['security_protocol']), new \Exception('SecurityProtocol is invalid', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
        }
        $topic = $config['topic'];
        $brokers = $config['brokers'];
        $key = $config['key'] ?? null;
        $formatter = $config['formatter'] ?? null;
        $handler = new KafkaLogHandler(topic: $topic, key: $key, brokers: $brokers, config: $config);
        $logger->pushHandler($handler);
        return $logger;
    }
}
