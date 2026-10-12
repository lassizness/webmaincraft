<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

final class m261012_000003_add_achievements_news extends Migration
{
    private const SLUG = 'achievement-system';

    public function safeUp(): void
    {
        $exists = (new Query())
            ->from('{{%news}}')
            ->where(['slug' => self::SLUG])
            ->exists($this->db);

        if($exists)
        {
            return;
        }

        $categoryId = (new Query())
            ->select('id')
            ->from('{{%news_categories}}')
            ->where(['slug' => 'general'])
            ->scalar($this->db);

        $publishedAt = date('Y-m-d H:i:s');

        $this->insert('{{%news}}', [
            'category_id' => $categoryId !== false
                ? (int)$categoryId
                : null,
            'author_id' => null,
            'title' => 'На HiveTech появилась система достижений',
            'slug' => self::SLUG,
            'excerpt' => 'Запустили большую систему достижений и игровой статистики: 91 достижение, технологические ветки, редкости, очки и прогресс по аккаунту.',
            'body' => $this->russianBody(),
            'published' => 1,
            'published_at' => $publishedAt,
        ]);

        $newsId = (int)$this->db->getLastInsertID();

        $this->batchInsert(
            '{{%news_translations}}',
            [
                'news_id',
                'language',
                'title',
                'excerpt',
                'body',
            ],
            [
                [
                    $newsId,
                    'en',
                    'Achievements Are Now Live on HiveTech',
                    'A new achievement and gameplay statistics system is live: 91 achievements, technology branches, rarities, score and account-wide progress.',
                    $this->englishBody(),
                ],
                [
                    $newsId,
                    'de',
                    'Das Erfolgssystem ist jetzt auf HiveTech verfügbar',
                    'Das neue Erfolgs- und Statistiksystem ist gestartet: 91 Erfolge, Technik-Kategorien, Seltenheitsstufen, Punkte und Fortschritt im Account.',
                    $this->germanBody(),
                ],
            ]
        );
    }

    public function safeDown(): void
    {
        $this->delete(
            '{{%news}}',
            ['slug' => self::SLUG]
        );
    }

    private function russianBody(): string
    {
        return <<<'HTML'
<p>На HiveTech заработала новая система достижений и игровой статистики. Теперь действия на сервере учитываются в профиле, а за прогресс в разных направлениях можно получать достижения с разной редкостью и количеством очков.</p>

<h2>91 достижение на старте</h2>
<p>Первая версия каталога уже содержит 91 постоянное достижение. Есть как обычные игровые цели, так и отдельные технологические ветки.</p>
<ul>
    <li>общее игровое время;</li>
    <li>добыча и строительство;</li>
    <li>крафт и переплавка;</li>
    <li>бой, PvP и исследования;</li>
    <li>Applied Energistics 2;</li>
    <li>IndustrialCraft 2;</li>
    <li>Forestry, Gendustry и Binnie;</li>
    <li>Thermal Expansion/Foundation;</li>
    <li>Immersive Engineering;</li>
    <li>Industrial Foregoing.</li>
</ul>

<h2>От обычных целей до больших инженерных задач</h2>
<p>Часть достижений открывается постепенно: провести 10, 50 или 100 часов в игре, установить тысячи блоков, изучить разные измерения или собрать серьёзную технологическую базу.</p>
<p>Есть и составные цели. Например, одно из достижений требует освоить сразу несколько крупных технологических направлений, а некоторые достижения скрыты до выполнения.</p>

<h2>Редкость и Achievement Score</h2>
<p>У каждого достижения есть редкость и собственная стоимость в очках:</p>
<ul>
    <li>Common;</li>
    <li>Uncommon;</li>
    <li>Rare;</li>
    <li>Epic;</li>
    <li>Legendary.</li>
</ul>
<p>Полученные очки формируют Achievement Score игрока. Он отображается в личном кабинете и позже будет использоваться в публичных рейтингах, когда они будут включены на проекте.</p>

<h2>Как считается прогресс</h2>
<p>HiveTechTelemetry на игровом сервере собирает игровые события и отправляет их на портал. При этом события высокой частоты агрегируются перед отправкой, а повторная доставка защищена от двойного начисления.</p>
<p>Портал хранит долговременную статистику отдельно: время игры, количество действий, уникальные открытия и уже полученные достижения. Сырые события нужны в основном для недавней истории и диагностики и автоматически удаляются через ограниченный срок хранения.</p>

<h2>Технологические моды</h2>
<p>Система уже понимает не только обычные Minecraft-события, но и действия из основных модов сборки. Например, она может учитывать машины IC2, компоненты AE2, виды пчёл Forestry, оборудование Thermal, Immersive Engineering и Industrial Foregoing.</p>
<p>Для неоднозначных действий мы специально не придумываем результат. Например, простое нажатие на машину не считается автоматически успешной установкой улучшения или завершённым производственным циклом. Такие события будут добавляться отдельно по мере подключения точных интеграций с модами.</p>

<h2>Что с наградами</h2>
<p>Сейчас основной наградой является Achievement Score и сам статус достижения в профиле. Система уже технически умеет выдавать дополнительные награды: кварки, игровые команды, права и временные привилегии, но в стартовом каталоге они пока не включены.</p>
<p>Сначала мы хотим посмотреть на реальную скорость получения достижений и отбалансировать экономику, чтобы награды нельзя было бесконечно фармить или получать слишком быстро.</p>

<hr>
<p>Открыть достижения можно в личном кабинете. Уже накопленная игровая статистика тоже учитывается: после обновления система пересчитывает прогресс и может сразу открыть то, что было выполнено раньше.</p>
HTML;
    }

