<?php

namespace DSE\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseExecuteQueryCommand extends DatabaseCommand
{

    const NAME = 'database:execute-query';

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Execute a SQL query on a database connection')
            ->addArgument('query', InputArgument::REQUIRED, 'The SQL query')
        ;
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

            $query = trim($this->input->getArgument('query'));
            $stmt = $connection->query($query);

            $columns = [];
            $results = [];
            while ($row = $stmt->fetch()) {
                if (empty($columns)) {
                    $columns = array_keys($row);
                }
                foreach ($row as $k => $r) {
                    $row[$k] = utf8_encode($r);
                }
                $results[] = $row;
            }

            $result = [
                'columns' => $columns,
                'results' => $results
            ];

            $this->render($result, 'json');

        }
    }
}
