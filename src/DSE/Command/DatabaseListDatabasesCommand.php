<?php

namespace DSE\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseListDatabasesCommand extends DatabaseCommand
{
    protected function configure()
    {
        $this->setName('database:list-databases');
        $this->setDescription('List all databases');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $testConnection = $this->getApplication()->find(DatabaseTestConnectionCommand::NAME);
        $arguments = [
            'command' => DatabaseTestConnectionCommand::NAME,
            '--connection' => $input->getOption('connection', null)
        ];
        $connectionCode = $testConnection->run(new ArrayInput($arguments), $output);

        if (0 === $connectionCode) {
            $connection = $this->getConnection($input, $output);
            $sm = $connection->getSchemaManager();
            $databases = $sm->listDatabases();
            $databasesToList = [];
            foreach ($databases as $d) {
                $databasesToList[] = [$d];
            }
            $this->renderTable(['Databases'], $databasesToList);
        }
    }
}
