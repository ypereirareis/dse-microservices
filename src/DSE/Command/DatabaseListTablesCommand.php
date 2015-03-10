<?php

namespace DSE\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseListTablesCommand extends DatabaseCommand
{
    protected function configure()
    {
        $this->setName('database:list-tables')
            ->setDescription('List all tables from a database')
            ->addOption('simple', 's', InputOption::VALUE_NONE, 'Simple output only with table names')
        ;
        parent::configure();
    }

    /**
     * @param array $data
     */
    protected function renderText(array $data = array())
    {
        foreach ($data as $t) {
            $this->wln($t);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (0 === $this->testConnection()) {
            $connection = $this->getConnection($input, $output);
            $sm = $connection->getSchemaManager();
            $tables = $sm->listTables();

            if ($input->getOption('simple')) {

                $tmpTables = [];
                foreach ($tables as $t) {
                    $tmpTables[] = $t->getName();
                }
                $tables = $tmpTables;
                $this->render($tables, $this->getOutputFormat());

            } else {

                foreach ($tables as $t) {

                    $detail = $this->getApplication()->find(DatabaseDetailTableCommand::NAME);
                    $arguments = [
                        'command' => DatabaseDetailTableCommand::NAME,
                        '--connection' => $this->input->getOption('connection', null),
                        '--format' => $this->input->getOption('format'),
                        'table' => $t->getName()
                    ];
                    $detail->run(new ArrayInput($arguments), $this->output);

                }

            }

        }
    }
}
