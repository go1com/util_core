<?php

namespace go1\util\schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use go1\flood\Flood;

class UserSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_user')) {
            $user = $schema->createTable('gc_user');
            $user->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $user->addColumn('uuid', 'string');
            $user->addColumn('ulid', Types::STRING, ['length' => 30, 'notnull' => false]);
            $user->addColumn('user_uuid', 'string', ['notnull' => false]);
            $user->addColumn('instance', 'string');
            $user->addColumn('profile_id', 'integer', ['notnull' => false]);
            $user->addColumn('mail', 'string');
            $user->addColumn('password', 'string');
            $user->addColumn('created', 'integer');
            $user->addColumn('access', 'integer');
            $user->addColumn('login', 'integer');
            $user->addColumn('status', 'integer');
            $user->addColumn('first_name', 'string');
            $user->addColumn('last_name', 'string');
            $user->addColumn('allow_public', 'integer', ['default' => 0]);
            $user->addColumn('data', 'text');
            $user->addColumn('timestamp', 'integer');
            $user->addColumn('locale', 'string', ['length' => 12, 'notnull' => false]);
            $user->addColumn('user_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $user->addColumn('job_role', 'string', ['notnull' => false]);
            $user->addColumn('migrated', Types::BOOLEAN, ['notnull' => true, 'default' => false]);

            $user->setPrimaryKey(['id']);
            $user->addIndex(['uuid']);
            $user->addIndex(['mail']);
            $user->addIndex(['created']);
            $user->addIndex(['login']);
            $user->addIndex(['timestamp']);
            $user->addIndex(['instance']);
            $user->addIndex(['user_uuid']);
            $user->addUniqueIndex(['uuid']);
            $user->addUniqueIndex(['ulid']);
            $user->addUniqueIndex(['instance', 'mail']);
            $user->addUniqueIndex(['instance', 'profile_id']);
            $user->addForeignKeyConstraint('gc_user', ['user_id'], ['id']);
        } else {
            $user = $schema->getTable('gc_user');
            if (!$user->hasColumn('user_id')) {
                $user->addColumn('user_id', 'integer', ['unsigned' => true, 'notnull' => false]);
                $user->addForeignKeyConstraint('gc_user', ['user_id'], ['id']);
            }
        }

        if (!$schema->hasTable('gc_role')) {
            $role = $schema->createTable('gc_role');
            $role->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $role->addColumn('instance', 'string');
            $role->addColumn('rid', 'integer', ['unsigned' => true]);
            $role->addColumn('name', 'string');
            $role->addColumn('weight', 'integer', ['size' => 'tiny', 'default' => 0]);
            $role->addColumn('permission', 'text', ['notnull' => false]);
            $role->setPrimaryKey(['id']);
            $role->addIndex(['instance', 'name', 'weight']);
        }

        if (!$schema->hasTable('gc_user_mail')) {
            $mail = $schema->createTable('gc_user_mail');
            $mail->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $mail->addColumn('user_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $mail->addColumn('title', 'string');
            $mail->addColumn('verified', 'integer', ['size' => 'tiny', 'default' => 0]);
            $mail->setPrimaryKey(['id']);
            $mail->addIndex(['title']);
            $mail->addIndex(['user_id']);
            $mail->addUniqueIndex(['user_id', 'title']);
            $user->addForeignKeyConstraint('gc_user', ['user_id'], ['id']);
        }

        if (!$schema->hasTable('gc_account_managers')) {
            $tbl = $schema->createTable('gc_account_managers');
            $tbl->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $tbl->addColumn('account_id', Types::INTEGER, ['unsigned' => true, 'length' => 10]);
            $tbl->addColumn('manager_account_id', Types::INTEGER, ['unsigned' => true, 'length' => 10]);
            $tbl->addColumn('created_at', Types::INTEGER);
            $tbl->setPrimaryKey(['id']);
            $tbl->addForeignKeyConstraint('gc_user', ['account_id'], ['id'], ['onDelete' => 'CASCADE']);
            $tbl->addForeignKeyConstraint('gc_user', ['manager_account_id'], ['id'], ['onDelete' => 'CASCADE']);
            $tbl->addUniqueIndex(['account_id', 'manager_account_id']);
        }

        if (!$schema->hasTable('gc_flood')) {
            if (class_exists(Flood::class)) {
                Flood::migrate($schema, 'gc_flood');
            }
        }

        if (!$schema->hasTable('user_stream')) {
            $stream = $schema->createTable('user_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'default' => 0]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['user_id']);
            $stream->addIndex(['created']);
        }

        if (!$schema->hasTable('account_stream')) {
            $stream = $schema->createTable('account_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('portal_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('account_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'default' => 0]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['portal_id']);
            $stream->addIndex(['account_id']);
            $stream->addIndex(['created']);
        }
    }

    public static function createViews(Connection $db, string $accountsName)
    {
        $manager = $db->getSchemaManager();
        $manager->createView(new View('gc_users', "SELECT * FROM gc_user WHERE instance = '{$accountsName}'"));
        $manager->createView(new View('gc_accounts', "SELECT * FROM gc_user WHERE instance <> '{$accountsName}'"));
    }

    public static function update01(Schema $schema)
    {
        $table = $schema->getTable('gc_user');
        if (!$table->hasColumn('locale')) {
            $table->addColumn('locale', 'string', ['length' => 12, 'notnull' => false]);
        }
    }

    public static function update02(Schema $schema)
    {
        $schema->hasTable('gc_user_locale') && $schema->dropTable('gc_user_locale');
    }

    public static function update03(Schema $schema)
    {
        $table = $schema->getTable('gc_user_mail');
        if (!$table->hasColumn('user_id')) {
            $table->addColumn('user_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $table->addIndex(['user_id']);
            $table->addForeignKeyConstraint('gc_user', ['user_id'], ['id']);
        }
        if (!$table->hasColumn('verified')) {
            $table->addColumn('verified', 'integer', ['size' => 'tiny', 'default' => 0]);
        }
        if ($table->hasColumn('user_id') && $table->hasColumn('verified')) {
            !$table->hasIndex('uniq_user_id_email') && $table->addUniqueIndex(['user_id', 'title'], 'uniq_user_id_email');
        }
    }

    public static function update06(Schema $schema) {

        if ($schema->hasTable('gc_user')) {
            $userTable = $schema->getTable('gc_user');
            if (!$userTable->hasColumn('ulid')) {
                //Length needs to allow for prefix which consists of a 3-character abbreviation plus a dash (4 in total)
                //ulid needs to be nullable until after we have generated and stored ulid's for all users, leave as false for now
                $userTable->addColumn('ulid', Types::STRING, ['length' => 30, 'notnull' => false]);
                $userTable->addUniqueIndex(['ulid']);
                $userTable->addIndex(['ulid']);
            }
        }
    }

    public static function update04(Schema $schema)
    {
        if ($schema->hasTable('user_stream')) {
            $userStream = $schema->getTable('user_stream');
            if (!$userStream->hasColumn('actor_id')) {
                $userStream->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            }
        }

        if ($schema->hasTable('account_stream')) {
            $accountStream = $schema->getTable('account_stream');
            if (!$accountStream->hasColumn('actor_id')) {
                $accountStream->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            }
        }
    }

    public static function update05(Schema $schema)
    {
        if($schema->hasTable('gc_user')) {
            $userTable = $schema->getTable('gc_user');
            if (!$userTable->hasColumn('migrated')) {
                $userTable->addColumn('migrated', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
            }
        }
    }
}
