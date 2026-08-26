<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
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

    /**
     * A refusal is not a server error. The user declined, or Azure declined on a
     * policy, and answering 500 pages somebody for an ordinary outcome.
     */
    public function testARefusedLoginAnswers403(): void
    {
        $client = static::createClient();
        $session = $this->startedLogin($client);

        $client->request('GET', '/openid-connect/generic?state='.$session.'&error=access_denied&error_description=User+cancelled');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertNull(
            $client->getResponse()->headers->get('Location'),
            'A refused login was answered with a redirect: the firewall re-entered its entry point and the loop is back.'
        );
    }

    /**
     * The page tells the user the login was declined rather than that something
     * went wrong, and still repeats nothing the identity provider sent.
     */
    public function testARefusedLoginSaysItWasDeclined(): void
    {
        $client = static::createClient();
        $session = $this->startedLogin($client);

        $client->request('GET', '/openid-connect/generic?state='.$session.'&error=access_denied&error_description=User+cancelled');

        $content = (string) $client->getResponse()->getContent();
        $this->assertStringContainsString('Login declined', $content);
        $this->assertStringContainsString('cancelled at the login screen', $content);
        $this->assertStringContainsString('/openidconnect/login/azure_az', $content);
        $this->assertStringNotContainsString('access_denied', $content);
        $this->assertStringNotContainsString('User cancelled', $content);
    }

    /**
     * A forged callback carries whatever text its sender chose. The state is
     * checked first, so none of it is trusted and the answer is the ordinary
     * failure page.
     */
    public function testAForgedRefusalIsAnOrdinaryFailure(): void
    {
        $client = static::createClient();
        $this->startedLogin($client);

        $client->request('GET', '/openid-connect/generic?state=does-not-match&error=access_denied&error_description=Call+0800+SCAM');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $content = (string) $client->getResponse()->getContent();
        $this->assertStringContainsString('could not complete your login', $content);
        $this->assertStringNotContainsString('SCAM', $content);
    }

    /**
     * Put the session values a login leaves behind in place, and return the state
     * the callback has to carry to be recognised as belonging to it.
     */
    private function startedLogin(KernelBrowser $client): string
    {
        $session = static::getContainer()->get('session.factory')->createSession();
        $session->set('oauth2provider', 'azure_az');
        $session->set('oauth2state', 'the-real-state');
        $session->set('oauth2nonce', 'the-real-nonce');
        $session->set('oauth2pkce_verifier', 'the-real-verifier');
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return 'the-real-state';
    }
}
