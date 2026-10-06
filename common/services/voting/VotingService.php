<?php

declare(strict_types=1);

namespace common\services\voting;

use common\models\{User, Vote, VoteService};
use common\services\WalletService;
use Yii;
use yii\db\IntegrityException;

final class VotingService
{
    public function __construct(
        private readonly VoteProviderRegistry $providers = new VoteProviderRegistry(),
    ) {
    }

    public function voteUrl(VoteService $service, string $playerName): string
    {
        $user = User::findActiveByUsername($playerName);
        if ($user === null) {
            throw new \DomainException('Minecraft player does not exist.');
        }

        $provider = $this->providers->get((string)$service->driver);
        return $provider->buildVoteUrl($service, (string)$user->username);
    }

    /**
     * Returns true when a reward was issued, false when the service cooldown is still active.
     */
    public function acceptCallback(VoteService $service): bool
    {
        $provider = $this->providers->get((string)$service->driver);
        $callback = $provider->parseCallback($service, Yii::$app->request);
        $user = User::findActiveByUsername($callback->playerName);

        if ($user === null) {
            throw new \DomainException('Vote player does not exist.');
        }

        $userId = (int)$user->id;
        $now = time();
        $cooldown = max(1, (int)$service->reward_cooldown_seconds);
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();

        try {
            // Serialize rewards per user through the existing wallet row. This keeps
            // concurrent callbacks from issuing the same reward twice.
            $wallet = $db->createCommand(
                'SELECT id FROM {{%wallets}} WHERE user_id=:uid FOR UPDATE',
                [':uid' => $userId]
            )->queryOne();

            if (!$wallet) {
                try {
                    $db->createCommand()->insert('{{%wallets}}', [
                        'user_id' => $userId,
                        'balance' => 0,
                    ])->execute();
                } catch (IntegrityException) {
                    // Another request may have created it between SELECT and INSERT.
                }

                $wallet = $db->createCommand(
                    'SELECT id FROM {{%wallets}} WHERE user_id=:uid FOR UPDATE',
                    [':uid' => $userId]
                )->queryOne();
            }

            if (!$wallet) {
                throw new \RuntimeException('Wallet row could not be locked.');
            }

            $lastRewardedAt = $db->createCommand(
                'SELECT rewarded_at
                 FROM {{%votes}}
                 WHERE service_id=:service_id
                   AND user_id=:user_id
                   AND rewarded_at IS NOT NULL
                 ORDER BY rewarded_at DESC, id DESC
                 LIMIT 1',
                [
                    ':service_id' => (int)$service->id,
                    ':user_id' => $userId,
                ]
            )->queryScalar();

            if ($lastRewardedAt !== false && $lastRewardedAt !== null) {
                $last = strtotime((string)$lastRewardedAt);
                if ($last !== false && ($last + $cooldown) > $now) {
                    $tx->commit();
                    return false;
                }
            }

            if ($callback->providerVoteId !== null) {
                $duplicateProviderVote = Vote::find()->where([
                    'service_id' => (int)$service->id,
                    'provider_vote_id' => $callback->providerVoteId,
                ])->exists();
                if ($duplicateProviderVote) {
                    $tx->commit();
                    return false;
                }
            }

            $vote = new Vote([
                'service_id' => (int)$service->id,
                'user_id' => $userId,
                'vote_date' => date('Y-m-d', $now),
                'voter_ip' => $callback->voterIp,
                'callback_ip' => $callback->callbackIp,
                'provider_vote_id' => $callback->providerVoteId,
                'reward_amount' => (float)$service->reward_amount,
                'callback_payload' => json_encode(
                    $callback->payload,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ),
                'created_at' => date('Y-m-d H:i:s', $now),
            ]);

            if (!$vote->save()) {
                throw new \RuntimeException('Vote record could not be saved.');
            }

            (new WalletService())->credit(
                $userId,
                (float)$service->reward_amount,
                'vote',
                'Голосование: ' . (string)$service->name,
                'vote',
                (int)$vote->id
            );

            $vote->rewarded_at = date('Y-m-d H:i:s', $now);
            if (!$vote->save(false, ['rewarded_at'])) {
                throw new \RuntimeException('Vote reward state could not be saved.');
            }

            $tx->commit();
            return true;
        } catch (IntegrityException $e) {
            if ($tx->isActive) {
                $tx->rollBack();
            }

            if ($callback->providerVoteId !== null && Vote::find()->where([
                'service_id' => (int)$service->id,
                'provider_vote_id' => $callback->providerVoteId,
            ])->exists()) {
                return false;
            }

            throw $e;
        } catch (\Throwable $e) {
            if ($tx->isActive) {
                $tx->rollBack();
            }
            throw $e;
        }
    }
}
