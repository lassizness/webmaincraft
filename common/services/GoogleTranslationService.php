<?php

declare(strict_types=1);

namespace common\services;

final class GoogleTranslationService
{
    private const ENDPOINT =
        'https://translation.googleapis.com/language/translate/v2';

    public function translate(
        string $text,
        string $target,
        string $format = 'text'
    ): string {
        $text = trim($text);

        if($text === '')
        {
            return '';
        }

        $key = trim((string)ht_env(
            'GOOGLE_TRANSLATE_API_KEY',
            ''
        ));

        if($key === '')
        {
            throw new \RuntimeException(
                'GOOGLE_TRANSLATE_API_KEY is not configured'
            );
        }

        $ch = curl_init(
            self::ENDPOINT . '?key=' . rawurlencode($key)
        );

        $payload = json_encode([
            'q' => $text,
            'source' => 'ru',
            'target' => $target,
            'format' => $format,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);

        if($response === false)
        {
            $error = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException(
                'Google Translate request failed: ' . $error
            );
        }

        $httpCode = (int)curl_getinfo(
            $ch,
            CURLINFO_RESPONSE_CODE
        );

        curl_close($ch);

        $data = json_decode(
            $response,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if(
            $httpCode >= 400
            || isset($data['error'])
        )
        {
            throw new \RuntimeException(
                'Google Translate API error: ' .
                ($data['error']['message'] ?? $response)
            );
        }

        $translated =
            $data['data']['translations'][0]['translatedText']
            ?? null;

        if(!is_string($translated))
        {
            throw new \RuntimeException(
                'Google Translate returned no translation'
            );
        }

        return html_entity_decode(
            $translated,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }
}
