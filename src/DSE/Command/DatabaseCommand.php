<?php

namespace DSE\Command;

use Doctrine\DBAL\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

abstract class DatabaseCommand extends Command
{
    protected $input;
    protected $output;

    private $connection;
    private $connectionParams;

    /**
     * @param  InputInterface            $input
     * @param  OutputInterface           $output
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (null === $this->connection) {
            $this->connection = $this->buildConnection();
            $platform =  $this->connection->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping('enum', 'string');
        }

        return $this->connection;
    }

    protected function testConnection()
    {
        $testConnection = $this->getApplication()->find(DatabaseTestConnectionCommand::NAME);
        $arguments = [
            'command' => DatabaseTestConnectionCommand::NAME,
            '--connection' => $this->input->getOption('connection', null)
        ];

        return $testConnection->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * @return mixed
     */
    protected function getConnectionParams()
    {
        return $this->connectionParams;
    }

    /**
     * @return string
     */
    protected function getOutputFormat()
    {
        $format = $this->input->getOption('format');

        return $format ? $format : 'text';
    }

    protected function configure()
    {
        $this->addOption(
            'connection',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Connection name to load from configuration file (if many)'
        );

        $this->addOption(
            'format',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Output format (json|text)'
        );
    }

    /**
     * @param $headers
     * @param $data
     */
    protected function renderTable($headers, $data)
    {
        $table = $this->getHelperSet()->get('table');
        $table
            ->setHeaders(array('Tables'))
            ->setRows($data)
        ;
        $table->render($this->output);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Exception
     */
    private function buildConnection()
    {
        $selectedConnection = null;
        $configurationFile = __DIR__.'/../../../config/connections.yml';
        $yamlParser = new Parser();
        try {
            $connections = $yamlParser->parse(file_get_contents($configurationFile));
        } catch (ParseException $e) {
            throw new \Exception (
                sprintf('Unable to parse the YAML configuration file : "%s"', $e->getMessage())
            );
        }

        if (empty($connections)) {
            throw new \Exception (
                sprintf('No connection found in file "%s"', $configurationFile)
            );
        }

        /**
         * Only one connexion allowed in configuration file
         */
        if (1 === count($connections)) {
            $selectedConnection = end($connections);
        }

        /**
         * Many connexions allowed in the configuration file,
         * we need to use get the connection option value.
         */
        if (null === $selectedConnection && 1 < count($connections)) {
            $connectionOption = $this->input->getOption('connection', null);
            if (null === $connectionOption) {
                throw new \Exception (
                    sprintf('You must specify a connection id to use')
                );
            }
            if (!in_array($connectionOption, array_keys($connections))) {
                throw new \Exception (
                    sprintf('Connection id "%s" not found', $connectionOption)
                );
            }
            $selectedConnection = $connections[$connectionOption];
        }

        $connectionParams = [
            'dbname' => $selectedConnection['database'],
            'user' => $selectedConnection['user'],
            'password' => $selectedConnection['password'],
            'host' => $selectedConnection['host'],
            'driver' => $selectedConnection['driver']
        ];

        $this->connectionParams = $connectionParams;

        return DriverManager::getConnection($connectionParams, new Configuration());
    }

    protected function wln($string, $level = 0)
    {
        $string = str_pad($string, strlen($string) + 4 * $level, " ", STR_PAD_LEFT);
        $this->output->writeln($string);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function renderJson(array $data = array())
    {
        $result = json_encode($data, 0, 99999);

        if (null === $result) {
            throw new \Exception(
                sprintf('There is an error in your JSON data: %s-%s', json_last_error(), json_last_error_msg())
            );
        }

        echo $result;
    }

    protected function renderText(array $data = array())
    {
        throw new \Exception('You must override this method');
    }

    protected function render(array $data = array(), $type = 'text')
    {
        if ('text' == $type) {
            $this->renderText($data);
        } else {
            $this->renderJson($data);
        }
    }

}
