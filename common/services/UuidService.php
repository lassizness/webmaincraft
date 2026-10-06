<?php

declare(strict_types=1);
namespace common\services;
final class UuidService
{
    public static function v4(): string
    {
        $d=random_bytes(16); $d[6]=chr((ord($d[6])&0x0f)|0x40); $d[8]=chr((ord($d[8])&0x3f)|0x80);
        $h=bin2hex($d); return sprintf('%s-%s-%s-%s-%s',substr($h,0,8),substr($h,8,4),substr($h,12,4),substr($h,16,4),substr($h,20,12));
    }
}
