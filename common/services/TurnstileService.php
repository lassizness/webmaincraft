<?php

declare(strict_types=1);
namespace common\services;
use Yii;use yii\httpclient\Client;
final class TurnstileService
{
    public function verify(?string $token,?string $ip): bool
    {
        $secret=(string)ht_env('TURNSTILE_SECRET','');if($secret==='')return true;if(!$token)return false;try{$r=(new Client())->post('https://challenges.cloudflare.com/turnstile/v0/siteverify',['secret'=>$secret,'response'=>$token,'remoteip'=>$ip])->send();return (bool)($r->data['success']??false);}catch(\Throwable){return false;}
    }
}
