<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OpenIdConnectFailureListenerTest extends WebTestCase
{
    /**
     * A callback that cannot be validated — here one arriving without the
     * session the login started — must end at the error page.
     *
     * A redirect would mean the failure was handled by the firewall after all,
     * which is the loop that kept the site down while the client secret was
     * expired.
     */
    public function testFailedCallbackRendersTheErrorPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/openid-connect/generic?state=bogus&code=bogus');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);

        $content = (string) $client->getResponse()->getContent();
        $this->assertStringContainsString('could not complete your login', $content);
        $this->assertStringContainsString('/openidconnect/login/azure_az', $content);
    }

    /**
     * The identity provider's own error text says why a login failed and
     * belongs in the log. The page says none of it.
     */
    public function testErrorPageDisclosesNothingAboutTheFailure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/openid-connect/generic?state=bogus&code=bogus');

        $content = (string) $client->getResponse()->getContent();
        $this->assertStringNotContainsString('Error occurred validating openid login', $content);
        $this->assertStringContainsString(
            'no-store',
            (string) $client->getResponse()->headers->get('Cache-Control'),
        );
    }
}
