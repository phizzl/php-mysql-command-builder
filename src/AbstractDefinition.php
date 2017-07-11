<?php


namespace Phizzl\MySql;


class AbstractDefinition
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $dbname;

    /**
     * @var ArgumentEscaper
     */
    protected $escaper;

    /**
     * AbstractDefinition constructor.
     * @param string $dbname
     */
    public function __construct($dbname)
    {
        $this->dbname = $dbname;
        $this->escaper = new ArgumentEscaper();
        $this->host = "";
        $this->port = 0;
        $this->user = "";
        $this->password = "";
    }

    /**
     * @param string $host
     * @return $this
     */
    public function host($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function port($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function password($password)
    {
        $this->password = $password;
        return $this;
    }
}