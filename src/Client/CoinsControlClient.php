<?php
/**
 * Created by PhpStorm.
 * User: dranac
 * Date: 10/01/18
 * Time: 17:13
 */

namespace App\Client;

use GuzzleHttp\Client;
use JMS\Serializer\SerializerBuilder;

/**
 * CoinsControl is a small exchange.
 * Accessible there : https://coinscontrol.com/
 * Class CoinsControlClient
 * @package App\Client
 */
class CoinsControlClient implements ExchangeInterface
{
//    @ToDo Make a command with cron which update a file with updated data. CPATEX takes too much time to respond
    /**
     * @var string
     */
    const BASE_URL = 'https://coinscontrol.com/Api/';
    const COUNT = 20;
    const FORMATTER = [
        'orders' => [
            'buy' => 'asks',
            'sell' => 'bids',
            'quantity' => 'volume',
            'rate' => 'price',
        ],
        'ticker' => [
            'ask' => 'sell',
            'bid' => 'buy',
            'volume' => 'vol',
        ]
    ];

    protected $baseParams;
    protected $markets = [
        'PHO_BTC',
        'PHO_LTC',
    ];


    /**
     * CoinsControlClient constructor.
     */
    public function __construct()
    {
        $this->baseParams = [
            'timeout_sec' => 30,
        ];
    }

    /**
     * @param array $params
     * @return array
     */
    public function getTickers($params = [])
    {
        $data = $this->buildRequest('GetMarketSummaries', $params);
        $allowed = $this->markets;

        /**
         * To make the api response efficient and not
         * having to deal with thousand of results,
         * we'll only take the allowed market we need.
         */

        $tickers = array_filter(
            json_decode(json_encode($data), true)['data'],
            function ($var) use ($allowed) {
                return in_array($var['market'], $allowed);
            }
        );

        $toReturn = [];
        foreach ($tickers as $ticker) {
            $toReturn[$ticker['market']] = $this->formatTicker($ticker);
        }

        return $toReturn;
    }

    private function formatTicker($ticker)
    {
        foreach ($ticker as $key => $value) {
            if (isset(self::FORMATTER['ticker'][$key])) {
                $ticker[self::FORMATTER['ticker'][$key]] = str_replace(',', '',$value);
                unset($ticker[$key]);
            } else {
                $ticker[$key] = str_replace(',', '',$value);
            }
        }
        return $ticker;
    }

    public function getMarkets()
    {
        $results = [];
        foreach ($this->markets as $market) {
            $data = $this->buildRequest('GetOrderBook/' . $market . '/Both/' . self::COUNT);
            $results[$market] = $this->formatMarketOrder(
                isset($data['data']['buy']) ? $data['data']['buy'] : [],
                isset($data['data']['sell']) ? $data['data']['sell'] : []
            );
        }

        return $results;
    }

    private function formatMarketOrder($buyTab = [], $sellTab = [])
    {
        $buy = [];
        foreach ($buyTab as $order) {
            $tmp = [];
            foreach ($order as $key => $value) {
                if (isset(self::FORMATTER['orders'][$key])) {
                    $tmp[self::FORMATTER['orders'][$key]] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
            $buy[] = $tmp;
        }

        $sell = [];
        foreach ($sellTab as $order) {
            $tmp = [];
            foreach ($order as $key => $value) {
                if (isset(self::FORMATTER['orders'][$key])) {
                    $tmp[self::FORMATTER['orders'][$key]] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
            $sell[] = $tmp;
        }

        return [
            'asks' => $buy,
            'bids' => $sell,
        ];
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

        return (array)$this->jsonDecode($request->getBody()->getContents());
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