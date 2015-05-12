<?php

/**
 * Handles the socket communication with the cli program
 *
 * @author Stefan Fixl
 */
class SocketClient {

    private $socket = null;
    
    /**
     * Connects to the server socket
     * @param string $ipaddr default: localhost
     * @param int $port port number - default: 8085
     */
    public function connect($ipaddr = "127.0.0.1", $port = 8085){
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            echo "socket_create() fehlgeschlagen: Grund: " . socket_strerror(socket_last_error()) . "\n";
        }
       
        $result = socket_connect($this->socket, $ipaddr, $port);
        if ($result === false) {
            echo "socket_connect() fehlgeschlagen.\nGrund: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n";
        }
    }
    /**
     * Send a message to the server
     * @param string
     */
    public function write($string){
        socket_write($this->socket, $string, strlen($string));
    }
    /**
     * Wait for a message from the server
     * @return string
     */
    public function read(){
        $answer = "";
        while ($out = socket_read($this->socket, 2048)) {
            $answer.=$out;
        }
        return $answer;
    }
    /**
     * Close the socket
     */
    public function close(){
        socket_close($this->socket);
    }
}
