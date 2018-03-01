<?php
namespace App\Client;

interface ClientInterface
{
    public function getTickers($params = []);
}