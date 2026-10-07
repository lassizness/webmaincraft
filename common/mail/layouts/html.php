<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

$appUrl = rtrim((string)(Yii::$app->params['appUrl'] ?? ''), '/');

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Html::encode(Yii::$app->language) ?>">
<head>
    <meta charset="<?= Html::encode(Yii::$app->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>HiveTech</title>
    <?php $this->head() ?>
    <style>
        body,
        table,
        td,
        a {
            font-family: Inter, Arial, Helvetica, sans-serif;
        }

        .mail-content h1,
        .mail-content h2,
        .mail-content h3 {
            margin: 0 0 16px;
            color: #eef5fb;
            font-weight: 800;
            line-height: 1.2;
        }

        .mail-content p {
            margin: 0 0 16px;
            color: #c8d5e1;
            font-size: 15px;
            line-height: 1.7;
        }

        .mail-content a {
            color: #69c9f2;
        }

        .mail-content a.mail-button {
            display: inline-block;
            border-radius: 10px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #15803d, #0369a1);
            color: #ffffff !important;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
        }

        @media only screen and (max-width: 640px) {
            .mail-shell {
                padding: 18px 10px !important;
            }

            .mail-card {
                border-radius: 14px !important;
            }

            .mail-header,
            .mail-content {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#090d12;color:#eef5fb;">
<?php $this->beginBody() ?>
<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    class="mail-shell"
    style="width:100%;margin:0;padding:32px 16px;background:#090d12;"
>
    <tr>
        <td align="center">
            <table
                role="presentation"
                width="100%"
                cellspacing="0"
                cellpadding="0"
                border="0"
                class="mail-card"
                style="width:100%;max-width:640px;overflow:hidden;border:1px solid #2f4254;border-radius:20px;background:#111821;"
            >
                <tr>
                    <td
                        height="4"
                        style="height:4px;font-size:0;line-height:0;background:linear-gradient(90deg,#22c55e,#38bdf8);"
                    >&nbsp;</td>
                </tr>
                <tr>
                    <td
                        class="mail-header"
                        style="padding:26px 32px 18px;background:#0d141c;border-bottom:1px solid #22303e;"
                    >
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td
                                    width="42"
                                    height="42"
                                    align="center"
                                    valign="middle"
                                    style="width:42px;height:42px;border-radius:11px;background:#22c55e;color:#06100b;font-size:21px;font-weight:800;"
                                >
                                    H
                                </td>
                                <td style="padding-left:12px;">
                                    <div style="color:#ffffff;font-size:20px;font-weight:800;line-height:1.15;">
                                        HiveTech
                                    </div>
                                    <div style="margin-top:4px;color:#73e2a7;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;">
                                        HiveGrid Network
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td
                        class="mail-content"
                        style="padding:28px 32px 32px;color:#c8d5e1;font-size:15px;line-height:1.7;"
                    >
                        <?= $content ?>
                    </td>
                </tr>
            </table>

            <table
                role="presentation"
                width="100%"
                cellspacing="0"
                cellpadding="0"
                border="0"
                style="width:100%;max-width:640px;"
            >
                <tr>
                    <td
                        align="center"
                        style="padding:16px 12px 0;color:#7f91a3;font-size:12px;line-height:1.6;"
                    >
                        <div>HiveGrid · Minecraft Network</div>
                        <?php if($appUrl !== ''): ?>
                            <div style="margin-top:4px;">
                                <a
                                    href="<?= Html::encode($appUrl) ?>"
                                    style="color:#69c9f2;text-decoration:none;"
                                >
                                    <?= Html::encode(
                                        preg_replace(
                                            '#^https?://#',
                                            '',
                                            $appUrl
                                        )
                                    ) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
