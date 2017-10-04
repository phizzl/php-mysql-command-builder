<?php

namespace Phizzl\MySqlCommandBuilder;


use Phizzl\PhpShellCommand\ShellCommand;
use Phizzl\PhpShellCommand\ShellCommandBuilder;

class MySqlDumpCommandBuilder extends AbstractDefinition
{
    const DUMP_MODE_INCLUDE = "incl";

    const DUMP_MODE_EXCLUDE = "excl";

    /**
     * @var
     */
    private $target;

    /**
     * @var bool
     */
    private $noCreateInfo;

    /**
     * @var bool
     */
    private $noData;

    /**
     * @var string
     */
    private $mysqldumpBin;

    /**
     * @var MySqlCommandBuilder
     */
    private $mysql;

    /**
     * @var string
     */
    private $dumpMode;

    /**
     * @var array
     */
    private $dumpModeTables;

    /**
     * @var bool
     */
    private $force;

    /**
     * MySqlDumpDefinition constructor.
     * @param string $dbname
     * @param string $target
     */
    public function __construct($dbname, $target)
    {
        parent::__construct($dbname);

        $this->target = $target;
        $this->noCreateInfo = false;
        $this->noData = false;
        $this->mysqldumpBin = "mysqldump";
        $this->dumpMode = self::DUMP_MODE_EXCLUDE;
        $this->dumpModeTables = [];
        $this->force = false;
        $this->mysql = new MySqlCommandBuilder($dbname);
    }

    /**
     * @param string $host
     * @return $this
     */
    public function host($host)
    {
        parent::host($host);
        $this->mysql->host($host);

        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function port($port)
    {
        parent::port($port);
        $this->mysql->port($port);

        return $this;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function user($user)
    {
        parent::user($user);
        $this->mysql->user($user);

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function password($password)
    {
        parent::password($password);
        $this->mysql->password($password);

        return $this;
    }

    /**
     * @param string $bin
     * @return $this
     */
    public function mysqldumpBin($bin)
    {
        $this->mysqldumpBin = $bin;
        return $this;
    }

    /**
     * @param string $bin
     * @return $this
     */
    public function mysqlBin($bin)
    {
        $this->mysql->mysqlBin($bin);
        return $this;
    }

    /**
     * @return $this
     */
    public function noCreateInfo()
    {
        $this->noCreateInfo = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function noData()
    {
        $this->noData = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function dumpModeInclude()
    {
        $this->dumpMode = self::DUMP_MODE_INCLUDE;
        return $this;
    }

    /**
     * @return $this
     */
    public function dumpModeExclude()
    {
        $this->dumpMode = self::DUMP_MODE_EXCLUDE;
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
     * @param string $table
     * @return $this
     */
    public function addTable($table)
    {
        $this->dumpModeTables[$table] = $table;
        return $this;
    }

    /**
     * @param array $table
     * @return $this
     */
    public function addTables(array $tables)
    {
        foreach($tables as $table){
            $this->addTable($table);
        }
        return $this;
    }

    /**
     * @return ShellCommand
     */
    public function getCommand()
    {
        $cmdBuilder = new ShellCommandBuilder($this->mysqldumpBin);

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

        if($this->noCreateInfo){
            $cmdBuilder->addOption('--no-create-info');
        }

        if($this->noData){
            $cmdBuilder->addOption('--no-data');
        }

        $cmdBuilder->addArgument($this->dbname);

        if(count($this->dumpModeTables)){
            $cmdBuilder->addOption("\$(" . $this->getSubFilterTableCommand()->getCommand() . ")");
        }

        $cmdBuilder->redirectOutputTo($this->target);

        return $cmdBuilder->buildCommand();
    }

    /**
     * @return ShellCommand
     */
    private function getSubFilterTableCommand()
    {
        $statements = $this->getTableStatements();
        $sql = "SHOW TABLES FROM {$this->dbname} WHERE 1";
        $not = $this->dumpMode === self::DUMP_MODE_EXCLUDE ? "NOT" : "";

        if(count($statements['list'])){
            $sql .= " AND Tables_in_{$this->dbname} {$not} IN(" . implode(', ', $statements['list']) . ")";
        }

        if(count($statements['likes'])){
            foreach($statements['likes'] as $like){
                $sql .= " AND Tables_in_{$this->dbname} {$not} LIKE {$like}";
            }
        }

        $this->mysql->query($sql);

        return $this->mysql->getCommand();
    }

    /**
     * @param array $items
     * @return array
     */
    private function getTableStatements()
    {
        $list = [];
        $likes = [];

        foreach($this->dumpModeTables as $item){
            $item = str_replace('*', '%', $item);
            $item = "\"{$item}\"";
            if(strpos($item, '%') === false){
                $list[] = $item;
            }
            else{
                $item = str_replace("_", "\\_", $item);
                $likes[] = $item;
            }
        }

        return ['list' => $list, 'likes' => $likes];
    }
}