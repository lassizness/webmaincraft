<?php

declare(strict_types=1);
namespace common\services;
use common\models\{DeliveryQueue,Server,User};
final class DeliveryService
{
    public function process(DeliveryQueue $q): void
    {
        $u=User::findOne($q->user_id);if(!$u)throw new \RuntimeException('User not found');if($q->delivery_type==='none'){return;}if($q->delivery_type!=='rcon_command'&&$q->delivery_type!=='permission'&&$q->delivery_type!=='cosmetic')throw new \RuntimeException('Unsupported delivery type');$s=Server::findOne($q->server_id);if(!$s)throw new \RuntimeException('Server not found');$prefix=$s->rcon_env_key?:'MAIN';$host=(string)ht_env('MINECRAFT_RCON_'.$prefix.'_HOST',$s->host);$port=(int)ht_env('MINECRAFT_RCON_'.$prefix.'_PORT',25575);$pass=(string)ht_env('MINECRAFT_RCON_'.$prefix.'_PASSWORD','');if($pass==='')throw new \RuntimeException('RCON password is not configured');$command=strtr((string)$q->payload,['{username}'=>$u->username,'{uuid}'=>$u->uuid]);(new RconClient($host,$port,$pass))->command($command);
    }
}
