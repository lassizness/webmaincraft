<?php

declare(strict_types=1);

namespace common\services\voting;

use common\models\VoteService;
use yii\web\BadRequestHttpException;
use yii\web\Request;

final class TopGVoteProvider implements VoteProviderInterface
{
    public function __construct(private readonly CallbackIpResolver $ipResolver = new CallbackIpResolver())
    {
    }

    public function buildVoteUrl(VoteService $service, string $playerName): string
    {
        if (preg_match('/^[A-Za-z0-9_]{3,16}$/', $playerName) !== 1) {
            throw new \InvalidArgumentException('Invalid Minecraft username.');
        }

        if (!str_contains((string)$service->vote_url_template, '{username}')) {
            throw new \RuntimeException('Vote URL template must contain {username}.');
        }

        return str_replace('{username}', rawurlencode($playerName), (string)$service->vote_url_template);
    }

    public function parseCallback(VoteService $service, Request $request): VoteProviderResult
    {
        $callbackIp = $this->ipResolver->resolve($request);

        $playerName = trim((string)$request->get('p_resp', ''));
        if (preg_match('/^[A-Za-z0-9_]{3,16}$/', $playerName) !== 1) {
            throw new BadRequestHttpException('Invalid Minecraft username.');
        }

        $voterIp = trim((string)$request->get('ip', ''));
        if ($voterIp !== '' && filter_var($voterIp, FILTER_VALIDATE_IP) === false) {
            $voterIp = null;
        }

        return new VoteProviderResult(
            playerName: $playerName,
            voterIp: $voterIp !== '' ? $voterIp : null,
            providerVoteId: null,
            callbackIp: $callbackIp,
            payload: [
                'p_resp' => $playerName,
                'ip' => $voterIp,
            ],
        );
    }

}
