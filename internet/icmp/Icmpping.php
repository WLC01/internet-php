<?php

namespace internet\icmp;

use internet\icmp\Icmp;

/**
 * @see https://www.rfc-editor.org/rfc/rfc792
 * @see https://www.thegeekstuff.com/2012/05/ip-header-checksum/
 *
 * 这是一个基于ICMP协议实现的ping功能，ICMP对应的RFC文档为：RFC792。协议帧如下:
 ***********************************************
 * type(8bit) * code(8bit) * checksum(16bit)
 ***********************************************
 *          id(8bit)  *  sequence(8bit)
 ***********************************************
 *          content (optional)
 ***********************************************
 */

class Icmpping extends Icmp
{
    protected $id;

    protected $type;

    protected $code;

    protected $packet; // data frame container

    protected $sequence;

    protected $checksum;

    public function __construct()
    {
        $this->type = chr(8) . chr(0);
        $this->code = chr(0) . chr(0);
        $this->id   = chr(0) . chr(0);
        $this->checksum = chr(0) . chr(0);
        $this->sequence = chr(0) . chr(0);
    }

    /**
     * @access public
     * @param string $addr ping主机地址
     * @param string $content 可选的数据
     * @return string
     */
    public function ping(string $addr, string $content = '') : string
    {
        echo sprintf("PING %s (%s) %s bytes of data.\n", $addr, gethostbyname($addr), PHP_OS == 'Linux' ? '56(84)' : (PHP_OS == 'Windows' ? '32' : 'unknown'));

        $this->packet = $this->type . $this->code . $this->checksum. $this->id . $this->sequence . $content;

        $this->packet = $this->type . $this->code . $this->checksum(). $this->id . $this->sequence . $content;

        $begin = microtime(true);

        $socket = socket_create(AF_INET, SOCK_RAW, getprotobyname('icmp'));
        if(!$socket)
        {
            return 'create socket handler error: ' . socket_strerror(socket_last_error()) . "\n";
        }

        if(!socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]))
        {
            return 'set socket error: ' . socket_strerror(socket_last_error()) . "\n";
        }

        if(!socket_connect($socket, $addr, null))
        {
            return 'connect socket error: ' . socket_strerror(socket_last_error()) . "\n";
        }

        if(!socket_send($socket, $this->packet, strlen($this->packet), 0))
        {
            return 'send error: ' . socket_strerror(socket_last_error()) . "\n";
        }

        if($response = socket_read($socket, 65535))
        {
            $result = unpack('C*', $response);

            return sprintf("%d bytes from %s: icmp_seq=%s  ttl=%s time=%0.3f ms\n", count($result) - 20, $addr, $result[28],
                $result[9], (microtime(true) - $begin) * 1000);
        }
        else
        {
            return 'read error: ' . socket_strerror(socket_last_error()) . "\n";
        }

        socket_close($socket);
    }
}


