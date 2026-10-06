<?php

declare(strict_types=1);
namespace common\services;
final class RconClient
{
    private $socket=null; private int $id=0;
    public function __construct(private string $host,private int $port,private string $password,private float $timeout=3.0){}
    public function connect(): void { $errno=0;$err='';$this->socket=@fsockopen($this->host,$this->port,$errno,$err,$this->timeout);if(!$this->socket)throw new \RuntimeException("RCON connect failed: $err ($errno)");stream_set_timeout($this->socket,(int)ceil($this->timeout));$r=$this->request(3,$this->password);if($r['id']===-1)throw new \RuntimeException('RCON auth failed'); }
    public function command(string $command): string { if(!$this->socket)$this->connect();return $this->request(2,$command)['body']; }
    private function request(int $type,string $body): array { $id=++$this->id;$payload=pack('VV',$id,$type).$body."\0\0";$packet=pack('V',strlen($payload)).$payload;fwrite($this->socket,$packet);$lenRaw=fread($this->socket,4);if(strlen($lenRaw)!==4)throw new \RuntimeException('RCON read failed');$len=unpack('V',$lenRaw)[1];$data='';while(strlen($data)<$len){$chunk=fread($this->socket,$len-strlen($data));if($chunk===false||$chunk==='')break;$data.=$chunk;}if(strlen($data)<10)throw new \RuntimeException('RCON response invalid');$h=unpack('Vid/Vtype',substr($data,0,8));return ['id'=>(int)$h['id'],'type'=>(int)$h['type'],'body'=>rtrim(substr($data,8),"\0")]; }
    public function __destruct(){if(is_resource($this->socket))fclose($this->socket);}
}
