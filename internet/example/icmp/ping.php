<?php

use internet\icmp\Icmp;

/**
 * please use root jurisdiction run example program, because ping use of RAW socket
 */

include_once dirname(dirname(__DIR__)) . '/icmp/Icmp.php'; // include class file

$ping = Icmp::Icmpping(); // instantiation class

echo $ping->ping('127.0.0.1'); // ping localhost