    private function englishBody(): string
    {
        return <<<'HTML'
<p>HiveTech now has a new achievement and gameplay statistics system. Actions performed on the game server can now contribute to account progress, with achievements of different rarities and score values.</p>

<h2>91 achievements at launch</h2>
<p>The first permanent catalog already contains 91 achievements, ranging from general gameplay goals to dedicated technology progression branches.</p>
<ul>
    <li>playtime;</li>
    <li>mining and building;</li>
    <li>crafting and smelting;</li>
    <li>combat, PvP and exploration;</li>
    <li>Applied Energistics 2;</li>
    <li>IndustrialCraft 2;</li>
    <li>Forestry, Gendustry and Binnie;</li>
    <li>Thermal Expansion/Foundation;</li>
    <li>Immersive Engineering;</li>
    <li>Industrial Foregoing.</li>
</ul>

<h2>From simple goals to large engineering milestones</h2>
<p>Some achievements form long-term progression: spend 10, 50 or 100 hours in the game, place thousands of blocks, visit multiple dimensions or build a serious technology infrastructure.</p>
<p>There are also compound achievements that require progress across several technology branches, as well as hidden achievements that reveal themselves only after completion.</p>

<h2>Rarity and Achievement Score</h2>
<p>Every achievement has a rarity and a score value:</p>
<ul>
    <li>Common;</li>
    <li>Uncommon;</li>
    <li>Rare;</li>
    <li>Epic;</li>
    <li>Legendary.</li>
</ul>
<p>Unlocked achievements contribute to the player's Achievement Score. The score is visible in the account area and will later be used by public rankings when that feature is enabled on the project.</p>

<h2>How progress is tracked</h2>
<p>HiveTechTelemetry collects gameplay events on the Minecraft server and sends them to the portal. High-frequency activity is aggregated before transmission, and retries are idempotent so the same event cannot be counted twice.</p>
<p>Long-term statistics are stored separately by the portal: playtime, counters, unique discoveries and unlocked achievements. Raw events are mainly used for recent history and diagnostics and are automatically deleted after a limited retention period.</p>

<h2>Technology mods</h2>
<p>The system already understands more than vanilla Minecraft events. It can track activity from the main technology mods in the pack, including IC2 machines, AE2 components, Forestry bee species, Thermal equipment, Immersive Engineering and Industrial Foregoing.</p>
<p>We deliberately do not fabricate ambiguous facts. A simple interaction with a machine is not automatically treated as a successful upgrade installation or completed production cycle. Exact deep integrations will be added separately as their internal hooks are verified.</p>

<h2>What about rewards?</h2>
<p>For now, the main reward is Achievement Score and the achievement itself on your profile. The platform already supports additional reward types such as quarks, game commands, permissions and temporary privileges, but they are intentionally not attached to the launch catalog yet.</p>
<p>We first want to see real progression speed and balance the economy so achievements cannot be farmed indefinitely or turned into an unintended source of currency.</p>

<hr>
<p>You can find achievements in your account area. Previously accumulated gameplay statistics are taken into account as well: after recalculation, achievements completed before the catalog launch can unlock retroactively.</p>
HTML;
    }

