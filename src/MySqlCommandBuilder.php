<?php


namespace Phizzl\MySqlCommandBuilder;


use Phizzl\PhpShellCommand\ShellCommand;
use Phizzl\PhpShellCommand\ShellCommandBuilder;

class MySqlCommandBuilder extends AbstractDefinition
{
    /**
     * @var string
     */
    private $mysqlBin;

    /**
     * @var string
     */
    private $query;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var string
     */
    private $arguments;

    /**
     * @var string
     */
    private $readFromFile;

    /**
     * MySqlDefinition constructor.
     * @param string $dbname
     */
    public function __construct($dbname)
    {
        parent::__construct($dbname);
        $this->mysqlBin = "mysql";
        $this->query = "";
        $this->force = false;
        $this->arguments = "";
        $this->readFromFile = "";
    }

    /**
     * @param string $bin
     * @return $this
     */
    public function mysqlBin($bin)
    {
        $this->mysqlBin = $bin;
        return $this;
    }

    /**
     * @return $this
     */
    public function force()
    {
        $this->force = true;
        return $this;
    }

    /**
     * @param string $arguments
     * @return $this
     */
    public function arguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param string $readFromFile
     * @return $this
     */
    public function readFromFile($readFromFile)
    {
        $this->importDump = $readFromFile;
        return $this;
    }

    /**
     * @return ShellCommand
     */
    public function getCommand()
    {
        $cmdBuilder = new ShellCommandBuilder($this->mysqlBin);
        $cmdBuilder
            ->addOption('-B')
            ->addOption('-N');

        if($this->force){
            $cmdBuilder->addOption('--force');
        }

        if($this->host !== ""){
            $cmdBuilder->addOption('--host', $this->host);
        }

        if($this->port > 0){
            $cmdBuilder->addOption('--port', $this->port);
        }

        if($this->user !== ""){
            $cmdBuilder->addOption('--user', $this->user);
        }

        if($this->password !== ""){
            $cmdBuilder->addOption('--password', $this->password);
        }

        if($this->query !== ""){
            $cmdBuilder->addOption('-e', $this->query, ShellCommandBuilder::OPTION_ASSIGN_SPACE);
        }

        if($this->arguments !== ""){
            foreach(explode(" ", $this->arguments) as $arg){
                $cmdBuilder->addArgument($arg);
            }
        }

        $cmdBuilder->addArgument($this->dbname);

        if(strlen($this->readFromFile)){
            $cmdBuilder->readFromFile($this->readFromFile);
        }

        return $cmdBuilder->buildCommand();
    }
}