<?php

namespace go1\util\customer\mock;

use go1\util\customer\CustomerEsSchema;
use go1\util\DateTime;

trait EsPortalMockTrait
{
    /**
     * @param \Elasticsearch\Client|\Util\Elasticsearch8\Elasticsearch\Client $client
     * @param $options
     * @return mixed
     */
    public function createEsPortal($client, $options = [])
    {
        static $autoId;

        $portal = [
            'id'            => $options['id'] ?? ++$autoId,
            'name'          => $options['name'] ?? 'GO1',
            'title'         => $options['title'] ?? 'az.mygo1.com',
            'status'        => $options['status'] ?? 1,
            'logo'          => $options['logo'] ?? null,
            'version'       => $options['version'] ?? '',
            'created'       => DateTime::formatDate($options['created'] ?? time()),
            'configuration' => $options['configuration'] ?? null,
        ];

        $client->create([
            'index'   => $options['index'] ?? CustomerEsSchema::INDEX,
            'routing' => $options['id'],
            'type'    => CustomerEsSchema::O_PORTAL,
            'id'      => $portal['id'],
            'body'    => $portal,
            'refresh' => true,
        ]);

        return $portal['id'];
    }
}
