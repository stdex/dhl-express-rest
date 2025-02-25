<?php

namespace Booni3\DhlExpressRest;

use Booni3\DhlExpressRest\API\Rates;
use Booni3\DhlExpressRest\API\Shipments;
use Booni3\DhlExpressRest\API\Tracking;
use GuzzleHttp\Client as GuzzleClient;

class DHL
{
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'Y-m-d\TH:i:s';

    protected const URI_SANDBOX = 'https://express.api.dhl.com/mydhlapi/test/';
    protected const URI_PRODUCTION = 'https://express.api.dhl.com/mydhlapi/???/';

    /** @var GuzzleClient */
    protected $client;

    /** @var array */
    protected $config;

    /** @var string */
    protected $user;

    /** @var string */
    protected $pass;

    public function __construct(array $config, GuzzleClient $client = null)
    {
        $this->config = $config;
        $this->client = $client;
    }

    public static function make(array $config, GuzzleClient $client = null): self
    {
        return new static($config, $client);
    }

    public function shipments(): Shipments
    {
        return new Shipments($this->client(), $this->config);
    }

    public function rates(): Rates
    {
        return new Rates($this->client(), $this->config);
    }

    public function tracking(): Tracking
    {
        return new Tracking($this->client(), $this->config);
    }

    protected function client(): GuzzleClient
    {
        if ($this->client) {
            return $this->client;
        }

        return new GuzzleClient([
            'base_uri' => $this->baseUri(),
            'timeout' => $this->config['timeout'] ?? 15
        ]);
    }

    private function baseUri(): string
    {
        if ($this->config['sandbox'] ?? false) {
            return self::URI_SANDBOX;
        }

        return self::URI_PRODUCTION;
    }
}
