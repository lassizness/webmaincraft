<?php

declare(strict_types=1);
namespace common\services;
use common\models\User;
final class DiscourseSsoService
{
    public function buildRedirect(User $u,string $sso,string $sig): string
    {
        $secret=(string)ht_env('FORUM_SSO_SECRET','');$url=rtrim((string)ht_env('FORUM_URL',''),'/');if($secret===''||$url==='')throw new \RuntimeException('Forum SSO is not configured');if(!hash_equals(hash_hmac('sha256',$sso,$secret),$sig))throw new \DomainException('Invalid SSO signature');$decoded=base64_decode($sso,true);if($decoded===false)throw new \DomainException('Invalid SSO payload');parse_str($decoded,$q);$nonce=$q['nonce']??null;if(!$nonce)throw new \DomainException('Missing nonce');$payload=http_build_query(['nonce'=>$nonce,'external_id'=>$u->uuid,'email'=>$u->email,'username'=>$u->username,'name'=>$u->username,'require_activation'=>$u->email_verified?false:true]);$enc=base64_encode($payload);$outSig=hash_hmac('sha256',$enc,$secret);return $url.'/session/sso_login?sso='.rawurlencode($enc).'&sig='.$outSig;
    }
}
