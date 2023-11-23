<?php

namespace Booni3\DhlExpressRest\API;

use Booni3\DhlExpressRest\Exceptions\ConfigException;
use Booni3\DhlExpressRest\Exceptions\ResponseException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class Client
{
    /** @var GuzzleClient */
    private $client;
    /**
     * @var array
     */
    private $config;

    public function __construct(GuzzleClient $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function get($endpoint = null, array $body = []): array
    {
        return $this->parse(function () use ($endpoint, $body) {
            $options = [
                'query' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ];
            $this->setAuth($options);

            return $this->client->request('GET', $endpoint, $options);
        });
    }

    public function post($endpoint = null, array $body = []): array
    {
        return $this->parse(function () use ($endpoint, $body) {
            $options = [
                'json' => $body,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ];
            $this->setAuth($options);

            return $this->client->request('POST', $endpoint, $options);
        });
    }

    private function parse(callable $callback)
    {
        $success = false;

        try {
            $response = call_user_func($callback);
            $success = json_decode((string)$response->getBody(), true);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response) {
                $clientException = json_decode((string)$response->getBody()->getContents(), true);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ResponseException::parseError($response->getBody());
        }

        if ($clientException ?? null) {
            throw ResponseException::clientException($clientException);
        }

        return $success;
    }

    protected function auth(): array
    {
        if (!$user = $this->config['user'] ?? false) {
            throw ConfigException::missingArgument('user');
        }

        if (!$pass = $this->config['pass'] ?? false) {
            throw ConfigException::missingArgument('pass');
        }

        return [$user, $pass];
    }

    protected function authApiKey(): string
    {
        if (!$apiKey = $this->config['apiKey'] ?? false) {
            throw ConfigException::missingArgument('apiKey');
        }

        return $apiKey;
    }

    protected function setAuth(array &$options): void
    {

        try {
            $options['auth'] = $this->auth();
        } catch (\Exception $e) {
        }

        try {
            $options['headers']['X-API-KEY'] = $this->authApiKey();
        } catch (\Exception $e) {
        }
    }
}
