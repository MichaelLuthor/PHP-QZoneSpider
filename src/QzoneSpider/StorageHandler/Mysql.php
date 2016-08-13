<?php
namespace MichaelLuthor\QzoneSpider\StorageHandler;
use MichaelLuthor\QzoneSpider\Library\DataStorageHandler;
use MichaelLuthor\QzoneSpider\Library\Util;
class Mysql extends DataStorageHandler {
    /**
     * @var \PDO
     */
    private $db = null;
    
    /**
     * {@inheritDoc}
     * @see \MichaelLuthor\QzoneSpider\Library\DataStorageHandler::init()
     */
    protected function init() {
        $this->db = new \PDO(
            sprintf('mysql:host=%s', $this->getConfig('host')),
            $this->getConfig('user'), 
            $this->getConfig('password')
        );
        
        $dbname = $this->getConfig('dbname');
        $this->db->exec(sprintf("USE %s", $dbname));
        if ('00000' !== $this->db->errorCode()) {
            $this->createDatabase();
            $this->db->exec(sprintf("USE %s", $dbname));
            if ('00000' !== $this->db->errorCode()) {
                throw new \Exception("Unable to use database '$dbname'.");
            }
        }
        $this->db->exec('SET NAMES UTF8');
    }
    
    /**
     * {@inheritDoc}
     * @see \MichaelLuthor\QzoneSpider\Library\DataStorageHandler::save()
     */
    public function save($category, $data) {
        $data = Util::arrayMerge(array('id'=>''), $data);
        $table = $category;
        if ( !$this->isTableExists($table) ) {
            $this->createTable($table);
        }
        foreach ( $data as $key => $value ) {
            $data[$key] = $this->db->quote($value);
        }
        $data = implode(',', $data);
        $statement = "INSERT INTO $table VALUES ($data)";
        $this->db->exec($statement);
        if ( '00000' !== $this->db->errorCode() ) {
            throw new \Exception('Database error.');
        }
    }
    
    /**
     * @param unknown $table
     */
    private function isTableExists( $table ) {
        $result = $this->db->query("SELECT id FROM $table LIMIT 1");
        $result->fetchAll();
        return '00000' === $this->db->errorCode();
    }
    
    /**
     * @param unknown $table
     */
    private function createTable( $table ) {
        $query = $this->loadTableCreateSQL('profile');
        $this->db->exec($query);
        if ( '00000' !== $this->db->errorCode() ) {
            throw new \Exception("Unable to create table '$table'.");
        }
    }
    
    /**
     * @return void
     */
    private function createDatabase() {
        $query = 'CREATE DATABASE %s DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        $dbname = $this->getConfig('dbname');
        $this->db->exec(sprintf($query, $dbname));
        if ('00000' !== $this->db->errorCode()) {
            throw new \Exception("Unable to create database '$dbname'.");
        }
    }
    
    /**
     * @param unknown $name
     */
    private function loadTableCreateSQL( $name ) {
        $path = sprintf('%s/../Data/MysqlTableSQLQuery/%s.sql', dirname(__FILE__), $name);
        if ( !file_exists($path) ) {
            throw new \Exception("Unable to load table query for '$name'.");
        }
        return file_get_contents($path);
    }
}