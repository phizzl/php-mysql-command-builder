<?php

namespace Phizzl\MySqlCommandBuilder;


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
        $this->escaper = new ArgumentEscaper();
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
        $this->mysql->host($host);
        return parent::host($host);
    }

    /**
     * @param int $port
     * @return $this
     */
    public function port($port)
    {
        $this->mysql->port($port);
        return parent::port($port);
    }

    /**
     * @param string $user
     * @return $this
     */
    public function user($user)
    {
        $this->mysql->user($user);
        return parent::user($user);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function password($password)
    {
        $this->mysql->password($password);
        return parent::password($password);
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
     * @return string
     */
    public function getCommand()
    {
        $command = $this->mysqldumpBin;

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

        if($this->noCreateInfo){
            $command .= " --no-create-info";
        }

        if($this->noData){
            $command .= " --no-data";
        }

        $command .= " {$this->dbname}";

        if(count($this->dumpModeTables)){
            $command .= " \$(" . $this->getSubFilterTableCommand() . ")";
        }

        $command .= " > {$this->target}";

        return $command;
    }

    /**
     * @return string
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
                $likes[] = $item;
            }
        }

        return ['list' => $list, 'likes' => $likes];
    }
}