<?php
namespace App\Client;

interface ExchangeInterface extends ClientInterface
{
    public function getTickers($params = []);
    public function getMarkets();
}