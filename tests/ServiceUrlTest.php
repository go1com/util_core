<?php

namespace go1\util\tests;

use go1\util\Service;
use PHPUnit\Framework\TestCase;

class ServiceUrlTest extends TestCase
{
    public function testGatewayUrls()
    {
        $this->assertEquals('https://api.go1.co', Service::gatewayUrl('production', true));
        $this->assertEquals('https://api-dev.go1.co', Service::gatewayUrl('dev', true));
        $this->assertEquals('https://api-staging.go1.co', Service::gatewayUrl('staging', true));
        $this->assertEquals('http://gateway.production', Service::gatewayUrl('production', false));
        $this->assertEquals('http://gateway.dev', Service::gatewayUrl('dev', false));
        $this->assertEquals('http://gateway.staging', Service::gatewayUrl('staging', false));
    }

    public function testServiceUrls()
    {
        $this->assertEquals('http://iam.qa.go1.service', Service::url('iam', 'qa', 'http://SERVICE.ENVIRONMENT.go1.service'));
        $this->assertEquals('http://iam.k8s-qa', Service::url('iam', 'k8s-qa'));
        $this->assertEquals('http://account-fields.k8s-qa', Service::url('account-fields', 'k8s-qa'));
        $this->assertEquals('account-fields.k8s-qa:5000', Service::url('grpc-account-fields', 'staging', 'http://SERVICE.k8s-qa'));

        putenv('FOO_DB_NAME=foo_db');
    }

    public function testServiceUrlsForB2k()
    {
        putenv('AZURE_BRIDGE_MODE=true');
        $env = "b2kirrelevant";
        $proxyService = 'BRIDGE_TO_K8S_PROXY';

        putenv("{$proxyService}_SERVICE_HOST=foo");
        putenv("{$proxyService}_SERVICE_PORT=80");
        $this->assertEquals('http://foo:80/jedi', Service::url('jedi', $env));

        // namespaced env vars preferred
        putenv("{$proxyService}_K8S_QA_SERVICE_HOST=bar");
        putenv("{$proxyService}_K8S_QA_SERVICE_PORT=8080");
        $this->assertEquals('http://bar:8080/jedi', Service::url('jedi', $env));

        putenv('ACCOUNT_FIELDS_SERVICE_HOST=foo');
        putenv('ACCOUNT_FIELDS_SERVICE_PORT_GRPC=4444');
        $this->assertEquals('foo:4444', Service::url('grpc-account-fields', $env));

        // namespaced env vars preferred
        putenv('ACCOUNT_FIELDS_K8S_QA_SERVICE_HOST=bar');
        putenv('ACCOUNT_FIELDS_K8S_QA_SERVICE_PORT_GRPC=3333');
        $this->assertEquals('bar:3333', Service::url('grpc-account-fields', $env));
    }
}