    private function germanBody(): string
    {
        return <<<'HTML'
<p>HiveTech verfügt jetzt über ein neues Erfolgs- und Spielstatistiksystem. Aktionen auf dem Minecraft-Server können nun zum Fortschritt des Accounts beitragen. Erfolge besitzen unterschiedliche Seltenheitsstufen und Punktwerte.</p>

<h2>91 Erfolge zum Start</h2>
<p>Der erste permanente Katalog enthält bereits 91 Erfolge. Sie reichen von allgemeinen Spielzielen bis zu eigenen Fortschrittszweigen für Technik-Mods.</p>
<ul>
    <li>Spielzeit;</li>
    <li>Abbau und Bauen;</li>
    <li>Herstellung und Schmelzen;</li>
    <li>Kampf, PvP und Erkundung;</li>
    <li>Applied Energistics 2;</li>
    <li>IndustrialCraft 2;</li>
    <li>Forestry, Gendustry und Binnie;</li>
    <li>Thermal Expansion/Foundation;</li>
    <li>Immersive Engineering;</li>
    <li>Industrial Foregoing.</li>
</ul>

<h2>Von einfachen Zielen bis zu großen Technik-Meilensteinen</h2>
<p>Ein Teil der Erfolge bildet langfristigen Fortschritt ab: 10, 50 oder 100 Stunden spielen, Tausende Blöcke setzen, verschiedene Dimensionen besuchen oder eine umfangreiche technische Infrastruktur aufbauen.</p>
<p>Es gibt außerdem kombinierte Erfolge, die Fortschritt in mehreren Technik-Zweigen gleichzeitig verlangen, sowie versteckte Erfolge, die erst nach dem Freischalten sichtbar werden.</p>

<h2>Seltenheit und Achievement Score</h2>
<p>Jeder Erfolg besitzt eine Seltenheitsstufe und einen Punktwert:</p>
<ul>
    <li>Common;</li>
    <li>Uncommon;</li>
    <li>Rare;</li>
    <li>Epic;</li>
    <li>Legendary.</li>
</ul>
<p>Freigeschaltete Erfolge erhöhen den Achievement Score des Spielers. Der Punktestand wird im persönlichen Bereich angezeigt und soll später auch für öffentliche Ranglisten genutzt werden, sobald diese Funktion im Projekt aktiviert wird.</p>

<h2>Wie der Fortschritt erfasst wird</h2>
<p>HiveTechTelemetry sammelt Spielereignisse auf dem Minecraft-Server und übermittelt sie an das Portal. Häufige Aktionen werden vor der Übertragung zusammengefasst. Wiederholte Übertragungen sind idempotent, sodass dasselbe Ereignis nicht doppelt gezählt wird.</p>
<p>Langfristige Statistiken werden getrennt gespeichert: Spielzeit, Zähler, einzigartige Entdeckungen und bereits freigeschaltete Erfolge. Rohereignisse werden hauptsächlich für die jüngere Historie und Diagnose verwendet und nach einer begrenzten Aufbewahrungszeit automatisch gelöscht.</p>

<h2>Technik-Mods</h2>
<p>Das System versteht bereits mehr als nur Vanilla-Minecraft-Ereignisse. Es kann Aktivitäten aus den wichtigsten Mods der Zusammenstellung erfassen, darunter IC2-Maschinen, AE2-Komponenten, Forestry-Bienenarten, Thermal-Geräte, Immersive Engineering und Industrial Foregoing.</p>
<p>Mehrdeutige Vorgänge werden bewusst nicht erfunden. Eine einfache Interaktion mit einer Maschine gilt beispielsweise nicht automatisch als erfolgreich installierte Verbesserung oder abgeschlossener Produktionszyklus. Solche tiefen Integrationen werden separat ergänzt, sobald die jeweiligen internen Hooks zuverlässig geprüft sind.</p>

<h2>Wie sieht es mit Belohnungen aus?</h2>
<p>Derzeit besteht die Hauptbelohnung aus dem Achievement Score und dem freigeschalteten Erfolg im Profil. Technisch unterstützt die Plattform bereits zusätzliche Belohnungen wie Quarks, Spielbefehle, Berechtigungen und zeitlich begrenzte Privilegien. Im Startkatalog sind sie jedoch bewusst noch nicht aktiviert.</p>
<p>Zuerst möchten wir die tatsächliche Geschwindigkeit des Fortschritts beobachten und die Wirtschaft ausbalancieren, damit Erfolge nicht unbegrenzt gefarmt oder zu einer unbeabsichtigten Währungsquelle werden können.</p>

<hr>
<p>Die Erfolge findet ihr im persönlichen Bereich. Bereits zuvor gesammelte Spielstatistiken werden ebenfalls berücksichtigt: Nach der Neuberechnung können Erfolge rückwirkend freigeschaltet werden, wenn ihre Bedingungen schon vor Veröffentlichung des Katalogs erfüllt waren.</p>
HTML;
    }
}
