<?php
namespace App\Command;

use App\Client\CoinCapClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class CoinCapSynchronisationCommand extends Command
{

    const PATH = __DIR__ . '/../../public/global';
    const FILENAME = 'coincap.json';

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:global:coincap:synchronise')
            // the short description shown while running "php bin/console list"
            ->setDescription('Update Global data from CoinCap.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to update data from CoinCap.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'CoinCap Synchonisation',
            '======================',
            '',
        ]);

        $client = new CoinCapClient();

        $content = $this->synchroniseData($client, $output);
//        $content["updatedAt"] = (new \DateTime())->format(DATE_ISO8601);

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

    private function synchroniseData(CoinCapClient $client, OutputInterface $output)
    {
        $content = $client->getTickers();

//        foreach ($client->getTickers() as $name => $value) {
//            $marketName = substr($name, 0, 3) . '-' . substr($name, 3, 3);
//            $output->writeln('Synchronise "' . $marketName . '" market.');
//            $content[$marketName] = $value;
//        }

        return $content;
    }

    private function formatData($data) {

    }
}
