<?php

namespace App\Controller;

use App\Command\BitflipSynchronisationCommand;
use App\Command\CoinMarketCapSynchronisationCommand;
use App\Command\CoinsControlSynchronisationCommand;
use App\Command\CPatexSynchronisationCommand;
use App\Command\CryptopiaSynchronisationCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class BitcoinWhiteController extends Controller
{
    CONST CMC_ID = 'bitcoin-white';
    CONST URLS = [
        [
            'icon'  => 'external icon',
            'url'   => 'https://bitcoinwhite.org',
            'label' => 'Website',
        ],
        [
            'icon'  => 'facebook icon',
            'url'   => 'https://www.facebook.com/WhiteBitcoin',
            'label' => 'Facebook',
        ],
        [
            'icon'  => 'twitter icon',
            'url'   => 'https://twitter.com/WhiteBitcoin',
            'label' => 'Twitter',
        ],
        [
            'icon'  => 'telegram icon',
            'url'   => 'https://t.me/BTW_Community',
            'label' => 'Telegram',
        ],
        [
            'icon'  => 'area chart icon',
            'url'   => 'https://coinmarketcap.com/currencies/bitcoin-white/',
            'label' => 'CoinMarketCap',
        ],
    ];

    public function index()
    {
        $bitflip = $this->BitflipFormatter();

        return $this->render(
            'currency.html.twig',
            [
                'urls'    => BitcoinWhiteController::URLS,
                'current' => [
                    'name'   => 'Bitcoin White',
                    'symbol' => 'BTW',
                ],
                'basic'   => [
                    'coinmarketcap' => $this->CoinMarketCapFormatter(),
                ],
                'markets' => [
                    'btc' => [
                        'bitflip'   => [
                            'url'  => 'https://bitflip.li/trade/BTW-BTC',
                            'data' => isset($bitflip['btc']) ? $bitflip['btc'] : null,
                        ],
                    ],
                    'usd' => [
                        'bitflip'   => [
                            'url'  => 'https://bitflip.li/trade/BTW-USD',
                            'data' => isset($bitflip['usd']) ? $bitflip['usd'] : null,
                        ],
                    ],
                    'eur' => [
                        'bitflip'   => [
                            'url'  => 'https://bitflip.li/trade/BTW-EUR',
                            'data' => isset($bitflip['eur']) ? $bitflip['eur'] : null,
                        ],
                    ],
                ],
            ]
        );
    }

    private function CoinMarketCapFormatter()
    {
        $finder = new Finder();
        $cmcFiles = $finder
            ->files()
            ->in(CoinMarketCapSynchronisationCommand::PATH)
            ->name(CoinMarketCapSynchronisationCommand::FILENAME);
        $iterator = $cmcFiles->getIterator();
        $iterator->rewind();

        if (empty($iterator->current())) {
            return [];
        }

        /** @var SplFileInfo $cpatexFile */
        $cmcFile = json_decode(($iterator->current())->getContents(), true);

        return $cmcFile['bitcoin-white'];
    }

    private function BitflipFormatter()
    {
        $finder = new Finder();
        $bitflipFiles = $finder
            ->files()
            ->in(BitflipSynchronisationCommand::PATH)
            ->name(BitflipSynchronisationCommand::FILENAME);
        $iterator = $bitflipFiles->getIterator();
        $iterator->rewind();

        if (empty($iterator->current())) {
            return [];
        }

        /** @var SplFileInfo $bitflipFile */
        $bitflipFile = json_decode(($iterator->current())->getContents(), true);

        return [
            'btc' => $bitflipFile['BTW-BTC'],
            'usd' => $bitflipFile['BTW-USD'],
            'eur' => $bitflipFile['BTW-EUR'],
        ];
    }
}