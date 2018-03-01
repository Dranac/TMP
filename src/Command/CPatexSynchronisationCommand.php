<?php

namespace App\Command;

use App\Client\CPatexClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class CPatexSynchronisationCommand extends Command
{

    const PATH = __DIR__ . '/../../public/exchange';
    const FILENAME = 'cpatex.json';

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:exchange:cpatex:synchronise')
            // the short description shown while running "php bin/console list"
            ->setDescription('Update markets data from CPatex.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to update markets data from CPatex. It\'s usefull because CPatex have a very slow API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'CPatex Synchonisation',
            '=====================',
            '',
        ]);

        $client = new CPatexClient();

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

    private function synchroniseTickers(CPatexClient $client, OutputInterface $output)
    {
        $content = [];

        foreach ($client->getTickers() as $name => $value) {
            $marketName = $this->getMarketName($name);
            $output->writeln('Synchronise "' . $marketName . '" market.');
            $content[$marketName] = $value;
        }

        return $content;
    }

    private function synchroniseMarkets(CPatexClient $client, $content, OutputInterface $output)
    {
        foreach ($client->getMarkets() as $name => $value) {
            $marketName = $this->getMarketName($name);
            $output->writeln('Synchronise "' . $marketName . '" market.');
            $content[$marketName]['market'] = $value;
        }

        return $content;
    }

    private function getMarketName($name) {
        return strtoupper(substr($name, 0, 3) . '-' . substr($name, 3, 3));
    }
}
