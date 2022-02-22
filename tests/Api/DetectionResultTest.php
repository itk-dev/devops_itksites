<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class DetectionResultTest extends ApiTestCase
{
    public function testAuthenticationDenied(): void
    {
        $response = static::createClient()->request('POST', '/api/detection_results', [
            'body' => '{
                          "type": "string",
                          "rootDir": "string",
                          "data": [
                            "string"
                          ]
                        }'
        ]);

        $this->assertResponseStatusCodeSame(401, 'Unauthenticated requests should be denied');
    }
}
