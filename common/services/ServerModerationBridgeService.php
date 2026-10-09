<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    Server,
    ServerModerationBridge
};
use Yii;
use yii\web\UnauthorizedHttpException;

final class ServerModerationBridgeService
{
    public const TOKEN_PREFIX = 'htb_';

    public function rotateToken(
        Server $server
    ): string
    {
        $token = self::TOKEN_PREFIX
            . rtrim(
                strtr(
                    base64_encode(random_bytes(36)),
                    '+/',
                    '-_'
                ),
                '='
            );

        $now = date('Y-m-d H:i:s');

        $bridge = ServerModerationBridge::findOne([
            'server_id' => $server->id,
        ]);

        if(!$bridge)
        {
            $bridge = new ServerModerationBridge([
                'server_id' => $server->id,
                'enabled' => 1,
                'created_at' => $now,
            ]);
        }

        $bridge->token_hash = hash('sha256', $token);
        $bridge->token_hint = substr($token, 0, 8)
            . '…'
            . substr($token, -6);
        $bridge->rotated_at = $now;
        $bridge->updated_at = $now;

        if(!$bridge->save())
        {
            throw new \RuntimeException(
                'Не удалось сохранить токен Moderation Bridge.'
            );
        }

        return $token;
    }

    public function setEnabled(
        Server $server,
        bool $enabled
    ): ServerModerationBridge
    {
        $bridge = ServerModerationBridge::findOne([
            'server_id' => $server->id,
        ]);

        if(!$bridge)
        {
            throw new \DomainException(
                'Сначала выпустите токен Moderation Bridge.'
            );
        }

        $bridge->enabled = $enabled ? 1 : 0;
        $bridge->updated_at = date('Y-m-d H:i:s');

        if(!$bridge->save(false, [
            'enabled',
            'updated_at',
        ]))
        {
            throw new \RuntimeException(
                'Не удалось изменить состояние Moderation Bridge.'
            );
        }

        return $bridge;
    }

    public function authenticate(
        string $serverCode,
        string $authorization,
        ?string $ipAddress = null
    ): ServerModerationBridge
    {
        $serverCode = trim($serverCode);

        if(
            $serverCode === ''
            || !preg_match(
                '/^Bearer\s+(.+)$/i',
                trim($authorization),
                $matches
            )
        )
        {
            throw new UnauthorizedHttpException(
                'Invalid bridge credentials.'
            );
        }

        $token = trim((string)$matches[1]);

        if(
            !str_starts_with(
                $token,
                self::TOKEN_PREFIX
            )
            || strlen($token) < 32
        )
        {
            throw new UnauthorizedHttpException(
                'Invalid bridge credentials.'
            );
        }

        $server = Server::findOne([
            'code' => $serverCode,
            'enabled' => 1,
        ]);

        if(!$server)
        {
            throw new UnauthorizedHttpException(
                'Invalid bridge credentials.'
            );
        }

        $bridge = ServerModerationBridge::findOne([
            'server_id' => $server->id,
            'enabled' => 1,
        ]);

        if(
            !$bridge
            || !hash_equals(
                (string)$bridge->token_hash,
                hash('sha256', $token)
            )
        )
        {
            throw new UnauthorizedHttpException(
                'Invalid bridge credentials.'
            );
        }

        $this->touchUsage(
            $bridge,
            $ipAddress
        );

        return $bridge;
    }

    public function heartbeat(
        ServerModerationBridge $bridge,
        array $payload,
        ?string $ipAddress = null
    ): void
    {
        $now = date('Y-m-d H:i:s');

        $revision = max(
            0,
            (int)($payload['revision'] ?? 0)
        );

        $cachedActions = max(
            0,
            (int)($payload['cached_actions'] ?? 0)
        );

        $onlinePlayers = max(
            0,
            (int)($payload['online_players'] ?? 0)
        );

        $bridgeVersion = $this->limitedString(
            $payload['bridge_version'] ?? null,
            64
        );

        $minecraftVersion = $this->limitedString(
            $payload['minecraft_version'] ?? null,
            32
        );

        $forgeVersion = $this->limitedString(
            $payload['forge_version'] ?? null,
            64
        );

        $lastError = trim(
            (string)($payload['last_error'] ?? '')
        );

        if(mb_strlen($lastError) > 4000)
        {
            $lastError = mb_substr(
                $lastError,
                0,
                4000
            );
        }

        $syncOk = filter_var(
            $payload['sync_ok'] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        $bridge->bridge_version = $bridgeVersion;
        $bridge->minecraft_version = $minecraftVersion;
        $bridge->forge_version = $forgeVersion;
        $bridge->last_revision = $revision;
        $bridge->cached_actions = $cachedActions;
        $bridge->online_players = $onlinePlayers;
        $bridge->last_heartbeat_at = $now;
        $bridge->last_seen_ip = $ipAddress;
        $bridge->last_error = $lastError !== ''
            ? $lastError
            : null;
        $bridge->updated_at = $now;

        if($syncOk !== false)
        {
            $bridge->last_sync_at = $now;
        }

        $bridge->save(false);
    }

    private function touchUsage(
        ServerModerationBridge $bridge,
        ?string $ipAddress
    ): void
    {
        $lastUsed = $bridge->last_used_at
            ? strtotime((string)$bridge->last_used_at)
            : 0;

        if($lastUsed > time() - 60)
        {
            return;
        }

        ServerModerationBridge::updateAll(
            [
                'last_used_at' => date('Y-m-d H:i:s'),
                'last_seen_ip' => $ipAddress,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'server_id' => $bridge->server_id,
            ]
        );

        $bridge->last_used_at =
            date('Y-m-d H:i:s');
        $bridge->last_seen_ip = $ipAddress;
    }

    private function limitedString(
        mixed $value,
        int $maxLength
    ): ?string
    {
        $value = trim((string)$value);

        if($value === '')
        {
            return null;
        }

        return mb_substr(
            $value,
            0,
            $maxLength
        );
    }
}
