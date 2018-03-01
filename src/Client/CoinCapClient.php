<?php
/**
 * Created by PhpStorm.
 * User: dranac
 * Date: 10/01/18
 * Time: 17:13
 */

namespace App\Client;

use GuzzleHttp\Client;

/**
 * coincap.io is a website/api which provide coin global data
 *
 * Class CoinCapClient
 * @package App\Client
 */
class CoinCapClient implements ClientInterface
{
    /**
     * @var string
     */
    const BASE_URL = 'https://coincap.io/page/';

    /**
     * @param array $params
     * @return array
     */
    public function getTickers($params = array())
    {
        return $this->buildRequest('PHO', $params);
    }

    /**
     * @param $coinId
     * @param array $params
     * @return array
     */
    public function getTickerByCoin($coinId, $params = array())
    {
        return $this->buildRequest('ticker/' . $coinId, $params);
    }
    /**
     * @param array $params
     * @return array
     */
    public function getGlobalData($params = array())
    {
        return $this->buildRequest('global', $params);
    }
    /**
     * @param $endpoint
     * @param array $params
     * @return array
     */
    private function buildRequest($endpoint, $params = array())
    {
        $client = new Client();
        $url = $this->buildUrl(self::BASE_URL . $endpoint, $params);
        $request = $client->request('GET', $url);
        return $request->getBody()->getContents();
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
        return json_decode($string);
    }
}