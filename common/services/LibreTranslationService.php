<?php

declare(strict_types=1);

namespace common\services;

final class LibreTranslationService
{
    private const ENDPOINT = 'http://127.0.0.1:5000/translate';

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

        if(!in_array($target, ['en', 'de'], true))
        {
            throw new \InvalidArgumentException(
                'Unsupported target language: ' . $target
            );
        }

        $ch = curl_init(self::ENDPOINT);

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
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 180,
        ]);

        $response = curl_exec($ch);

        if($response === false)
        {
            $error = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException(
                'LibreTranslate request failed: ' . $error
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

        if($httpCode >= 400)
        {
            throw new \RuntimeException(
                'LibreTranslate HTTP ' . $httpCode . ': ' . $response
            );
        }

        if(isset($data['error']))
        {
            throw new \RuntimeException(
                'LibreTranslate error: ' . $data['error']
            );
        }

        $translated = $data['translatedText'] ?? null;

        if(!is_string($translated))
        {
            throw new \RuntimeException(
                'LibreTranslate returned no translatedText'
            );
        }

        return $translated;
    }
}
