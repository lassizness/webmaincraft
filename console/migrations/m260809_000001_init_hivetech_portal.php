<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260809_000001_init_hivetech_portal extends Migration
{
    private string $options = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->prepareGravitTables();
        $this->prepareUsers();
        $this->createTokens();
        $this->createSessions();
        $this->createLoginAttempts();
        $this->createRbac();
        $this->createContent();
        $this->createSupport();
        $this->createEconomy();
        $this->createServersAndDelivery();
        $this->createSettingsAndAudit();
        $this->seed();
    }

    public function safeDown(): bool
    {
        echo "This migration extends a shared Gravit/user database and is intentionally irreversible.\n";
        return false;
    }

    private function prepareGravitTables(): void
    {
        if (!$this->db->schema->getTableSchema('{{%hwids}}', true)) {
            $this->createTable('{{%hwids}}', [
                'id'=>$this->bigPrimaryKey(),'publickey'=>$this->binary(),'hwDiskId'=>$this->string(255),'baseboardSerialNumber'=>$this->string(255),'graphicCard'=>$this->string(255),'displayId'=>$this->binary(),
                'bitness'=>$this->integer(),'totalMemory'=>$this->bigInteger(),'logicalProcessors'=>$this->integer(),'physicalProcessors'=>$this->integer(),'processorMaxFreq'=>$this->bigInteger(),
                'battery'=>$this->boolean()->notNull()->defaultValue(0),'banned'=>$this->boolean()->notNull()->defaultValue(0),
            ], $this->options);
            $this->createIndex('uq_hwids_publickey','{{%hwids}}','publickey',true);
        }
        if (!$this->db->schema->getTableSchema('{{%hwidLog}}', true)) {
            $this->createTable('{{%hwidLog}}', ['id'=>$this->bigPrimaryKey(),'hwidId'=>$this->bigInteger()->notNull(),'newPublicKey'=>$this->binary()->notNull()],$this->options);
            $this->createIndex('idx_hwidlog_hwid','{{%hwidLog}}','hwidId');
            $this->addForeignKey('fk_hwidlog_hwid','{{%hwidLog}}','hwidId','{{%hwids}}','id','CASCADE');
        }
        if (!$this->db->schema->getTableSchema('{{%user_permissions}}', true)) {
            $this->createTable('{{%user_permissions}}', ['uuid'=>$this->string(36)->notNull(),'name'=>$this->string(100)->notNull()],$this->options);
            $this->createIndex('uq_user_permissions_uuid_name','{{%user_permissions}}',['uuid','name'],true);
        }
    }

    private function prepareUsers(): void
    {
        if (!$this->db->schema->getTableSchema('{{%users}}', true)) {
            $this->createTable('{{%users}}', [
                'id' => $this->bigPrimaryKey()->unsigned(),
                'uuid' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
                'username' => 'varchar(16) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL',
                'email' => $this->string(254)->notNull(),
                'password' => 'varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
                'accessToken' => 'char(32) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
                'serverID' => 'varchar(41) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
                'hwidId' => $this->bigInteger()->null(),
                'status' => "enum('pending','active','blocked') NOT NULL DEFAULT 'pending'",
                'email_verified' => $this->boolean()->notNull()->defaultValue(0),
                'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
                'last_login_at' => $this->dateTime()->null(),
            ], $this->options);
            $this->createIndex('uq_users_username','{{%users}}','username',true);
            $this->createIndex('uq_users_email','{{%users}}','email',true);
            $this->createIndex('uq_users_uuid','{{%users}}','uuid',true);
            $this->createIndex('idx_users_access_token','{{%users}}','accessToken');
            $this->createIndex('idx_users_server_id','{{%users}}','serverID');
            $this->createIndex('idx_users_hwid','{{%users}}','hwidId');
            $this->addForeignKey('users_hwidfk','{{%users}}','hwidId','{{%hwids}}','id','SET NULL');
        } else {
            $this->execute("UPDATE {{%users}} SET uuid=UUID() WHERE uuid IS NULL OR uuid=''");
            $this->execute("ALTER TABLE {{%users}} MODIFY uuid char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL");
            $this->execute("ALTER TABLE {{%users}} MODIFY status enum('pending','active','blocked') NOT NULL DEFAULT 'pending'");
        }
    }

    private function createTokens(): void
    {
        if ($this->db->schema->getTableSchema('{{%user_tokens}}', true)) return;
        $this->createTable('{{%user_tokens}}', [
            'id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),
            'type'=>"enum('email_verify','password_reset') NOT NULL",'token_hash'=>'char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'expires_at'=>$this->dateTime()->notNull(),'used_at'=>$this->dateTime()->null(),'created_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ],$this->options);
        $this->createIndex('uq_user_tokens_hash','{{%user_tokens}}','token_hash',true);$this->createIndex('idx_user_tokens_user_type','{{%user_tokens}}',['user_id','type']);$this->createIndex('idx_user_tokens_expires','{{%user_tokens}}','expires_at');
        $this->addForeignKey('fk_user_tokens_user','{{%user_tokens}}','user_id','{{%users}}','id','CASCADE');
    }

    private function createSessions(): void
    {
        if ($this->db->schema->getTableSchema('{{%web_sessions}}', true)) return;
        $this->createTable('{{%web_sessions}}', [
            'id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'token_hash'=>'char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'ip_address'=>$this->string(45),'user_agent'=>$this->string(512),'expires_at'=>$this->dateTime()->notNull(),'last_seen_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'revoked_at'=>$this->dateTime()->null(),'created_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ],$this->options);
        $this->createIndex('uq_web_sessions_token','{{%web_sessions}}','token_hash',true);$this->createIndex('idx_web_sessions_user','{{%web_sessions}}','user_id');$this->createIndex('idx_web_sessions_expires','{{%web_sessions}}','expires_at');
        $this->addForeignKey('fk_web_sessions_user','{{%web_sessions}}','user_id','{{%users}}','id','CASCADE');
    }

    private function createLoginAttempts(): void
    {
        $this->createTable('{{%login_attempts}}', ['id'=>$this->bigPrimaryKey()->unsigned(),'identifier_hash'=>'char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL','ip_address'=>$this->string(45)->notNull(),'success'=>$this->boolean()->notNull()->defaultValue(0),'created_at'=>$this->dateTime()->notNull()],$this->options);
        $this->createIndex('idx_login_attempts_lookup','{{%login_attempts}}',['identifier_hash','ip_address','created_at']);
    }

    private function createRbac(): void
    {
        $this->createTable('{{%auth_rule}}',['name'=>$this->string(64)->notNull(),'data'=>$this->binary(),'created_at'=>$this->integer(),'updated_at'=>$this->integer(),'PRIMARY KEY(name)'],$this->options);
        $this->createTable('{{%auth_item}}',['name'=>$this->string(64)->notNull(),'type'=>$this->smallInteger()->notNull(),'description'=>$this->text(),'rule_name'=>$this->string(64),'data'=>$this->binary(),'created_at'=>$this->integer(),'updated_at'=>$this->integer(),'PRIMARY KEY(name)'],$this->options);
        $this->createTable('{{%auth_item_child}}',['parent'=>$this->string(64)->notNull(),'child'=>$this->string(64)->notNull(),'PRIMARY KEY(parent, child)'],$this->options);
        $this->createTable('{{%auth_assignment}}',['item_name'=>$this->string(64)->notNull(),'user_id'=>$this->string(64)->notNull(),'created_at'=>$this->integer(),'PRIMARY KEY(item_name, user_id)'],$this->options);
        $this->addForeignKey('fk_auth_item_rule','{{%auth_item}}','rule_name','{{%auth_rule}}','name','SET NULL','CASCADE');
        $this->addForeignKey('fk_auth_child_parent','{{%auth_item_child}}','parent','{{%auth_item}}','name','CASCADE','CASCADE');
        $this->addForeignKey('fk_auth_child_child','{{%auth_item_child}}','child','{{%auth_item}}','name','CASCADE','CASCADE');
        $this->addForeignKey('fk_auth_assignment_item','{{%auth_assignment}}','item_name','{{%auth_item}}','name','CASCADE','CASCADE');
        $this->createIndex('idx_auth_assignment_user','{{%auth_assignment}}','user_id');
    }

    private function createContent(): void
    {
        $this->createTable('{{%news_categories}}',['id'=>$this->primaryKey(),'name'=>$this->string(128)->notNull(),'slug'=>$this->string(128)->notNull()],$this->options);$this->createIndex('uq_news_categories_slug','{{%news_categories}}','slug',true);
        $this->createTable('{{%news}}',['id'=>$this->bigPrimaryKey()->unsigned(),'category_id'=>$this->integer(),'author_id'=>$this->bigInteger()->unsigned(),'title'=>$this->string(255)->notNull(),'slug'=>$this->string(255)->notNull(),'excerpt'=>$this->text(),'body'=>$this->text()->notNull(),'published'=>$this->boolean()->notNull()->defaultValue(0),'published_at'=>$this->dateTime(),'created_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),'updated_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP')],$this->options);$this->createIndex('uq_news_slug','{{%news}}','slug',true);$this->createIndex('idx_news_published','{{%news}}',['published','published_at']);$this->addForeignKey('fk_news_category','{{%news}}','category_id','{{%news_categories}}','id','SET NULL');$this->addForeignKey('fk_news_author','{{%news}}','author_id','{{%users}}','id','SET NULL');
        $this->createTable('{{%pages}}',['id'=>$this->primaryKey(),'title'=>$this->string(255)->notNull(),'slug'=>$this->string(255)->notNull(),'body'=>$this->text()->notNull(),'meta_title'=>$this->string(255),'meta_description'=>$this->string(255),'published'=>$this->boolean()->notNull()->defaultValue(1),'sort'=>$this->integer()->notNull()->defaultValue(100),'created_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),'updated_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP')],$this->options);$this->createIndex('uq_pages_slug','{{%pages}}','slug',true);
        $this->createTable('{{%slides}}',['id'=>$this->primaryKey(),'title'=>$this->string(255)->notNull(),'subtitle'=>$this->text(),'image_url'=>$this->string(255),'button_text'=>$this->string(255),'button_url'=>$this->string(255),'enabled'=>$this->boolean()->notNull()->defaultValue(1),'sort'=>$this->integer()->notNull()->defaultValue(100)],$this->options);
    }

    private function createSupport(): void
    {
        $this->createTable('{{%support_tickets}}',['id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'subject'=>$this->string(255)->notNull(),'status'=>"enum('open','answered','closed') NOT NULL DEFAULT 'open'",'assigned_to'=>$this->bigInteger()->unsigned(),'created_at'=>$this->dateTime()->notNull(),'updated_at'=>$this->dateTime()->notNull()],$this->options);$this->createIndex('idx_support_user','{{%support_tickets}}','user_id');$this->createIndex('idx_support_status','{{%support_tickets}}','status');$this->addForeignKey('fk_support_user','{{%support_tickets}}','user_id','{{%users}}','id','CASCADE');$this->addForeignKey('fk_support_assignee','{{%support_tickets}}','assigned_to','{{%users}}','id','SET NULL');
        $this->createTable('{{%support_messages}}',['id'=>$this->bigPrimaryKey()->unsigned(),'ticket_id'=>$this->bigInteger()->unsigned()->notNull(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'body'=>$this->text()->notNull(),'is_staff'=>$this->boolean()->notNull()->defaultValue(0),'created_at'=>$this->dateTime()->notNull()],$this->options);$this->addForeignKey('fk_support_message_ticket','{{%support_messages}}','ticket_id','{{%support_tickets}}','id','CASCADE');$this->addForeignKey('fk_support_message_user','{{%support_messages}}','user_id','{{%users}}','id','CASCADE');
        $this->createTable('{{%support_attachments}}',['id'=>$this->bigPrimaryKey()->unsigned(),'message_id'=>$this->bigInteger()->unsigned()->notNull(),'stored_name'=>$this->string(255)->notNull(),'original_name'=>$this->string(255)->notNull(),'mime_type'=>$this->string(255)->notNull(),'size'=>$this->integer()->unsigned()->notNull(),'created_at'=>$this->dateTime()->notNull()],$this->options);$this->addForeignKey('fk_support_attachment_message','{{%support_attachments}}','message_id','{{%support_messages}}','id','CASCADE');
    }

    private function createEconomy(): void
    {
        $this->createTable('{{%wallets}}',['id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'balance'=>$this->decimal(14,2)->notNull()->defaultValue(0),'updated_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP')],$this->options);$this->createIndex('uq_wallet_user','{{%wallets}}','user_id',true);$this->addForeignKey('fk_wallet_user','{{%wallets}}','user_id','{{%users}}','id','CASCADE');
        $this->createTable('{{%wallet_transactions}}',['id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'amount'=>$this->decimal(14,2)->notNull(),'balance_after'=>$this->decimal(14,2)->notNull(),'type'=>$this->string(32)->notNull(),'description'=>$this->string(255)->notNull(),'reference_type'=>$this->string(32),'reference_id'=>$this->bigInteger()->unsigned(),'created_at'=>$this->dateTime()->notNull()],$this->options);$this->createIndex('idx_wallet_tx_user','{{%wallet_transactions}}',['user_id','id']);$this->addForeignKey('fk_wallet_tx_user','{{%wallet_transactions}}','user_id','{{%users}}','id','CASCADE');
        $this->createTable('{{%payment_orders}}',['id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'provider'=>$this->string(32)->notNull()->defaultValue('manual'),'amount'=>$this->decimal(14,2)->notNull(),'status'=>"enum('new','paid','cancelled','failed') NOT NULL DEFAULT 'new'",'external_id'=>$this->string(128),'provider_payload'=>$this->text(),'paid_at'=>$this->dateTime(),'created_at'=>$this->dateTime()->notNull(),'updated_at'=>$this->dateTime()->notNull()],$this->options);$this->createIndex('idx_payment_user','{{%payment_orders}}','user_id');$this->createIndex('uq_payment_external','{{%payment_orders}}',['provider','external_id'],true);$this->addForeignKey('fk_payment_user','{{%payment_orders}}','user_id','{{%users}}','id','CASCADE');
        $this->createTable('{{%shop_categories}}',['id'=>$this->primaryKey(),'name'=>$this->string(128)->notNull(),'slug'=>$this->string(128)->notNull(),'enabled'=>$this->boolean()->notNull()->defaultValue(1),'sort'=>$this->integer()->notNull()->defaultValue(100)],$this->options);$this->createIndex('uq_shop_category_slug','{{%shop_categories}}','slug',true);
        $this->createTable('{{%shop_products}}',['id'=>$this->bigPrimaryKey()->unsigned(),'category_id'=>$this->integer(),'server_id'=>$this->integer(),'name'=>$this->string(255)->notNull(),'slug'=>$this->string(255)->notNull(),'description'=>$this->text(),'price'=>$this->decimal(14,2)->notNull(),'delivery_type'=>"enum('none','rcon_command','permission','cosmetic') NOT NULL DEFAULT 'none'",'delivery_payload'=>$this->text(),'enabled'=>$this->boolean()->notNull()->defaultValue(1),'sort'=>$this->integer()->notNull()->defaultValue(100),'created_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),'updated_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP')],$this->options);$this->createIndex('uq_shop_product_slug','{{%shop_products}}','slug',true);$this->addForeignKey('fk_shop_product_category','{{%shop_products}}','category_id','{{%shop_categories}}','id','SET NULL');
        $this->createTable('{{%shop_purchases}}',['id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'product_id'=>$this->bigInteger()->unsigned()->notNull(),'price'=>$this->decimal(14,2)->notNull(),'status'=>$this->string(32)->notNull()->defaultValue('paid'),'created_at'=>$this->dateTime()->notNull()],$this->options);$this->createIndex('idx_purchase_user','{{%shop_purchases}}',['user_id','id']);$this->addForeignKey('fk_purchase_user','{{%shop_purchases}}','user_id','{{%users}}','id','CASCADE');$this->addForeignKey('fk_purchase_product','{{%shop_purchases}}','product_id','{{%shop_products}}','id','RESTRICT');
        $this->createTable('{{%promo_codes}}',['id'=>$this->bigPrimaryKey()->unsigned(),'code'=>'varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL','type'=>"enum('wallet','product') NOT NULL DEFAULT 'wallet'",'value'=>$this->decimal(14,2)->notNull()->defaultValue(0),'product_id'=>$this->bigInteger()->unsigned(),'max_uses'=>$this->integer()->unsigned(),'used_count'=>$this->integer()->unsigned()->notNull()->defaultValue(0),'expires_at'=>$this->dateTime(),'enabled'=>$this->boolean()->notNull()->defaultValue(1),'created_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')],$this->options);$this->createIndex('uq_promo_code','{{%promo_codes}}','code',true);$this->addForeignKey('fk_promo_product','{{%promo_codes}}','product_id','{{%shop_products}}','id','SET NULL');
        $this->createTable('{{%promo_code_usages}}',['id'=>$this->bigPrimaryKey()->unsigned(),'promo_code_id'=>$this->bigInteger()->unsigned()->notNull(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'used_at'=>$this->dateTime()->notNull()],$this->options);$this->createIndex('uq_promo_usage','{{%promo_code_usages}}',['promo_code_id','user_id'],true);$this->addForeignKey('fk_promo_usage_code','{{%promo_code_usages}}','promo_code_id','{{%promo_codes}}','id','CASCADE');$this->addForeignKey('fk_promo_usage_user','{{%promo_code_usages}}','user_id','{{%users}}','id','CASCADE');
    }

    private function createServersAndDelivery(): void
    {
        $this->createTable('{{%servers}}',['id'=>$this->primaryKey(),'name'=>$this->string(255)->notNull(),'code'=>$this->string(64)->notNull(),'host'=>$this->string(255)->notNull(),'port'=>$this->integer()->notNull()->defaultValue(25565),'description'=>$this->string(255),'rcon_env_key'=>$this->string(64),'enabled'=>$this->boolean()->notNull()->defaultValue(1),'sort'=>$this->integer()->notNull()->defaultValue(100)],$this->options);$this->createIndex('uq_server_code','{{%servers}}','code',true);
        $this->addForeignKey('fk_shop_product_server','{{%shop_products}}','server_id','{{%servers}}','id','SET NULL');
        $this->createTable('{{%delivery_queue}}',['id'=>$this->bigPrimaryKey()->unsigned(),'purchase_id'=>$this->bigInteger()->unsigned(),'user_id'=>$this->bigInteger()->unsigned()->notNull(),'server_id'=>$this->integer(),'delivery_type'=>$this->string(32)->notNull(),'payload'=>$this->text(),'status'=>"enum('pending','processing','done','failed') NOT NULL DEFAULT 'pending'",'attempts'=>$this->integer()->notNull()->defaultValue(0),'last_error'=>$this->text(),'available_at'=>$this->dateTime(),'created_at'=>$this->dateTime()->notNull(),'processed_at'=>$this->dateTime()],$this->options);$this->createIndex('idx_delivery_work','{{%delivery_queue}}',['status','available_at','id']);$this->addForeignKey('fk_delivery_purchase','{{%delivery_queue}}','purchase_id','{{%shop_purchases}}','id','SET NULL');$this->addForeignKey('fk_delivery_user','{{%delivery_queue}}','user_id','{{%users}}','id','CASCADE');$this->addForeignKey('fk_delivery_server','{{%delivery_queue}}','server_id','{{%servers}}','id','SET NULL');
    }

    private function createSettingsAndAudit(): void
    {
        $this->createTable('{{%settings}}',['key'=>$this->string(128)->notNull(),'value'=>$this->text(),'updated_at'=>$this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),'PRIMARY KEY(`key`)'],$this->options);
        $this->createTable('{{%audit_log}}',['id'=>$this->bigPrimaryKey()->unsigned(),'user_id'=>$this->bigInteger()->unsigned(),'action'=>$this->string(128)->notNull(),'entity_type'=>$this->string(64),'entity_id'=>$this->string(64),'ip_address'=>$this->string(45),'data_json'=>$this->text(),'created_at'=>$this->dateTime()->notNull()],$this->options);$this->createIndex('idx_audit_user','{{%audit_log}}',['user_id','id']);$this->createIndex('idx_audit_action','{{%audit_log}}',['action','id']);$this->addForeignKey('fk_audit_user','{{%audit_log}}','user_id','{{%users}}','id','SET NULL');
    }

    private function seed(): void
    {
        $now=time();
        foreach ([
            ['user',1,'Authenticated player'],['moderator',1,'Support/content moderator'],['admin',1,'Full administration'],
            ['adminPanel',2,'Open administration panel'],['manageUsers',2,'Manage users and roles'],['manageContent',2,'Manage news/pages/slides'],
            ['manageSupport',2,'Manage support'],['manageShop',2,'Manage shop and promos'],['managePayments',2,'Manage payments'],
            ['manageServers',2,'Manage Minecraft servers'],['manageSettings',2,'Manage project settings'],['viewAudit',2,'View audit log'],
        ] as [$name,$type,$desc]) $this->insert('{{%auth_item}}',['name'=>$name,'type'=>$type,'description'=>$desc,'created_at'=>$now,'updated_at'=>$now]);
        foreach ([['moderator','user'],['moderator','adminPanel'],['moderator','manageContent'],['moderator','manageSupport'],
                  ['admin','moderator'],['admin','manageUsers'],['admin','manageShop'],['admin','managePayments'],['admin','manageServers'],['admin','manageSettings'],['admin','viewAudit']] as [$parent,$child])
            $this->insert('{{%auth_item_child}}',['parent'=>$parent,'child'=>$child]);
        $this->insert('{{%news_categories}}',['name'=>'Общее','slug'=>'general']);
        foreach ([
            ['site.title','HiveTech'],['site.tagline','Технологичный Minecraft-проект'],['maintenance.enabled','0'],['registration.enabled','1'],['shop.enabled','1'],
        ] as [$k,$v]) $this->insert('{{%settings}}',['key'=>$k,'value'=>$v]);
        $this->execute("INSERT IGNORE INTO {{%wallets}} (user_id,balance) SELECT id,0 FROM {{%users}}");
        $this->execute("INSERT IGNORE INTO {{%auth_assignment}} (item_name,user_id,created_at) SELECT 'user',CAST(id AS CHAR),$now FROM {{%users}}");
        foreach ([
            ['Правила проекта','rules','<h2>Правила HiveTech</h2><p>Заполните правила проекта в административной панели.</p>',1,10],
            ['О проекте','about','<h2>HiveTech</h2><p>Игровой Minecraft-проект с единым аккаунтом сайта и лаунчера.</p>',1,20],
        ] as [$title,$slug,$body,$pub,$sort]) $this->insert('{{%pages}}',['title'=>$title,'slug'=>$slug,'body'=>$body,'published'=>$pub,'sort'=>$sort]);
        $this->insert('{{%slides}}',['title'=>'HiveTech','subtitle'=>'Единый аккаунт, собственный лаунчер и развиваемый игровой мир.','button_text'=>'Создать аккаунт','button_url'=>'/register','enabled'=>1,'sort'=>10]);
    }
}
