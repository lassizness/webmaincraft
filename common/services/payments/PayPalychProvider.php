<?php

declare(strict_types=1);

namespace common\services\payments;

use common\models\{PaymentOrder, Setting};
use Yii;
use yii\httpclient\Client;

final class PayPalychProvider implements PaymentProviderInterface
{
    public function code(): string
    {
        return 'paypalych';
    }

    public function isConfigured(): bool
    {
        return (string)Setting::value('payments.paypalych.enabled', '0') === '1'
            && $this->shopId() !== ''
            && $this->apiToken() !== '';
    }

    public function createPayment(PaymentOrder $order): PaymentInitResult
    {
        if(!$this->isConfigured())
        {
            throw new \RuntimeException('PayPalych is not configured.');
        }

        $client = new Client([
            'baseUrl' => rtrim($this->baseUrl(), '/') . '/',
            'requestConfig' => [
                'format' => Client::FORMAT_URLENCODED,
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON,
            ],
        ]);

        $response = $client
            ->createRequest()
            ->setMethod('POST')
            ->setUrl('bill/create')
            ->addHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken(),
                'Accept' => 'application/json',
            ])
            ->setData([
                'amount' => number_format((float)$order->amount, 2, '.', ''),
                'shop_id' => $this->shopId(),
                'order_id' => (string)$order->id,
                'description' => 'HiveTech balance top-up #' . $order->id,
                'type' => 'normal',
                'currency_in' => 'RUB',
                'name' => 'HiveTech',
            ])
            ->send();

        $data = is_array($response->data) ? $response->data : [];

        if(!$response->isOk || empty($data['bill_id']))
        {
            Yii::error([
                'provider' => $this->code(),
                'statusCode' => $response->statusCode,
                'response' => $data,
                'orderId' => (int)$order->id,
            ], 'payments');

            throw new \RuntimeException('PayPalych bill creation failed.');
        }

        $paymentUrl = trim((string)($data['link_page_url'] ?? $data['link_url'] ?? ''));

        if($paymentUrl === '')
        {
            throw new \RuntimeException('PayPalych response does not contain payment URL.');
        }

        return new PaymentInitResult(
            (string)$data['bill_id'],
            $paymentUrl,
            $data
        );
    }

    public function verifyPostback(array $payload): bool
    {
        $outSum = (string)($payload['OutSum'] ?? '');
        $invId = (string)($payload['InvId'] ?? '');
        $signature = strtoupper(trim((string)($payload['SignatureValue'] ?? '')));

        if($outSum === '' || $invId === '' || $signature === '')
        {
            return false;
        }

        $expected = strtoupper(md5(
            $outSum . ':' . $invId . ':' . $this->signatureToken()
        ));

        return hash_equals($expected, $signature);
    }

    private function shopId(): string
    {
        return trim((string)Setting::value('payments.paypalych.shop_id', ''));
    }

    private function baseUrl(): string
    {
        return trim((string)Setting::value(
            'payments.paypalych.base_url',
            'https://pally.info/api/v1'
        ));
    }

    private function apiToken(): string
    {
        return trim((string)ht_env('PAYPALYCH_API_TOKEN', ''));
    }

    private function signatureToken(): string
    {
        return trim((string)ht_env(
            'PAYPALYCH_SIGNATURE_TOKEN',
            $this->apiToken()
        ));
    }
}
