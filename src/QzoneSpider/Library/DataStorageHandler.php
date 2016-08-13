<?php
namespace MichaelLuthor\QzoneSpider\Library;
abstract class DataStorageHandler {
    private $config = array();
    public function __construct( $config=array() ) {
        $this->config = $config;
        $this->init();
    }
    public function getConfig( $name, $default=null ) {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }
    protected function init() {}
    abstract public function save($category, $data);
}