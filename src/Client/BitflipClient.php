<?php
/**
 * Created by PhpStorm.
 * User: dranac
 * Date: 10/01/18
 * Time: 17:13
 */

namespace App\Client;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Class BitflipClient
 * @package App\Client
 * https://bitflip.li/apidoc
 */
class BitflipClient implements ExchangeInterface
{
//    @ToDo Make a command with cron which update a file with updated data. CPATEX takes too much time to respond
    /**
     * @var string
     */
    const BASE_URL = 'https://api.bitflip.cc/method/';

    protected $markets = [
        'BTW:BTC',
        'BTW:USD',
        'BTW:EUR',
    ];


    const FORMATTER = [
        'orders' => [
            'amount' => 'volume',
            'price' => 'rate',
            'rate' => 'price',
        ],
        'ticker' => [
            'volume' => 'vol',
        ]
    ];

    /**
     * @param array $params
     * @return array
     */
    public function getTickers($params = [])
    {
        $tickers = [];
        foreach ($this->markets as $market) {
            $tickers[$market] = [
                "ticker" => $this->formatTicker(
                    $this->buildRequest('market.getOHLC', ['pair' => $market])[1]
                )
            ];
        }

        return $tickers;
    }

    public function getMarkets()
    {
        $results = [];
        foreach ($this->markets as $market) {
            // @ToDo il faut s'enregistrer sur le site pour passer ce call avec les tokens
            $data = $this->buildRequest('market.getOrderBook', ['pair' => $market, 'limit' => 20], true);
            dump($data);
            $results[$market] = $this->formatMarketOrder($data[1]['buy'], $data[1]['sell']);
        }

        return $results;
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return array
     */
    private function buildRequest($endpoint, $params = [], $private = false)
    {
        $client = new Client([
                'defaults' => [
                    'headers' => [
                        $private ? $this->getPrivateInfo(json_encode($params)) : null
                    ]
                ]
            ]
        );
        $url = $this->buildUrl(self::BASE_URL . $endpoint);

        $request = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);

        return $this->jsonDecode($request->getBody()->getContents());
    }

    /**
     * @param $url
     * @param array $params
     * @return string
     */
    private function buildUrl($url, $params = [])
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

    private function getPrivateInfo($body)
    {
        return [
            'X-API-Token' => getenv('BITFLIP_KEY'),
            'X-API-Sign'  => hash_hmac(
                'sha512',
                $body,
                getenv('BITFLIP_SECRET')
            ),
        ];
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
}