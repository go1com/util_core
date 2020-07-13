<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use GuzzleHttp\Client;
use PDO;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use function array_fill_keys;
use function array_map;

class UserHelper
{
    const ROOT_JWT                     = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';
    const DEFAULT_ACCOUNTS_ROLES       = [Roles::AUTHENTICATED];
    const DEFAULT_PORTAL_ROLES         = [Roles::STUDENT, Roles::AUTHENTICATED];
    const SYSTEM_USER_ID               = -1;
    const CRON_USER_ID                 = -11;
    const INTERACTIVE_ADMIN_USER_ID    = -100;
    const INTERACTIVE_ADMIN_PROFILE_ID = -100;
    const QUIZ_ADMIN_USER_ID           = -200;
    const QUIZ_ADMIN_PROFILE_ID        = -200;

    public static function isEmbeddedPortalActive(stdClass $user): bool
    {
        $portals = $user->embedded->portal ?? null;
        $portal = is_array($portals) ? array_shift($portals) : $portals;

        return $portal ? $portal->status : true;
    }

    /**
     * @deprecated Use UserDomainHelper::load()
     */
    public static function load(Connection $db, int $id, string $instance = null, $columns = '*')
    {
        $sql = "SELECT $columns FROM gc_user WHERE id = ?";
        $params = [$id];

        if ($instance) {
            $sql .= ' AND instance = ?';
            $params[] = $instance;
        }

        return $db->executeQuery($sql, $params)->fetch(DB::OBJ);
    }

    /**
     * @deprecated Use UserDomainHelper::loadByEmail()
     */
    public static function loadByEmail(Connection $db, string $instance, string $mail, $columns = '*')
    {
        return $db
            ->executeQuery("SELECT $columns FROM gc_user WHERE instance = ? AND mail = ?", [$instance, $mail])
            ->fetch(DB::OBJ);
    }

    public static function queryMultiple(Connection $db, array $ids, string $columns = '*')
    {
        return $db->executeQuery("SELECT $columns FROM gc_user WHERE id IN (?)", [$ids], [Connection::PARAM_INT_ARRAY]);
    }

    /**
     * @deprecated Use UserDomainHelper::loadMultiple().
     */
    public static function loadMultiple(Connection $db, array $ids, string $columns = '*'): array
    {
        return self::queryMultiple($db, $ids, $columns)->fetchAll(DB::OBJ);
    }

    /**
     * @deprecated Use UserDomainHelper::loadByProfileId().
     */
    public static function loadByProfileId(Connection $db, int $profileId, string $portalName, $columns = '*')
    {
        $sql = "SELECT $columns FROM gc_user WHERE profile_id = ? AND instance = ?";

        return $db->executeQuery($sql, [$profileId, $portalName])->fetch(DB::OBJ);
    }

    /**
     * @deprecated Use UserDomainHelper::uuidByProfileId().
     */
    public static function uuidByProfileId(Connection $db, string $accountsName, int $profileId)
    {
        return $db->fetchColumn('SELECT uuid FROM gc_user WHERE instance = ? AND profile_id = ?', [$accountsName, $profileId]);
    }

    /**
     * @deprecated Use UserDomainHelper::uuidByProfileId().
     */
    public function profileId2uuid(Client $client, $userUrl, $profileId)
    {
        $jwt = JWT::encode(['admin' => true], 'INTERNAL');
        $url = rtrim($userUrl, '/') . "/account/masquerade/-/{$profileId}?jwt=$jwt";
        $res = $client->get($url, ['https_errors' => false]);

        return (200 == $res->getStatusCode())
            ? json_decode($res->getBody()->getContents())->uuid
            : false;
    }

    public static function name(stdClass $user, bool $last = false)
    {
        $name = $last ? "{$user->first_name} {$user->last_name}" : $user->first_name;

        return trim($name) ?: $user->mail;
    }

    /**
     * @deprecated Don't use this method.
     */
    public static function firstName(Connection $db, stdClass $account, string $accountsName)
    {
        if ($account->instance != $accountsName) {
            $user = static::loadByEmail($db, $accountsName, $account->mail);
        } else {
            $user = $account;
        }

        return ($user && $user->first_name) ? $user->first_name : '';
    }

    /**
     * @deprecated Don't use this method.
     */
    public static function lastName(Connection $db, stdClass $account, string $accountsName)
    {
        if ($account->instance != $accountsName) {
            $user = static::loadByEmail($db, $accountsName, $account->mail);
        } else {
            $user = $account;
        }

        return ($user && $user->last_name) ? $user->last_name : '';
    }

