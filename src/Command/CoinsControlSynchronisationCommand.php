<?php

namespace App\Command;

use App\Client\CoinsControlClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class CoinsControlSynchronisationCommand extends Command
{
    const PATH = __DIR__ . '/../../public/exchange';
    const FILENAME = 'coinscontrol.json';

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:exchange:coinscontrol:synchronise')
            // the short description shown while running "php bin/console list"
            ->setDescription('Update markets data from CoinsControl.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to update markets data from CoinsControl. It\'s usefull because CPatex have a very slow API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'CoinsControl Synchronisation',
            '============================',
            '',
        ]);

        $client = new CoinsControlClient();

        $content = $this->synchroniseTickers($client, $output);
        $content = $this->synchroniseMarkets($client, $content, $output);
        $content["updatedAt"] = (new \DateTime())->format(DATE_ISO8601);

//        $content = $this->formatKeyWording($content);

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

    private function synchroniseTickers(CoinsControlClient $client, OutputInterface $output)
    {
        $content = [];

        foreach ($client->getTickers() as $name => $value) {
            $marketName = $this->getMarketName($name);
            $output->writeln('Synchronise "' . $name . '" market.');
            $content[$marketName]['at'] = (new \DateTime())->getTimestamp();
            $content[$marketName]['ticker'] = $value;
        }

        return $content;
    }

    private function synchroniseMarkets(CoinsControlClient $client, $content, OutputInterface $output)
    {
        foreach ($client->getMarkets() as $name => $value) {
            $marketName = $this->getMarketName($name);
            $output->writeln('Synchronise "' . $marketName . '" market.');

            $content[$marketName]['market'] = $value;
        }

        return $content;
    }

    private function getMarketName($name)
    {
        return substr($name, 0, 3) . '-' . substr($name, 4, 3);
    }
}
