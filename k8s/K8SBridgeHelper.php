<?php
namespace go1\util\k8s;

/**
 * Class K8SBridgeHelper
 * @package go1\util\k8s
 * Class to help simplify dealing with Bridge to Azure ENV variables, rewriting ENV names to be compatible with our apps
 */
class K8SBridgeHelper {
    public function init()
    {
        if (getenv('AZURE_BRIDGE_MODE') && !getenv('AZURE_BRIDGE_MODE_INITIATED')) {
            // prevent this from being run every call
            putenv("AZURE_BRIDGE_MODE_INITIATED=true");

            // Rewrite DB ENVs
            /**
             * In the KubernetesLocalProcessConfig.yaml:
                - name: RDS_DB_HOST_OVERRIDE
                    value: $(externalendpoints:10.10.12.5:3306)
               Results in following ENV variables:
                [RDS_DB_HOST_OVERRIDE] => 10.10.12.5
                ...
                [10_10_12_5_SERVICE_HOST] => 127.1.1.1
                [10_10_12_5_SERVICE_PORT] => 55054
             * Which means we need to take the value of RDS_DB_HOST_OVERRIDE and look for the other ENV variables
             * to get the right value
             */
            if ($dbHostOverride = getenv('RDS_DB_HOST_OVERRIDE')) {
                $dbEnvName = $this->k8sEnvNameTransform($dbHostOverride);
                list($dbHost, $dbPort) = $this->getServiceEnvValues($dbEnvName);
                // Rewrite DB ENVs
                if ($dbHost) {
                    $masterUsername = getenv('RDS_DB_USERNAME');
                    $masterPassword = getenv('RDS_DB_PASSWORD');
                    // overwrite hosts
                    putenv("RDS_DB_HOST={$dbHost}");
                    putenv("RDS_DB_HOST_READER={$dbHost}");
                    putenv("RDS_DB_SLAVE={$dbHost}");
                    putenv("MYSQL_HOST={$dbHost}");
                    putenv("RDS_SSL_DB_HOST={$dbHost}");
                    putenv("RDS_SSL_DB_HOST_READER={$dbHost}");
                    putenv("RDS_SSL_DB_SLAVE={$dbHost}");
                    // overwrite username
                    putenv("RDS_DB_USERNAME_READER={$masterUsername}");
                    putenv("RDS_DB_USERNAME_SLAVE={$masterUsername}");
                    putenv("RDS_SSL_DB_USERNAME_READER={$masterUsername}");
                    putenv("RDS_SSL_DB_USERNAME_SLAVE={$masterUsername}");
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
                $redisHostEnvName = $this->k8sEnvNameTransform($redisHostOverride);
                list($redisHost, $redisPort) = $this->getServiceEnvValues($redisHostEnvName);
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
            if ($memCachedHostOverride = getenv('CACHE_HOST_OVERRIDE')) {
                $memCachedHostEnvName = $this->k8sEnvNameTransform($memCachedHostOverride);
                list($memCachedHost, $memCachedPort) = $this->getServiceEnvValues($memCachedHostEnvName);
                $cachBackend = getenv('CACHE_BACKEND');
                if ($memCachedHost && $memCachedPort && $cachBackend === 'memcached') {
                    putenv("CACHE_HOST={$memCachedHost}");
                    putenv("CACHE_PORT={$memCachedPort}");
                }
            }

            // Rewrite Rabbit Queue ENVs
            if ($queueHostOverride = getenv('QUEUE_HOST_OVERRIDE')) {
                $queueHostEnvName = $this->k8sEnvNameTransform($queueHostOverride);
                list($queueHost, $queuePort) = $this->getServiceEnvValues($queueHostEnvName);
                $queuePass = getenv('QUEUE_PASSWORD');
                $queueUser = getenv('QUEUE_USER');
                if ($memCachedHost && $memCachedPort && $cachBackend === 'memcached') {
                    $queueUrl = "amqp://{$queueUser}:{$queuePass}@{$queueHost}:{$queuePort}";
                    putenv("QUEUE_HOST={$queueHost}");
                    putenv("QUEUE_PORT={$queuePort}");
                    putenv("QUEUE_URL={$queueUrl}");
                    putenv("RABBITMQ_URL={$queueUrl}");
                }
            }

            // Rewrite other Service ENVs, replacing namespace, allowing uniformed treatment for local, QA and Prod
            $env = getenv();
            foreach ($env as $key => $value) {
                if (stripos($key, "_K8S_QA")) {
                    // Save new without namspace
                    putenv(str_replace("_K8S_QA", "", $key) . "=" . $value);
                    // Remove old to avoid confusion
                    putenv($key);
                }
            }
        }
    }

    public function k8sEnvNameTransform( string $name) : string {
       return strtoupper(str_replace([".", "-"], "_", $name));
    }

    public function getServiceEnvValues(string $serviceName) : array {
        return [getenv("{$serviceName}_SERVICE_HOST"), getenv("{$serviceName}_SERVICE_PORT")];
    }
}
