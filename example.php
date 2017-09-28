<?php


use Phizzl\MySqlCommandBuilder\MySqlDumpCommandBuilder;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$dumpDefinition = new MySqlDumpCommandBuilder("testdb", "dump.sql");

$dumpDefinition
    ->user("myuser")
    ->password("mypass")
    ->host("127.0.0.1")
    ->port(3306)
    ->noData()
    ->noCreateInfo()
    ->addTable('oxv_*')
    ->addTable('testtable');

echo $dumpDefinition->getCommand()->getCommand();