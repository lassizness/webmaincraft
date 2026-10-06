<?php

declare(strict_types=1);

namespace common\services\voting;

use common\models\VoteService;
use yii\web\Request;

interface VoteProviderInterface
{
    public function buildVoteUrl(VoteService $service, string $playerName): string;

    public function parseCallback(VoteService $service, Request $request): VoteProviderResult;
}
