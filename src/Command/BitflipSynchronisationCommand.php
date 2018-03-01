<?php

namespace App\Command;

use App\Client\BitflipClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class BitflipSynchronisationCommand extends Command
{

    const PATH = __DIR__ . '/../../public/exchange';
    const FILENAME = 'bitflip.json';

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:exchange:bitflip:synchronise')
            // the short description shown while running "php bin/console list"
            ->setDescription('Update markets data from Bitflip.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to update markets data from Bitflip. It\'s usefull because Bitflip have a very slow API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Bitflip Synchonisation',
            '=====================',
            '',
        ]);

        $client = new BitflipClient();
        dump($client->getTickers());

        $content = $this->synchroniseTickers($client, $output);
        $content = $this->synchroniseMarkets($client, $content, $output);
        $content["updatedAt"] = (new \DateTime())->format(DATE_ISO8601);

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

    private function synchroniseTickers(BitflipClient $client, OutputInterface $output)
    {
        $content = [];

        foreach ($client->getTickers() as $name => $value) {
            dump($name, $value);
            $marketName = $this->getMarketName($name);
            $output->writeln('Synchronise "' . $marketName . '" market.');
            $content[$marketName] = $value;
            $content[$marketName]['at'] = (new \DateTime())->getTimestamp();
        }

        return $content;
    }

    private function synchroniseMarkets(BitflipClient $client, $content, OutputInterface $output)
    {
        foreach ($client->getMarkets() as $name => $value) {
            $marketName = $this->getMarketName($name);
            $output->writeln('Synchronise "' . $marketName . '" market.');
            $content[$marketName]['market'] = $value;
        }

        return $content;
    }

    private function getMarketName($name) {
        return strtoupper(substr($name, 0, 3) . '-' . substr($name, 4, 3));
    }
}
