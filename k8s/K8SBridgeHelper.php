<?php

namespace go1\util\k8s;

/**
 * Class K8SBridgeHelper
 *
 * @package go1\util\k8s
 * Class to help simplify dealing with Bridge to Azure ENV variables, rewriting ENV names to be compatible with our apps
 */
class K8SBridgeHelper
{
    public function init()
    {
        if (getenv('AZURE_BRIDGE_MODE') && !getenv('AZURE_BRIDGE_MODE_INITIATED')) {
            // prevent this from being run every call
            putenv("AZURE_BRIDGE_MODE_INITIATED=true");

            // Rewrite DB ENVs
            /**
             * In the KubernetesLocalProcessConfig.yaml:
             * - name: RDS_DB_HOST_OVERRIDE
             * value: $(externalendpoints:10.10.12.5:3306)
             * Results in following ENV variables:
             * [RDS_DB_HOST_OVERRIDE] => 10.10.12.5
             * ...
             * [10_10_12_5_SERVICE_HOST] => 127.1.1.1
             * [10_10_12_5_SERVICE_PORT] => 55054
             * Which means we need to take the value of RDS_DB_HOST_OVERRIDE and look for the other ENV variables
             * to get the right value
             */
            if ($dbHostOverride = getenv('RDS_DB_HOST_OVERRIDE')) {
                [$dbHost, $dbPort] = self::getServiceEnvValues($dbHostOverride);
                $masterUsername = getenv('RDS_DB_USERNAME');
                $masterPassword = getenv('RDS_DB_PASSWORD');
                // Rewrite DB ENVs
                if ($dbHost) {
                    // overwrite hosts
                    putenv("RDS_DB_HOST={$dbHost}");
                    putenv("RDS_DB_HOST_READER={$dbHost}");
                    putenv("RDS_DB_SLAVE={$dbHost}");
                    putenv("MYSQL_HOST={$dbHost}");
                    putenv("RDS_SSL_DB_HOST={$dbHost}");
                    putenv("RDS_SSL_DB_HOST_READER={$dbHost}");
                    putenv("RDS_SSL_DB_SLAVE={$dbHost}");
                }
                if ($masterUsername) {
                    // overwrite username
                    putenv("RDS_DB_USERNAME_READER={$masterUsername}");
                    putenv("RDS_DB_USERNAME_SLAVE={$masterUsername}");
                    putenv("RDS_SSL_DB_USERNAME_READER={$masterUsername}");
                    putenv("RDS_SSL_DB_USERNAME_SLAVE={$masterUsername}");
                }
                if ($masterPassword) {
                    // overwrite password
                    putenv("RDS_DB_PASSWORD_READER={$masterPassword}");
                    putenv("RDS_DB_PASSWORD_SLAVE={$masterPassword}");
                    putenv("RDS_SSL_DB_PASSWORD_READER={$masterPassword}");
                    putenv("RDS_SSL_DB_PASSWORD_SLAVE={$masterPassword}");
                }
                if ($dbPort) {
                    putenv("MYSQL_PORT={$dbPort}");
                    putenv("RDS_DB_PORT={$dbPort}");
                    putenv("RDS_SSL_DB_PORT={$dbPort}");
                }
            }

            // Rewrite Redis ENVs
            if ($redisHostOverride = getenv('REDIS_HOST_OVERRIDE')) {
                [$redisHost, $redisPort] = self::getServiceEnvValues($redisHostOverride);
                $redisAuth = getenv('REDIS_AUTH');
                if ($redisHost && $redisAuth && $redisPort) {
                    putenv("REDIS_HOST=" . $redisHost);
                    putenv("REDIS_DSN=redis://:{$redisAuth}@{$redisHost}:{$redisPort}");
                    putenv("REDIS_PORT={$redisPort}");
                    putenv("REDIS_TCP=tcp://{$redisHost}:{$redisPort}?auth={$redisAuth}");
                    putenv("REDIS_TCP_READER=tcp://{$redisHost}:{$redisPort}?auth={$redisAuth}");
                }
            }

            // Rewrite MemCached ENVs
            if (getenv('CACHE_HOST_OVERRIDE')) {
                [$memCachedHost, $memCachedPort] = self::getServiceEnvValues('k8s-qa-memcached', 'k8s-qa');
                $cachBackend = getenv('CACHE_BACKEND');
                if ($memCachedHost && $memCachedPort && $cachBackend === 'memcached') {
                    putenv("CACHE_HOST={$memCachedHost}");
                    putenv("CACHE_PORT={$memCachedPort}");
                }
            }

            // Rewrite Rabbit Queue ENVs
            if (getenv('QUEUE_HOST_OVERRIDE')) {
                [$queueHost, $queuePort] = self::getServiceEnvValues('rabbitmq', 'k8s-qa', 'amqp');
                $queuePass = getenv('QUEUE_PASSWORD');
                $queueUser = getenv('QUEUE_USER');
                if ($queueHost && $queuePort && $queuePass && $queueUser) {
                    $queueUrl = "amqp://{$queueUser}:{$queuePass}@{$queueHost}:{$queuePort}";
                    putenv("QUEUE_HOST={$queueHost}");
                    putenv("QUEUE_PORT={$queuePort}");
                    putenv("QUEUE_URL={$queueUrl}");
                    putenv("RABBITMQ_URL={$queueUrl}");
                }
            }

            // Rewrite ES ENVs
            if ($esHostOverride = getenv('ES_URL_AU_V8_HOST_OVERRIDE')) {
                [$esHost, $esPort] = self::getServiceEnvValues($esHostOverride);
                $esUrl = getenv('ES_URL_AU_V8');
                if ($esHost && $esPort && $esUrl) {
                    $prefix = explode('@', $esUrl)[0];
                    putenv("ES_URL_AU_V8={$prefix}@{$esHost}:{$esPort}");
                }
            }
        }
    }

