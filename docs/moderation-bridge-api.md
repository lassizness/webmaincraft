# HiveTech Moderation Bridge API v1

The Moderation Bridge API is a server-to-server API used by the
HiveTechModeration Forge mod.

## Authentication

Every Minecraft server has its own token.

Required headers:

```http
Authorization: Bearer htb_<token>
X-HiveTech-Server: <server-code>
```

The clear-text token is shown only once in the admin panel when it is created
or rotated. Only its SHA-256 hash is stored in the database.

A server credential can read only global moderation state plus moderation
state assigned to that server.

## Base URL

```text
/internal/game
```

The complete URL is based on `APP_URL`.

## GET /moderation/snapshot

Returns the current active ban/mute state for the authenticated server and a
revision representing the moderation history at the same database snapshot.

Example:

```json
{
  "api_version": 1,
  "server": {
    "id": 1,
    "code": "main"
  },
  "generated_at": "2026-10-09T11:00:00+05:00",
  "revision": 1842,
  "actions": [
    {
      "id": 151,
      "uuid": "00000000-0000-0000-0000-000000000001",
      "username": "Player",
      "type": "ban",
      "scope": "global",
      "server_code": null,
      "reason": "Читы",
      "starts_at": "2026-10-09T10:00:00+05:00",
      "expires_at": "2026-10-12T10:00:00+05:00",
      "status": "active"
    }
  ]
}
```

Only `ban` and `mute` are returned. Internal moderator comments and staff
data are never exposed.

## GET /moderation/changes?after=<revision>&limit=200

Returns moderation history changes after a revision.

Maximum limit: 500.

Operations:

- `apply`: a ban/mute was created.
- `reverse`: a ban/mute was revoked or expired.
- `noop`: historical create event is already inactive and must not be replayed.
- `refresh`: reserved for future moderation event types.

Example:

```json
{
  "api_version": 1,
  "server": {
    "id": 1,
    "code": "main"
  },
  "generated_at": "2026-10-09T11:00:03+05:00",
  "revision": 1844,
  "current_revision": 1844,
  "has_more": false,
  "changes": [
    {
      "revision": 1843,
      "operation": "apply",
      "event_type": "created",
      "id": 152,
      "uuid": "00000000-0000-0000-0000-000000000002",
      "username": "OtherPlayer",
      "type": "mute",
      "scope": "server",
      "server_code": "main",
      "reason": "Спам",
      "starts_at": "2026-10-09T11:00:01+05:00",
      "expires_at": "2026-10-09T12:00:01+05:00",
      "status": "active"
    }
  ]
}
```

The client must ignore `noop` events and should apply `apply` only while the returned action is still active and not expired.

The client should persist the returned `revision`. When `has_more=true`,
request the next page immediately using the returned revision.

## GET /moderation/player/{uuid}

Returns current active ban/mute state for one player, scoped to the
authenticated Minecraft server.

This endpoint is intended for an additional live check during player login.

## POST /bridge/heartbeat

Request body:

```json
{
  "bridge_version": "1.0.0",
  "minecraft_version": "1.12.2",
  "forge_version": "14.23.5.2860",
  "revision": 1844,
  "cached_actions": 17,
  "online_players": 42,
  "sync_ok": true,
  "last_error": ""
}
```

The server stores the latest heartbeat and exposes it in the admin panel.

Response includes the latest known moderation revision:

```json
{
  "api_version": 1,
  "server": {
    "id": 1,
    "code": "main"
  },
  "generated_at": "2026-10-09T11:01:00+05:00",
  "accepted": true,
  "current_revision": 1844
}
```

## Bridge behaviour

Recommended client behaviour:

1. Load local disk cache.
2. Request snapshot during startup.
3. Poll changes every 3 seconds.
4. Persist cache and revision after successful updates.
5. Perform player lookup during login when the API is reachable.
6. Continue enforcing cached punishments when the API is unavailable.
7. Expire cached actions locally according to `expires_at`.
8. Fail open for an unknown player when both API and cache have no ban.
9. Send heartbeat every 60 seconds.