    /**
     * @deprecated Use AccessChecker
     */
    public static function jwt(Request $req)
    {
        if ($auth = $req->headers->get('Authorization') ?: $req->headers->get('Authorization')) {
            if (0 === strpos($auth, 'Bearer ')) {
                return substr($auth, 7);
            }
        }

        if (!$token = $req->query->get('jwt', isset($token))) {
            if (!$token = $req->cookies->get('jwt')) {
                return false;
            }
        }

        return (2 === substr_count($token, '.')) ? $token : false;
    }

    /**
     * @deprecated Don't use this method.
     */
    public static function authorizationHeader(Request $req)
    {
        if (!$jwt = static::jwt($req)) {
            throw new RuntimeException('JWT not found.');
        }

        return [
            'Content-Type'  => 'application/json',
            'Authorization' => "Bearer $jwt",
        ];
    }

    public static function encode(stdClass &$payload): string
    {
        $array = isset($payload->object->content) ? $payload : [
            'iss'    => 'go1.user',
            'ver'    => '2.0',
            'exp'    => strtotime('+ 1 month'),
            'object' => ['type' => 'user', 'content' => $payload],
        ];

        return JWT::encode($array, 'INTERNAL');
    }

    /**
     * @deprecated Use UserMapper
     */
    public static function format(stdClass $user)
    {
        $data = isset($user->data) ? (is_scalar($user->data) ? json_decode($user->data, true) : $user->data) : null;

        return (object) [
            'id'         => (int) $user->id,
            'instance'   => $user->instance,
            'mail'       => $user->mail,
            'name'       => "{$user->first_name} {$user->last_name}",
            'profile_id' => (int) $user->profile_id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'roles'      => $user->roles ?? ($data['roles'] ?? null),
            'avatar'     => $user->avatar ?? ($data['avatar']['uri'] ?? null),
            'created'    => (int) $user->created,
            'login'      => (int) $user->login,
            'status'     => (bool) $user->status,
            'data'       => (object) (is_array($data) ? array_diff_key($data, ['avatar' => 0, 'roles' => 0, 'phone' => 0]) : $data),
            'timestamp'  => intval($user->timestamp),
            'locale'     => $user->locale ?? null,
            'phone'      => $data['phone'] ?? null,
            'root'       => null,
        ];
    }

    /**
     * @deprecated Use UserDomainHelper.
     */
    public function attachRootAccount(Connection $db, array &$accounts, $accountsName)
    {
        $q = $db->createQueryBuilder();
        $q = $q
            ->select('u.id, u.mail, u.profile_id')
            ->from('gc_user', 'u')
            ->where($q->expr()->in('u.mail', ':mails'))
            ->andWhere('u.instance = :instance')
            ->setParameter(':mails', array_column($accounts, 'mail'), Connection::PARAM_STR_ARRAY)
            ->setParameter(':instance', $accountsName)
            ->execute();

        while ($user = $q->fetch(DB::OBJ)) {
            foreach ($accounts as &$account) {
                if (is_array($account)) {
                    if ($user->mail == $account['mail']) {
                        $account['root'] = [
                            'id'         => (int) $user->id,
                            'profile_id' => (int) $user->profile_id,
                        ];
                    }
                } else {
                    if ($user->mail == $account->mail) {
                        $account->root = [
                            'id'         => (int) $user->id,
                            'profile_id' => (int) $user->profile_id,
                        ];
                    }
                }
            }
        }
    }

    public function userRoles(Connection $db, int $userId, string $instance)
    {
        $roleIds = 'SELECT target_id FROM gc_ro WHERE type = ? AND source_id = ?';
        $roleIds = $db->executeQuery($roleIds, [EdgeTypes::HAS_ROLE, $userId])->fetchAll(PDO::FETCH_COLUMN);

        return $roleIds
            ? $db
                ->executeQuery(
                    'SELECT name FROM gc_role WHERE instance = ? AND id IN (?)',
                    [$instance, $roleIds],
                    [DB::STRING, DB::INTEGERS]
                )
                ->fetchAll(PDO::FETCH_COLUMN)
            : [];
    }

    public static function roleId(Connection $db, string $roleName, string $instance)
    {
        return $db->fetchColumn('SELECT id FROM gc_role WHERE name = ? AND instance = ?', [$roleName, $instance]);
    }

