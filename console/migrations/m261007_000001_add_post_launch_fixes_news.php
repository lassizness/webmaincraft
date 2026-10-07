<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

final class m261007_000001_add_post_launch_fixes_news extends Migration
{
    private const SLUG = 'post-launch-fixes';

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
            'category_id' => $categoryId !== false ? (int)$categoryId : null,
            'author_id' => null,
            'title' => 'Что мы исправили после технического запуска',
            'slug' => self::SLUG,
            'excerpt' => 'Подводим итоги первых недель после технического запуска: аккаунты, лаунчер, скины, привилегии, голосование, локализация и другие исправления.',
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
                    'What We Fixed After the Technical Launch',
                    'A summary of the first weeks after the technical launch: accounts, launcher, skins, privileges, voting, localization and other fixes.',
                    $this->englishBody(),
                ],
                [
                    $newsId,
                    'de',
                    'Was wir seit dem technischen Start verbessert haben',
                    'Ein Rückblick auf die ersten Wochen nach dem technischen Start: Accounts, Launcher, Skins, Ränge, Abstimmungen, Lokalisierung und weitere Verbesserungen.',
                    $this->germanBody(),
                ],
            ]
        );
    }

    public function safeDown(): void
    {
        $this->delete('{{%news}}', ['slug' => self::SLUG]);
    }

    private function russianBody(): string
    {
        return <<<'HTML'
<p>После технического запуска HiveTech мы постепенно проверяли портал, лаунчер и связку с игровым сервером уже в реальных условиях. За это время накопилось достаточно изменений, поэтому решили собрать их в одну новость.</p>

<p>Главная задача этого этапа была простой: убрать мелкие и не очень мелкие проблемы, которые мешали нормально пользоваться проектом, и привести сайт с лаунчером к состоянию, когда они работают как единая система.</p>

<h2>Аккаунт и авторизация</h2>
<p>Мы доработали общую систему HiveTech ID. Аккаунт на сайте и аккаунт в лаунчере используют одну и ту же учётную запись, поэтому не нужно заводить отдельные логины и пароли.</p>
<p>Исправили и проверили:</p>
<ul>
    <li>авторизацию через сайт и HiveTech Launcher;</li>
    <li>регистрацию новых игроков;</li>
    <li>подтверждение email;</li>
    <li>восстановление и смену пароля;</li>
    <li>завершение старых сессий после смены пароля;</li>
    <li>работу аккаунта после изменения email;</li>
    <li>защиту от повторных и подозрительных попыток входа.</li>
</ul>
<p>Также привели в порядок сообщения об ошибках, чтобы вместо непонятного поведения пользователь получал нормальное объяснение того, что произошло.</p>

<h2>Скины, плащи и внешний вид персонажа</h2>
<p>Отдельно пришлось повозиться с системой текстур.</p>
<p>Исправили выдачу скинов и плащей в лаунчере, перевели загрузку текстур на HTTPS-адреса и привязали всё к аккаунту HiveTech.</p>
<p>В личном кабинете теперь можно:</p>
<ul>
    <li>загружать собственный скин;</li>
    <li>загружать плащ при наличии подходящего статуса;</li>
    <li>удалять текущий скин или плащ;</li>
    <li>выбирать цвет ника для доступных привилегий;</li>
    <li>сразу видеть превью загруженной текстуры.</li>
</ul>
<p>Также поправили проверки формата и размера файлов и сообщения об ошибках при загрузке.</p>

<h2>Статусы и привилегии</h2>
<p>Доработали систему игровых статусов и их синхронизацию с сервером.</p>
<p>Теперь портал корректно учитывает срок действия привилегии, выдаёт нужный игровой ранг и снимает его после окончания. Вместе со статусом синхронизируются связанные возможности, в том числе плащи и персонализация ника.</p>

<h2>Голосование за сервер</h2>
<p>Появилась полноценная система голосования за HiveTech.</p>
<p>Подключили TopG и TopCraft, а награды за подтверждённые голоса автоматически начисляются на баланс игрока в кварках.</p>
<p>Для голосования используется игровой ник, а повторные callback-запросы от рейтингов защищены от повторного начисления награды.</p>
<p>Сервисы голосования также появились в личном кабинете и на главной странице.</p>

<h2>Скачивание лаунчера</h2>
<p>Исправили неприятную проблему со скачиванием HiveTech Launcher.</p>
<p>Раньше ссылка на EXE-файл вела с HTTPS-сайта на обычный HTTP-порт, из-за чего современные браузеры могли блокировать загрузку или заставлять сохранять файл вручную.</p>
<p>Теперь лаунчер скачивается через обычный HTTPS-адрес непосредственно с hivegrid.online.</p>

<h2>Локализация сайта</h2>
<p>Большой объём работы сделали с языками.</p>
<p>Русский, английский и немецкий интерфейсы теперь охватывают значительно больше разделов сайта:</p>
<ul>
    <li>регистрацию и вход;</li>
    <li>восстановление пароля;</li>
    <li>личный кабинет;</li>
    <li>скины и плащи;</li>
    <li>привилегии;</li>
    <li>магазин;</li>
    <li>баланс;</li>
    <li>голосование;</li>
    <li>серверы;</li>
    <li>системные уведомления и ошибки.</li>
</ul>
<p>Исправили места, где при английском или немецком языке внезапно появлялся русский текст.</p>
<p>Письма подтверждения регистрации, восстановления пароля и ответы поддержки теперь тоже могут отправляться на языке пользователя. Новости и страницы сайта имеют отдельные версии для каждого языка.</p>

<h2>Портал и техническая часть</h2>
<p>Помимо видимых изменений, было много работы под капотом.</p>
<p>Мы перевели портал под Git, добавили историю изменений и автоматическую проверку PHP-кода перед объединением новых правок. Это позволит дальше обновлять сайт аккуратнее и снизит шанс случайно сломать работающий функционал.</p>
<p>Также продолжаем приводить интерфейс к единому виду: проверяем отображение на телефонах и планшетах, читаемость текста, контраст элементов и поведение длинных названий на разных языках.</p>

<hr>
<p>Технический запуск для нас и был нужен именно для этого: не объявить проект «готовым навсегда», а проверить всю связку в работе и постепенно убрать слабые места.</p>
<p>Работа продолжается. Если заметите странное поведение сайта, лаунчера, личного кабинета или игрового сервера, пишите в поддержку. Такие сообщения помогают быстрее находить вещи, которые невозможно поймать только внутренними тестами.</p>
HTML;
    }

    private function englishBody(): string
    {
        return <<<'HTML'
<p>After the technical launch of HiveTech, we started testing the portal, launcher and game-server integration under real conditions. Enough changes have accumulated since then, so we decided to collect the most important ones in a single update.</p>

<p>The goal of this stage was simple: remove the small and not-so-small issues that got in the way and make the website and launcher behave like one connected system.</p>

<h2>Account and authentication</h2>
<p>We improved the shared HiveTech ID system. The website and HiveTech Launcher use the same account, so there is no need to create separate usernames and passwords.</p>
<p>We fixed and verified:</p>
<ul>
    <li>sign-in through the website and HiveTech Launcher;</li>
    <li>new player registration;</li>
    <li>email verification;</li>
    <li>password recovery and password changes;</li>
    <li>revocation of old sessions after a password change;</li>
    <li>account behavior after changing the email address;</li>
    <li>protection against repeated and suspicious login attempts.</li>
</ul>
<p>We also cleaned up user-facing error messages so that failed actions now explain what actually happened instead of appearing to do nothing.</p>

<h2>Skins, capes and character appearance</h2>
<p>The texture system needed a separate round of work.</p>
<p>We fixed skin and cape delivery in the launcher, moved texture loading to HTTPS endpoints and tied everything to the HiveTech account.</p>
<p>The account area now allows you to:</p>
<ul>
    <li>upload your own skin;</li>
    <li>upload a cape when your status allows it;</li>
    <li>remove the current skin or cape;</li>
    <li>choose a nickname color for eligible privileges;</li>
    <li>preview the uploaded texture immediately.</li>
</ul>
<p>File format and size validation, as well as upload error messages, were improved too.</p>

<h2>Statuses and privileges</h2>
<p>We refined the game-status system and its synchronization with the server.</p>
<p>The portal now correctly tracks privilege expiration, assigns the required game rank and removes it when the privilege ends. Related features, including capes and nickname personalization, are synchronized with the active status as well.</p>

<h2>Server voting</h2>
<p>HiveTech now has a complete voting system.</p>
<p>TopG and TopCraft are connected, and rewards for confirmed votes are automatically credited to the player's quark balance.</p>
<p>Votes are linked to the Minecraft username, while repeated callbacks from ranking services are protected against duplicate rewards.</p>
<p>Voting services are also available from the account area and the home page.</p>

<h2>Launcher download</h2>
<p>We fixed an annoying issue with downloading HiveTech Launcher.</p>
<p>The old EXE link pointed from the HTTPS website to a plain HTTP port. Modern browsers could block this download or require saving the file manually.</p>
<p>The launcher is now downloaded through a regular HTTPS address directly from hivegrid.online.</p>

<h2>Website localization</h2>
<p>A large part of the work went into localization.</p>
<p>Russian, English and German now cover many more areas of the website:</p>
<ul>
    <li>registration and sign-in;</li>
    <li>password recovery;</li>
    <li>the account area;</li>
    <li>skins and capes;</li>
    <li>privileges;</li>
    <li>the store;</li>
    <li>the wallet;</li>
    <li>voting;</li>
    <li>servers;</li>
    <li>system notifications and errors.</li>
</ul>
<p>We fixed places where Russian text could unexpectedly appear while using the English or German interface.</p>
<p>Registration verification, password recovery and support emails can now be sent in the user's language as well. News and CMS pages also have separate language versions.</p>

<h2>Portal and technical improvements</h2>
<p>There was also a lot of work behind the scenes.</p>
<p>The portal is now maintained in Git, with change history and automatic PHP syntax checks before new changes are merged. This gives us a safer and more predictable update process.</p>
<p>We are also continuing a visual cleanup pass: checking phones and tablets, text readability, color contrast and how longer labels behave across different languages.</p>

<hr>
<p>This is exactly what the technical launch was meant for: not to declare the project “finished forever”, but to test the entire system in real use and gradually remove weak spots.</p>
<p>Work continues. If you notice unusual behavior on the website, in the launcher, account area or game server, please contact support. Reports like these help us find issues that are difficult to catch with internal testing alone.</p>
HTML;
    }

    private function germanBody(): string
    {
        return <<<'HTML'
<p>Nach dem technischen Start von HiveTech haben wir das Portal, den Launcher und die Anbindung an den Spielserver unter realen Bedingungen weiter getestet. Seitdem haben sich genügend Änderungen angesammelt, um die wichtigsten davon in einem Update zusammenzufassen.</p>

<p>Das Ziel dieser Phase war einfach: kleine und größere Probleme zu beseitigen, die bei der Nutzung störten, und Website sowie Launcher zu einem zusammenhängenden System zu machen.</p>

<h2>Account und Anmeldung</h2>
<p>Wir haben das gemeinsame HiveTech-ID-System weiter verbessert. Website und HiveTech Launcher verwenden denselben Account, daher sind keine getrennten Benutzernamen oder Passwörter erforderlich.</p>
<p>Überarbeitet und geprüft wurden:</p>
<ul>
    <li>die Anmeldung über Website und HiveTech Launcher;</li>
    <li>die Registrierung neuer Spieler;</li>
    <li>die Bestätigung der E-Mail-Adresse;</li>
    <li>Passwort-Wiederherstellung und Passwortänderung;</li>
    <li>das Beenden alter Sitzungen nach einer Passwortänderung;</li>
    <li>das Verhalten des Accounts nach einer Änderung der E-Mail-Adresse;</li>
    <li>der Schutz vor wiederholten und verdächtigen Anmeldeversuchen.</li>
</ul>
<p>Außerdem wurden Fehlermeldungen überarbeitet, damit bei Problemen klar erklärt wird, was passiert ist.</p>

<h2>Skins, Capes und Charakterdarstellung</h2>
<p>Das Textursystem hat eine eigene Überarbeitungsrunde bekommen.</p>
<p>Wir haben die Ausgabe von Skins und Capes im Launcher korrigiert, die Textur-Endpunkte auf HTTPS umgestellt und alles an den HiveTech-Account gebunden.</p>
<p>Im persönlichen Bereich ist jetzt Folgendes möglich:</p>
<ul>
    <li>einen eigenen Skin hochladen;</li>
    <li>ein Cape hochladen, wenn der aktive Status dies erlaubt;</li>
    <li>den aktuellen Skin oder das Cape entfernen;</li>
    <li>für passende Privilegien eine Namensfarbe auswählen;</li>
    <li>die hochgeladene Textur direkt in einer Vorschau sehen.</li>
</ul>
<p>Auch die Prüfung von Dateiformat und Dateigröße sowie die Fehlermeldungen beim Hochladen wurden verbessert.</p>

<h2>Status und Privilegien</h2>
<p>Wir haben das System der Spielstatus und die Synchronisierung mit dem Server weiter ausgebaut.</p>
<p>Das Portal berücksichtigt jetzt die Laufzeit eines Privilegs korrekt, vergibt den passenden Rang im Spiel und entfernt ihn nach Ablauf wieder. Zugehörige Funktionen wie Capes und Namenspersonalisierung werden ebenfalls mit dem aktiven Status synchronisiert.</p>

<h2>Abstimmungen für den Server</h2>
<p>HiveTech verfügt jetzt über ein vollständiges Abstimmungssystem.</p>
<p>TopG und TopCraft sind angebunden. Belohnungen für bestätigte Stimmen werden automatisch als Quarks dem Guthaben des Spielers gutgeschrieben.</p>
<p>Die Stimme wird dem Minecraft-Namen zugeordnet, während wiederholte Callback-Anfragen der Ranking-Dienste gegen doppelte Belohnungen geschützt sind.</p>
<p>Die Abstimmungsdienste sind außerdem im persönlichen Bereich und auf der Startseite erreichbar.</p>

<h2>Launcher-Download</h2>
<p>Ein unangenehmes Problem beim Download des HiveTech Launchers wurde behoben.</p>
<p>Der frühere EXE-Link führte von der HTTPS-Website auf einen normalen HTTP-Port. Moderne Browser konnten diesen Download blockieren oder nur über manuelles Speichern zulassen.</p>
<p>Der Launcher wird jetzt über eine normale HTTPS-Adresse direkt von hivegrid.online heruntergeladen.</p>

<h2>Lokalisierung der Website</h2>
<p>Ein großer Teil der Arbeit floss in die Lokalisierung.</p>
<p>Russisch, Englisch und Deutsch decken jetzt deutlich mehr Bereiche der Website ab:</p>
<ul>
    <li>Registrierung und Anmeldung;</li>
    <li>Passwort-Wiederherstellung;</li>
    <li>den persönlichen Bereich;</li>
    <li>Skins und Capes;</li>
    <li>Privilegien;</li>
    <li>den Shop;</li>
    <li>das Guthaben;</li>
    <li>Abstimmungen;</li>
    <li>Server;</li>
    <li>Systemmeldungen und Fehler.</li>
</ul>
<p>Wir haben Stellen korrigiert, an denen bei englischer oder deutscher Oberfläche unerwartet russischer Text angezeigt wurde.</p>
<p>Bestätigungs-, Passwort- und Support-E-Mails können jetzt ebenfalls in der Sprache des Benutzers versendet werden. Auch News und CMS-Seiten besitzen getrennte Sprachversionen.</p>

<h2>Portal und technische Verbesserungen</h2>
<p>Zusätzlich gab es viele Änderungen unter der Haube.</p>
<p>Das Portal wird jetzt über Git gepflegt. Änderungen sind nachvollziehbar, und PHP-Code wird automatisch auf Syntaxfehler geprüft, bevor neue Anpassungen zusammengeführt werden. Dadurch werden weitere Updates sicherer und vorhersehbarer.</p>
<p>Außerdem arbeiten wir weiter an der visuellen Qualität: Darstellung auf Smartphones und Tablets, Lesbarkeit von Texten, Farbkontraste und das Verhalten längerer Beschriftungen in verschiedenen Sprachen.</p>

<hr>
<p>Genau dafür war der technische Start gedacht: nicht um das Projekt als „für immer fertig“ zu erklären, sondern um das gesamte System im echten Betrieb zu testen und Schwachstellen Schritt für Schritt zu beseitigen.</p>
<p>Die Arbeit geht weiter. Wenn euch ungewöhnliches Verhalten auf der Website, im Launcher, im persönlichen Bereich oder auf dem Spielserver auffällt, meldet euch beim Support. Solche Hinweise helfen uns, Probleme zu finden, die sich mit internen Tests allein nur schwer entdecken lassen.</p>
HTML;
    }
}
