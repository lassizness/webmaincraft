<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

final class Vote extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%votes}}';
    }

    public function rules(): array
    {
        return [
            [['service_id', 'user_id', 'vote_date', 'reward_amount'], 'required'],
            [['service_id', 'user_id'], 'integer'],
            [['reward_amount'], 'number', 'min' => 0],
            [['vote_date', 'rewarded_at', 'created_at'], 'safe'],
            [['callback_payload'], 'string'],
            [['voter_ip', 'callback_ip'], 'string', 'max' => 45],
            [['provider_vote_id'], 'string', 'max' => 128],
        ];
    }

    public function getService(): ActiveQuery
    {
        return $this->hasOne(VoteService::class, ['id' => 'service_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
