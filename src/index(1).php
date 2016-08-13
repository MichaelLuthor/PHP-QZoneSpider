<?php
use MichaelLuthor\QzoneSpider\Spider;
require 'QzoneSpider/Spider.php';
$spider = new Spider('568109749', '', array(
    '975462080',
    '654302423',
    '260014426',
), array(
    'name' => 'mysql',
    'host' => 'localhost',
    'dbname' => 'qzone',
    'user' => 'root',
    'password' => '',
));
$spider->run();