    public static function isStaff(array $userRoles = null): bool
    {
        if (empty($userRoles)) {
            return false;
        }

        $staffRoles = array_diff(Roles::ACCOUNTS_ROLES, [Roles::AUTHENTICATED]);
        foreach ($userRoles as $userRole) {
            if (in_array($userRole, $staffRoles)) {
                return true;
            }
        }

        return false;
    }

    public static function userInstanceIds(Connection $db, string $mail): array
    {
        $sql = 'SELECT gc_instance.id FROM gc_instance ';
        $sql .= 'INNER JOIN gc_user ON gc_instance.title = gc_user.instance ';
        $sql .= 'WHERE mail = ?';

        return $db->executeQuery($sql, [$mail])->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @deprecated Use UserDomainHelper::loadPortalAccount()->user->legacyId.
     */
    public static function userId(Connection $db, int $accountId, string $accountsName)
    {
        if ($account = self::load($db, $accountId)) {
            if ($account->instance == $accountsName) {
                return $account->id;
            } else {
                if ($user = self::loadByEmail($db, $accountsName, $account->mail)) {
                    return $user->id;
                }
            }
        }

        return null;
    }

    /**
     * @deprecated Use UserDomainHelper::loadUserByProfileId()
     */
    public static function loadUserByProfileId(Connection $db, int $profileId, string $columns = '*'): ?stdClass
    {
        $user = $db
            ->executeQuery("SELECT $columns FROM gc_users WHERE profile_id = ?", [$profileId], [DB::INTEGER])
            ->fetch(DB::OBJ);

        return $user ?: null;
    }

    public static function userIdsToAccountIds(Connection $db, string $portalName, array $userIds): array
    {
        $userIds = array_map('intval', $userIds);
        if (!$userIds) {
            return [];
        }

        $q = 'SELECT gc_ro.source_id AS userId, acc.id AS accountId';
        $q .= ' FROM gc_accounts acc';
        $q .= ' INNER JOIN gc_ro ON gc_ro.type = ? AND gc_ro.source_id IN (?) AND acc.id = gc_ro.target_id';
        $q .= ' WHERE acc.instance = ?';
        $q = $db->executeQuery($q, [EdgeTypes::HAS_ACCOUNT, $userIds, $portalName], [DB::INTEGER, DB::INTEGERS, DB::STRING]);

        $results = array_fill_keys($userIds, null);
        while ($_ = $q->fetch(DB::OBJ)) {
            $results[(int) $_->userId] = (int) $_->accountId;
        }

        return $results;
    }

    public static function accountIdsToUserIds(Connection $db, array $accountIds): array
    {
        $accountIds = array_map('intval', $accountIds);
        if (!$accountIds) {
            return [];
        }

        $q = 'SELECT source_id AS userId, target_id AS accountId';
        $q .= ' FROM gc_ro';
        $q .= ' WHERE type = ? AND target_id IN (?)';
        $q = $db->executeQuery($q, [EdgeTypes::HAS_ACCOUNT, $accountIds], [DB::INTEGER, DB::INTEGERS, DB::INTEGER]);

        $results = array_fill_keys($accountIds, null);
        while ($_ = $q->fetch(DB::OBJ)) {
            $results[(int) $_->accountId] = (int) $_->userId;
        }

        return $results;
    }

    public static function getAccountIds(Connection $db, int $userId): array
    {
        $accountIds = $db
            ->executeQuery(
                "SELECT target_id FROM gc_ro ro WHERE type = ? AND source_id = ?",
                [EdgeTypes::HAS_ACCOUNT, $userId]
            )
            ->fetchAll(PDO::FETCH_COLUMN);

        return array_map('intval', $accountIds);
    }

    public static function getPortalJWT(stdClass $portal): string
    {
        $payload = [
            'iss'    => 'go1.user',
            'ver'    => '1.0',
            'exp'    => strtotime('+ 1 month'),
            'object' => [
                'type'    => 'user',
                'content' => [
                    'id'         => 1,
                    'profile_id' => 1,
                    'mail'       => "user.0@{$portal->title}",
                    'name'       => 'public',
                    'accounts'   => [
                        [
                            'id'         => 1,
                            'profile_id' => 1,
                            'instance'   => $portal->title,
                            'portal_id'  => (int) $portal->id,
                            'name'       => 'public',
                            'roles'      => [Roles::STUDENT],
                        ],
                    ],
                ],
            ],
        ];

        return JWT::encode($payload, 'INTERNAL');
    }
}
