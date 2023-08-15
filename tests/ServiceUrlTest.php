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
        $this->assertEquals('account-fields.k8s-qa:5000', Service::url('grpc-account-fields', 'k8s-qa'));
    }
}
