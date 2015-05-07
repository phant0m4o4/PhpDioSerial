<?php
include "vendor/autoload.php";
$connect= \PHPDioSerial\Serial::connect('COM1');
$connect->write('hello',3);
echo$connect->read();
