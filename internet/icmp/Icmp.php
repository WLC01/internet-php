<?php

namespace internet\icmp;

include_once dirname(dirname(__FILE__)) . '/autoload.php';

abstract class Icmp
{
    const VERSION = '0.0.1'; // version

    const REALIZATION = ['ping']; // based ICMP protocol realization of function

    private static $container = [
        'icmpping' => ['path' => Icmpping::class, 'obj' => null, 'is_new' => false]
    ];

    private static function make($class, ...$argv) : Icmp
    {
        self::check_run_env();

        $lower_class = strtolower($class);

        if(empty(self::$container[$lower_class]))
        {
            return 'not found class' . $class;
        }

        if(self::$container[$lower_class]['is_new'])
        {
            return self::$container[$lower_class]['obj'];
        }

        self::$container[$lower_class]['is_new'] = true;
        self::$container[$lower_class]['obj']    = new self::$container[$lower_class]['path']($argv);

        return self::$container[$lower_class]['obj'];
    }

    private static function check_run_env()
    {
        if(PHP_SAPI != 'cli')
        {
            die('Only the CLI environment is allowed to run programs');
        }
    }

    final public function checksum() : string
    {
        strlen($this->packet) % 2 && $this->packet .= "\x00";

        $sum = array_sum(unpack('n*', $this->packet));

        return pack('n*', ~(($sum = ($sum >> 16) + ($sum & 0xffff)) + ($sum >> 16)));
    }

    public static function __callStatic($class, $argv)
    {
        return forward_static_call('static::make', $class, $argv);
    }
}
