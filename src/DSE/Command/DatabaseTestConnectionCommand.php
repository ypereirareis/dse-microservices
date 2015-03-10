<?php

namespace DSE\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseTestConnectionCommand extends DatabaseCommand
{
    const NAME = 'database:test-connection';

    protected function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('Test a database connection');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getConnection($input, $output);
        $connectionParams = $this->getConnectionParams();

        try {
            $connection->connect();
        } catch (\Exception $e) {
            $table = $this->getHelperSet()->get('table');
            $table
                ->setHeaders(['Host', 'Database', 'Driver', 'User', 'Password'])
                ->setRows([
                    [
                        $connectionParams['host'],
                        $connectionParams['database'],
                        $connectionParams['driver'],
                        $connectionParams['user'],
                        '**********'
                    ]
                ])
            ;
            $output->writeln('');
            $table->render($output);
            $output->writeln('');
            throw $e;
        }
    }
}
