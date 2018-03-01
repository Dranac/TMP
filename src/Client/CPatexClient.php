<?php
/**
 * Created by PhpStorm.
 * User: dranac
 * Date: 10/01/18
 * Time: 17:13
 */

namespace App\Client;

use GuzzleHttp\Client;

class CPatexClient implements ExchangeInterface
{
//    @ToDo Make a command with cron which update a file with updated data. CPATEX takes too much time to respond
    /**
     * @var string
     */
    const BASE_URL = 'https://c-patex.com:443//api/v2/';

    protected $baseParams;
    protected $markets = [
        'phobtc',
        'phoblc',
        'pholtc',
    ];

    /**
     * CPatexClient constructor.
     */
    public function __construct()
    {
        $this->baseParams = [
            'access_key' => getenv('CPATEX_KEY'),
            'secret_key' => getenv('CPATEX_SECRET'),
            'timeout_sec' => 30,
        ];
    }

    /**
     * @param array $params
     * @return array
     */
    public function getTickers($params = [])
    {
        $data = $this->buildRequest('tickers.json', $params);
        $allowed = $this->markets;

        /**
         * To make the api response efficient and not
         * having to deal with thousand of results,
         * we'll only take the allowed market we need.
         */
        return array_filter(
            json_decode(json_encode($data), true),
            function ($key) use ($allowed) {
                return in_array($key, $allowed);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getMarkets()
    {
        $results = [];
        foreach ($this->markets as $market) {
            $data = $this->buildRequest('order_book.json', ['market' => $market]);
            $results[$market] = $data;
        }

        return $results;
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return array
     */
    private function buildRequest($endpoint, $params = array())
    {
        $client = new Client();
        $url = $this->buildUrl(self::BASE_URL . $endpoint, array_merge($params, $this->baseParams));
        $request = $client->request('GET', $url);
        return $this->jsonDecode($request->getBody()->getContents());
    }

    /**
     * @param $url
     * @param array $params
     * @return string
     */
    private function buildUrl($url, $params = array())
    {
        $output = $url;
        if ($params) {
            $output .= '?' . http_build_query($params);
        }
        return $output;
    }

    /**
     * @param $string
     * @return array
     */
    private function jsonDecode($string)
    {
        return json_decode($string, true);
    }
}