<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait CertificateMockTrait
{
    public function createCertificateTemplate(Connection $db, array $options = [])
    {
        $db->insert('certificate_template', [
            'id'        => $options['id'] ?? null,
            'template'  => $options['template'] ?? '<h1>Awesome Certificate</h1>',
            'portal_id' => $options['portal_id'] ?? null,
            'lo_id'     => $options['lo_id'] ?? null,
        ]);

        return $options['id'] ?? $db->lastInsertId('certificate_template');
    }
}
