<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\{SupportAttachment, SupportMessage, SupportTicket};
use frontend\models\ReplyForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

final class SupportController extends AdminController
{
    protected string $permission = 'manageSupport';

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => SupportTicket::find()->orderBy(['updated_at' => SORT_DESC]),
            ]),
        ]);
    }

    public function actionAttachment(int $id)
    {
        $attachment = SupportAttachment::findOne($id);

        if(!$attachment)
        {
            throw new NotFoundHttpException();
        }

        $path = Yii::getAlias('@storage/uploads/support/' . $attachment->stored_name);

        if(!is_file($path))
        {
            throw new NotFoundHttpException();
        }

        return Yii::$app->response->sendFile(
            $path,
            $attachment->original_name,
            [
                'mimeType' => $attachment->mime_type,
                'inline' => false,
            ]
        );
    }

    public function actionView(int $id)
    {
        $ticket = SupportTicket::findOne($id);

        if(!$ticket)
        {
            throw new NotFoundHttpException();
        }

        $model = new ReplyForm();

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $message = new SupportMessage([
                'ticket_id' => $ticket->id,
                'user_id' => Yii::$app->user->id,
                'body' => $model->message,
                'is_staff' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $message->save(false);

            $ticket->status = SupportTicket::STATUS_ANSWERED;
            $ticket->assigned_to = Yii::$app->user->id;
            $ticket->updated_at = date('Y-m-d H:i:s');
            $ticket->save(false);

            if($ticket->user)
            {
                $user = $ticket->user;
                $previousLanguage = Yii::$app->language;
                Yii::$app->language = in_array((string)$user->language, ['ru', 'en', 'de'], true)
                    ? (string)$user->language
                    : 'ru';

                try
                {
                    Yii::$app->mailer
                        ->compose('support-reply', [
                            'ticket' => $ticket,
                            'message' => $message,
                        ])
                        ->setTo($user->email)
                        ->setSubject(Yii::t('app', 'Ответ поддержки HiveTech #{id}', [
                            'id' => $ticket->id,
                        ]))
                        ->send();
                }
                finally
                {
                    Yii::$app->language = $previousLanguage;
                }
            }

            return $this->refresh();
        }

        return $this->render('view', [
            'ticket' => $ticket,
            'model' => $model,
        ]);
    }

    public function actionClose(int $id)
    {
        if(Yii::$app->request->isPost)
        {
            $ticket = SupportTicket::findOne($id);

            if($ticket)
            {
                $ticket->status = SupportTicket::STATUS_CLOSED;
                $ticket->updated_at = date('Y-m-d H:i:s');
                $ticket->save(false);
            }
        }

        return $this->redirect(['view', 'id' => $id]);
    }
}
