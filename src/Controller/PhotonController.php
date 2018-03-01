<?php

namespace App\Controller;

use App\Command\CoinMarketCapSynchronisationCommand;
use App\Command\CoinsControlSynchronisationCommand;
use App\Command\CPatexSynchronisationCommand;
use App\Command\CryptopiaSynchronisationCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PhotonController extends Controller
{
    CONST CMC_ID = 'photon';
    CONST URLS = [
        [
            'icon'  => 'external icon',
            'url'   => 'https://photoncc.com',
            'label' => 'Website',
        ],
        [
            'icon'  => 'github icon',
            'url'   => 'https://github.com/photonproject',
            'label' => 'Github',
        ],
        [
            'icon'  => 'protect icon',
            'url'   => 'https://github.com/photonproject/photon/releases',
            'label' => 'Wallet',
        ],
        [
            'icon'  => 'facebook icon',
            'url'   => 'https://www.facebook.com/PHOCoin/',
            'label' => 'Facebook',
        ],
        [
            'icon'  => 'twitter icon',
            'url'   => 'https://twitter.com/PhotonCC',
            'label' => 'Twitter',
        ],
        [
            'icon'  => 'telegram icon',
            'url'   => 'https://photoncc.com',
            'label' => 'Telegram',
        ],
        [
            'icon'  => 'area chart icon',
            'url'   => 'https://coinmarketcap.com/currencies/photon/',
            'label' => 'CoinMarketCap',
        ],
    ];

    public function index()
    {
        $cryptopia = $this->CryptopiaFormatter();
        $cpatex = $this->CPatexFormatter();

        return $this->render(
            'currency.html.twig',
            [
                'urls'    => PhotonController::URLS,
                'current' => [
                    'name'   => 'Photon',
                    'symbol' => 'PHO',
                ],
                'basic'   => [
                    'coinmarketcap' => $this->CoinMarketCapFormatter(),
                ],
                'markets' => [
                    'btc' => [
                        'cryptopia' => [
                            'url'  => 'https://www.cryptopia.co.nz/Exchange/?market=PHO_BTC',
                            'data' => isset($cryptopia['btc']) ? $cryptopia['btc'] : null,
                        ],
                        'c-patex'   => [
                            'url'  => 'https://c-patex.com/markets/phobtc',
                            'data' => isset($cpatex['btc']) ? $cpatex['btc'] : null,
                        ],
                    ],
                    'ltc' => [
                        'c-patex' => [
                            'url'  => 'https://c-patex.com/markets/pholtc',
                            'data' => isset($cpatex['ltc']) ? $cpatex['ltc'] : null,
                        ],
                    ],
                    'blc' => [
                        'c-patex' => [
                            'url'  => 'https://c-patex.com/markets/phoblc',
                            'data' => isset($cpatex['blc']) ? $cpatex['blc'] : null,
                        ]
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

        return $cmcFile['photon'];
    }

    private function CPatexFormatter()
    {
        $finder = new Finder();
        $cpatexFiles = $finder
            ->files()
            ->in(CPatexSynchronisationCommand::PATH)
            ->name(CPatexSynchronisationCommand::FILENAME);
        $iterator = $cpatexFiles->getIterator();
        $iterator->rewind();

        if (empty($iterator->current())) {
            return [];
        }

        /** @var SplFileInfo $cpatexFile */
        $cpatexFile = json_decode(($iterator->current())->getContents(), true);

        return [
            'btc' => $cpatexFile['PHO-BTC'],
            'blc' => $cpatexFile['PHO-BLC'],
            'ltc' => $cpatexFile['PHO-LTC'],
        ];
    }

    /**
     * ToDo Check if this exchange is broken or not ?
     * @return array
     */
    private function CoinsControlFormatter()
    {
        $finder = new Finder();
        $ccontrolFiles = $finder
            ->files()
            ->in(CoinsControlSynchronisationCommand::PATH)
            ->name(CoinsControlSynchronisationCommand::FILENAME);
        $iterator = $ccontrolFiles->getIterator();
        $iterator->rewind();


        if (empty($iterator->current())) {
            return [];
        }

        /** @var SplFileInfo $cpatexFile */
        $ccontrolFile = json_decode(($iterator->current())->getContents(), true);

        return [
            'btc' => $ccontrolFile['PHO-BTC'],
            'ltc' => $ccontrolFile['PHO-LTC'],
        ];
    }

    private function CryptopiaFormatter()
    {
        $finder = new Finder();
        $cryptopiaFiles = $finder
            ->files()
            ->in(CryptopiaSynchronisationCommand::PATH)
            ->name(CryptopiaSynchronisationCommand::FILENAME);
        $iterator = $cryptopiaFiles->getIterator();
        $iterator->rewind();

        if (empty($iterator->current())) {
            return [];
        }

        /** @var SplFileInfo $cpatexFile */
        $ccontrolFile = json_decode(($iterator->current())->getContents(), true);

        return [
            'btc' => $ccontrolFile['PHO-BTC'],
        ];
    }
}