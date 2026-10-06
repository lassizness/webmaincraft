<?php

declare(strict_types=1);
namespace frontend\models;
use yii\base\Model;use yii\web\UploadedFile;
final class TicketForm extends Model { public string $subject='';public string $message='';public array $attachments=[];public function rules():array{return [[['subject','message'],'required'],['subject','string','min'=>4,'max'=>255],['message','string','min'=>10],['attachments','file','extensions'=>['png','jpg','jpeg','webp','pdf','txt','log','zip'],'maxSize'=>5*1024*1024,'maxFiles'=>3,'skipOnEmpty'=>true]];} }
