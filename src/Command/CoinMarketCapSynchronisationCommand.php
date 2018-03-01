<?php

namespace App\Command;

use App\Client\CoinMarketCapClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class CoinMarketCapSynchronisationCommand extends Command
{

    const PATH = __DIR__ . '/../../public/global';
    const FILENAME = 'coinmarketcap.json';

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:global:coinmarketcap:synchronise')
            // the short description shown while running "php bin/console list"
            ->setDescription('Update Global data from CoinMarketCap.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to update data from CoinMarketCap.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'CoinMarketCap Synchonisation',
            '============================',
            '',
        ]);

        $client = new CoinMarketCapClient();

        $content = $this->synchroniseData($client, $output);
        $content["at"] = (new \DateTime())->format(DATE_ISO8601);

        $fs = new Filesystem();
        try {
            $fs->dumpFile($this::PATH . '/' . $this::FILENAME, json_encode($content));
        } catch (IOException $e) {
            throw new IOException($e);
        }

        $output->writeln([
            '',
            '================',
            'Sync. successful',
        ]);
    }

    private function synchroniseData(CoinMarketCapClient $client, OutputInterface $output)
    {
        $content = [];

        foreach ($client->getTickers() as $ticker) {
            $content[$ticker->id] = $ticker;
        };

        return $content;
    }
}
