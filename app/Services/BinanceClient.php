<?php

namespace App\Services;

use App\Models\UserKey;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;

class BinanceClient
{
    private Client $http;
    private string $baseUrl;
    private int $recvWindow;

    public function __construct()
    {
        $this->baseUrl = config('app.binance_base_url', env('BINANCE_BASE_URL'));
        $this->recvWindow = (int) env('BINANCE_RECV_WINDOW', 5000);
        $this->http = new Client(['base_uri' => $this->baseUrl, 'timeout' => 10]);
    }

    private function getKeys(string $username): ?array
    {
        $row = UserKey::where('username', $username)->first();
        if (!$row) return null;

        return [
            'apiKey'   => Crypt::decryptString($row->api_key_encrypted),
            'secret'   => Crypt::decryptString($row->secret_key_encrypted),
            'testnet'  => (bool) $row->use_testnet,
        ];
    }

    public function setKeys(string $username, string $apiKey, string $secret, bool $useTestnet = true): void
    {
        UserKey::updateOrCreate(
            ['username' => $username],
            [
                'api_key_encrypted'   => Crypt::encryptString($apiKey),
                'secret_key_encrypted'=> Crypt::encryptString($secret),
                'use_testnet'         => $useTestnet,
            ]
        );
    }

    public function clearKeys(string $username): void
    {
        UserKey::where('username', $username)->delete();
    }

    public function hasKeys(string $username): bool
    {
        return UserKey::where('username', $username)->exists();
    }

    /** PUBLIC (no-sign) */
    public function getPrice(string $symbol): array
    {
        $res = $this->http->get('/api/v3/ticker/price', ['query' => ['symbol' => strtoupper($symbol)]]);
        return json_decode($res->getBody()->getContents(), true);
    }

    /** PRIVATE helpers (signing) */
    private function sign(string $secret, array $params): string
    {
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return hash_hmac('sha256', $query, $secret);
    }

    private function timestamp(): int
    {
        // Optionally sync with /api/v3/time if you see timestamp errors.
        return (int) round(microtime(true) * 1000);
    }

    private function authedRequest(string $username, string $method, string $path, array $params = []): array
    {
        $keys = $this->getKeys($username);
        if (!$keys) {
            throw new \RuntimeException('No API keys stored for this user');
        }
        $params['timestamp'] = $this->timestamp();
        $params['recvWindow'] = $params['recvWindow'] ?? $this->recvWindow;

        $signature = $this->sign($keys['secret'], $params);
        $params['signature'] = $signature;

        $options = [
            'headers' => ['X-MBX-APIKEY' => $keys['apiKey']],
        ];

        if (strtoupper($method) === 'GET' || strtoupper($method) === 'DELETE') {
            $options['query'] = $params;
        } else {
            $options['form_params'] = $params;
        }

        $res = $this->http->request($method, $path, $options);
        return json_decode($res->getBody()->getContents(), true);
    }

    /** PRIVATE tests/account */
    public function validateKeys(string $username): bool
    {
        // call account endpoint to verify keys
        try {
            $this->authedRequest($username, 'GET', '/api/v3/account');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function testOrder(string $username, array $order): array
    {
        // order: symbol, side(BUY/SELL), type(MARKET/LIMIT), quantity, price?, timeInForce?
        return $this->authedRequest($username, 'POST', '/api/v3/order/test', $order);
    }
}
