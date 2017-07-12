<?php


namespace Phizzl\MySqlCommandBuilder;


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
     * @return string
     */
    public function getShellCommand()
    {
        $command = "{$this->mysqlBin} -B -N";

        if($this->host !== ""){
            $command .= " --host=" . $this->escaper->escape($this->host);
        }

        if($this->port > 0){
            $command .= " --port=" . $this->escaper->escape($this->port);
        }

        if($this->user !== ""){
            $command .= " --user=" . $this->escaper->escape($this->user);
        }

        if($this->password !== ""){
            $command .= " --password=" . $this->escaper->escape($this->password);
        }

        if($this->query !== ""){
            $command .= " -e " . $this->escaper->escape($this->query);
        }

        if($this->arguments !== ""){
            $command .= " {$this->arguments}";
        }

        return $command;
    }
}