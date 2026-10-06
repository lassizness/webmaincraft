<?php

declare(strict_types=1);

namespace common\services;

final class MinecraftStatusService
{
    private const PROTOCOL_VERSION = 340; // Minecraft 1.12.2

    public function status(string $host, int $port, int $timeout = 3): array
    {
        $errno = 0;
        $errstr = '';

        $socket = @fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );

        if (!$socket) {
            return [
                'online' => false,
                'error' => $errstr ?: 'Connection failed',
            ];
        }

        stream_set_timeout($socket, $timeout);

        try {
            /*
             * Handshake packet:
             *
             * Packet ID        = 0
             * Protocol version = 340 (Minecraft 1.12.2)
             * Server address
             * Server port
             * Next state       = 1 (status)
             */

            $data =
                $this->varInt(0) .
                $this->varInt(self::PROTOCOL_VERSION) .
                $this->writeString($host) .
                pack('n', $port) .
                $this->varInt(1);

            $packet = $this->varInt(strlen($data)) . $data;

            $this->writeAll($socket, $packet);

            /*
             * Status Request
             *
             * length = 1
             * packet id = 0
             */
            $this->writeAll(
                $socket,
                $this->varInt(1) . $this->varInt(0)
            );

            // Response packet length
            $this->readVarInt($socket);

            // Response packet ID
            $packetId = $this->readVarInt($socket);

            if ($packetId !== 0) {
                throw new \RuntimeException(
                    'Unexpected packet ID: ' . $packetId
                );
            }

            // JSON string length
            $jsonLength = $this->readVarInt($socket);

            if ($jsonLength <= 0 || $jsonLength > 10 * 1024 * 1024) {
                throw new \RuntimeException(
                    'Invalid JSON length: ' . $jsonLength
                );
            }

            $json = $this->readExact($socket, $jsonLength);

            $data = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return [
                'online' => true,
                'players' => $data['players'] ?? null,
                'version' => $data['version'] ?? null,
                'description' => $data['description'] ?? null,
                'modinfo' => $data['modinfo'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'online' => false,
                'error' => $e->getMessage(),
            ];
        } finally {
            fclose($socket);
        }
    }

    private function writeString(string $value): string
    {
        return $this->varInt(strlen($value)) . $value;
    }

    private function varInt(int $value): string
    {
        $result = '';

        do {
            $temp = $value & 0x7F;
            $value >>= 7;

            if ($value !== 0) {
                $temp |= 0x80;
            }

            $result .= chr($temp);
        } while ($value !== 0);

        return $result;
    }

    private function readVarInt($socket): int
    {
        $numRead = 0;
        $result = 0;

        do {
            $byte = fread($socket, 1);

            if ($byte === '' || $byte === false) {
                $meta = stream_get_meta_data($socket);

                if (!empty($meta['timed_out'])) {
                    throw new \RuntimeException(
                        'Timeout while reading VarInt'
                    );
                }

                throw new \RuntimeException(
                    'Connection closed while reading VarInt'
                );
            }

            $value = ord($byte);

            $result |=
                ($value & 0x7F) << (7 * $numRead);

            $numRead++;

            if ($numRead > 5) {
                throw new \RuntimeException(
                    'VarInt too big'
                );
            }
        } while (($value & 0x80) !== 0);

        return $result;
    }

    private function readExact($socket, int $length): string
    {
        $data = '';

        while (strlen($data) < $length) {
            $chunk = fread(
                $socket,
                $length - strlen($data)
            );

            if ($chunk === '' || $chunk === false) {
                $meta = stream_get_meta_data($socket);

                if (!empty($meta['timed_out'])) {
                    throw new \RuntimeException(
                        'Timeout while reading response'
                    );
                }

                throw new \RuntimeException(
                    'Connection closed while reading response'
                );
            }

            $data .= $chunk;
        }

        return $data;
    }

    private function writeAll($socket, string $data): void
    {
        $written = 0;
        $length = strlen($data);

        while ($written < $length) {
            $result = fwrite(
                $socket,
                substr($data, $written)
            );

            if ($result === false || $result === 0) {
                throw new \RuntimeException(
                    'Failed to write to socket'
                );
            }

            $written += $result;
        }
    }
}
