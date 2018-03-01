<?php

namespace App\Controller;

use App\Command\CoinMarketCapSynchronisationCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class BoardController extends Controller
{
    const ALLOWED_CRYPTO = [
        'photon'
    ];

    public function index()
    {
        return $this->render(
            'index.html.twig',
            [
                'coins' => $this->CoinMarketCapFormatter(),
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

	try {
		/** @var SplFileInfo $cmcFile */
		$cmcFile = json_decode(($iterator->current())->getContents(), true);

		$toReturn = [];
		foreach (self::ALLOWED_CRYPTO as $value) {
		    if (isset($cmcFile[$value])) {
		        if($cmcFile[$value]['percent_change_24h'] < -5) {
		            $cmcFile[$value]['status'] = 'red frown';
		        } else if($cmcFile[$value]['percent_change_24h'] < -5) {
		            $cmcFile[$value]['status'] = 'grey meh';
		        } else {
		            $cmcFile[$value]['status'] = 'green smile';
		        }
		        $toReturn[] = $cmcFile[$value];
		    }
		}

		return $toReturn;
	} catch (FatalThrowableError) {
		return [];
	}
    }
}
