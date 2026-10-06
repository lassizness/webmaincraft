<?php

declare(strict_types=1);

namespace common\services\voting;

use common\models\VoteService;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Request;

final class TopCraftVoteProvider implements VoteProviderInterface
{
    public function __construct(private readonly CallbackIpResolver $ipResolver = new CallbackIpResolver())
    {
    }

    public function buildVoteUrl(VoteService $service, string $playerName): string
    {
        if (preg_match('/^[A-Za-z0-9_]{3,16}$/', $playerName) !== 1) {
            throw new \InvalidArgumentException('Invalid Minecraft username.');
        }

        $url = trim((string)$service->vote_url_template);
        if ($url === '') {
            throw new \RuntimeException('TopCraft vote URL is not configured.');
        }

        // TopCraft can use a static project page. If a future URL supports a nickname
        // placeholder, the same driver can fill it without changing the UI or database.
        return str_replace('{username}', rawurlencode($playerName), $url);
    }

    public function parseCallback(VoteService $service, Request $request): VoteProviderResult
    {
        $secret = trim((string)$service->callback_secret);
        if ($secret === '') {
            throw new \RuntimeException('TopCraft callback secret is not configured.');
        }

        $playerName = trim((string)$this->param($request, ['nickname', 'username', 'nick']));
        if (preg_match('/^[A-Za-z0-9_]{3,16}$/', $playerName) !== 1) {
            throw new BadRequestHttpException('Invalid Minecraft username.');
        }

        $timestamp = trim((string)$this->param($request, ['timestamp', 'time']));
        if ($timestamp === '' || strlen($timestamp) > 64) {
            throw new BadRequestHttpException('Invalid vote timestamp.');
        }

        $signature = trim((string)$this->param($request, ['key', 'hash', 'signature']));
        $expected = sha1($playerName . $timestamp . $secret);
        if ($signature === '' || !hash_equals($expected, strtolower($signature))) {
            throw new ForbiddenHttpException('Invalid TopCraft callback signature.');
        }

        return new VoteProviderResult(
            playerName: $playerName,
            voterIp: null,
            providerVoteId: $signature,
            callbackIp: $this->ipResolver->resolve($request),
            payload: [
                'nickname' => $playerName,
                'timestamp' => $timestamp,
                'key' => $signature,
            ],
        );
    }

    private function param(Request $request, array $names): mixed
    {
        foreach ($names as $name) {
            $value = $request->post($name, null);
            if ($value !== null && $value !== '') {
                return $value;
            }

            $value = $request->get($name, null);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return '';
    }
}
