<?php

namespace Phizzl\MySql;


class ArgumentEscaper
{
    const MODE_WIN = "win";

    const MODE_LINUX = "linux";

    /**
     * @var string
     */
    private $mode;

    /**
     * ArgumentEscaper constructor.
     * @param string $mode
     */
    public function __construct($mode = "")
    {
        $this->mode = $mode ? $mode : $this->guessMode();
    }

    /**
     * @return string
     */
    private function guessMode()
    {
        return strtoupper(substr(PHP_OS, 3)) === "WIN" ? self::MODE_WIN : self::MODE_LINUX;
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return $this->mode === self::MODE_WIN
            ? addcslashes($string, '\\"')
            : escapeshellarg($string);
    }
}