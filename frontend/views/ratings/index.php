<?php

use yii\helpers\{Html, Url};

$this->title = 'Рейтинги';
?>

<div class="container ht-section">
    <div class="section-title">
        <div>
            <h2>Рейтинги HiveTech</h2>
            <p class="muted mb-0">
                Общий рейтинг по очкам достижений.
            </p>
        </div>
    </div>

    <div class="ht-card">
        <?php if(!$rows): ?>
            <div class="muted">
                Рейтинг пока пуст. Первые позиции появятся после разблокировки достижений.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Игрок</th>
                        <th class="text-end">Очки</th>
                        <th class="text-end">Достижения</th>
                        <th class="text-end">Последнее</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <td class="fw-bold">
                                #<?= (int)$row['rank'] ?>
                            </td>
                            <td>
                                <a
                                    href="<?= Url::to([
                                        '/ratings/player',
                                        'username' => $row['username'],
                                    ]) ?>"
                                    class="text-decoration-none fw-bold"
                                >
                                    <?= Html::encode((string)$row['username']) ?>
                                </a>
                            </td>
                            <td class="text-end">
                                <?= number_format((int)$row['score'], 0, ',', ' ') ?>
                            </td>
                            <td class="text-end">
                                <?= (int)$row['achievements'] ?>
                            </td>
                            <td class="text-end muted">
                                <?= Html::encode((string)$row['last_unlock']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