    public static function k8sEnvNameTransform(string $name): string
    {
        # https://github.com/Azure/Bridge-To-Kubernetes/blame/3b208325c25bbc10db440dd7035c245cc8a78446/src/library/Connect/LocalEnvironmentManager.cs#L453
        return strtoupper(str_replace([".", "-"], "_", explode(".", $name)[0]));
    }

    public static function getServiceEnvValues(string $name, string $ns = 'k8s-qa', string $portName = 'http'): array
    {
        return [
            self::getHostByNamePreferNs($name, $ns),
            self::getPortByNamePreferNs($name, $ns, $portName)
        ];
    }

    public static function getHostByNamePreferNs(string $name, string $ns = 'k8s-qa'): string
    {
        $transformedName = self::k8sEnvNameTransform($name);
        $transformedNs = self::k8sEnvNameTransform($ns);

        $host = getenv("{$transformedName}_SERVICE_HOST");
        $nsHost = getenv("{$transformedName}_{$transformedNs}_SERVICE_HOST");

        return $nsHost ?: $host; // prefer ns specified host
    }

    public static function getPortByNamePreferNs(string $name, string $ns = 'k8s-qa', string $portName = 'http'): string
    {
        $transformedName = self::k8sEnvNameTransform($name);
        $transformedPortName = self::k8sEnvNameTransform($portName);
        $transformedNs = self::k8sEnvNameTransform($ns);

        $port = getenv("{$transformedName}_SERVICE_PORT");
        $namedPort = getenv("{$transformedName}_SERVICE_PORT_{$transformedPortName}");
        $nsPort = getenv("{$transformedName}_{$transformedNs}_SERVICE_PORT");
        $nsNamedPort = getenv("{$transformedName}_{$transformedNs}_SERVICE_PORT_{$transformedPortName}");

        return $nsNamedPort ?: $nsPort ?: $namedPort ?: $port; // prefer ns specified port
    }
}
