<?php

namespace DSE\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDetailTableCommand extends DatabaseCommand
{
    const NAME = 'database:detail-table';

    /**
     * @param array $data
     */
    protected function renderText(array $data = array())
    {
        $this->wln("");
        $this->wln("-------------------------------------------------------------");
        $this->wln(" > TABLE: <info>". $data['name']."</info>");
        $this->wln("-------------------------------------------------------------");

        $this->wln("");
        $this->wln("<comment>=== Primary key:</comment>", 1);

        foreach ($data['primary_keys'] as $v) {
            $this->wln(sprintf("= %s: ", strtoupper($v['name'])), 2);
            $this->wln("- Columns: ".$v['columns'], 3);
            $this->wln("- IsUnique: ".($v['is_unique'] ? 'yes' : 'no'), 3);
            $this->wln("- IsPrimary: ".($v['is_primary'] ? 'yes' : 'no'), 3);
        }

        $this->wln("");
        $this->wln("<comment>=== Indexes:</comment>", 1);
        foreach ($data['indexes'] as $v) {
            $this->wln(sprintf("= %s: ", strtoupper($v['name'])), 2);
            $this->wln("- Columns: ".$v['columns'], 3);
            $this->wln("- IsUnique: ".($v['is_unique'] ? 'yes' : 'no'), 3);
            $this->wln("- IsPrimary: ".($v['is_primary'] ? 'yes' : 'no'), 3);
        }

        $this->wln("");
        $this->wln("<comment>=== Foreign keys:</comment>", 1);
        foreach ($data['foreign_keys'] as $v) {
            $this->wln(sprintf("= %s: ", strtoupper($v['name'])), 2);
            $this->wln(sprintf('%s (%s) references %s (%s)', strtoupper($v['local_table']), $v['local_column'], strtoupper($v['foreign_table']), $v['foreign_column']), 3);
        }

        $this->wln("");
        $this->wln("<comment>=== Columns:</comment>", 1);
        foreach ($data['columns'] as $v) {
            $this->wln(sprintf("= %s: ", strtoupper($v['name'])), 2);
            unset($v['name']);
            foreach ($v as $key => $info) {
                if ($info !== null && strlen($info)) {
                    $this->wln("$key: ".$info, 3);
                }
            }
        }

        $this->wln("");
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Show table detail by table name and connection')
            ->addArgument('table', InputArgument::REQUIRED, 'The name of the table')
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $tableName = trim($this->input->getArgument('table'));

        if (0 === $this->testConnection()) {
            $connection = $this->getConnection($input, $output);
            $sm = $connection->getSchemaManager();

            if (!$sm->tablesExist([$tableName])) {
                throw new \Exception(sprintf('Unknown table: %s', $tableName));
            }

            $details = $sm->listTableDetails($tableName);

            $table = [
                'name' => $details->getName()
            ];

            $table['columns'] = [];
            foreach ($details->getColumns() as $c) {
                $conf = $c->toArray();
                $conf['type'] = $conf['type']->__toString();
                $table['columns'][] = $conf;
            }

            $table['foreign_keys'] = [];
            foreach ($details->getForeignKeys() as $k => $f) {
                $table['foreign_keys'][] = ['name' => $k,
                    'local_table' => $f->getLocalTableName(),
                    'local_column' => implode(', ', $f->getLocalColumns()),
                    'foreign_table' => $f->getForeignTableName(),
                    'foreign_column' => implode(', ',$f->getForeignColumns()),
                ];
            }

            $table['indexes'] = [];
            foreach ($details->getIndexes() as $i) {
                $table['indexes'][] = [
                    'name' => $i->getName(),
                    'columns' => implode(', ', $i->getColumns()),
                    'is_unique' => $i->IsUnique() ? true : false,
                    'is_primary' => $i->IsPrimary() ? true : false
                ];
            }

            $p = $details->getPrimaryKey();
            $table['primary_keys'] = [[
                'name' => $p->getName(),
                'columns' => implode(', ', $p->getColumns()),
                'is_unique' => $p->IsUnique() ? true : false,
                'is_primary' => $p->IsPrimary() ? true : false
            ]];

            $this->render($table, $this->getOutputFormat());
        }
    }
}
