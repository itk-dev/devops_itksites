<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ServerCrudController;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The dashboard exists only to redirect to the Server index.
 *
 * It builds that URL on an injected AdminUrlGenerator, and the chain starts
 * with unsetAll() because the generator instance is held for as long as this
 * controller is — which, in a worker, is longer than one request. Order matters
 * there: unsetAll() after setController() rather than before would wipe the
 * controller back out and produce a URL pointing nowhere in particular, without
 * anything throwing. Hence asserting where the redirect actually lands.
 */
class DashboardControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testItRedirectsToTheServerIndex(): void
    {
        $client = static::createClient();

        $user = static::getContainer()->get('doctrine')->getManager()
            ->getRepository(User::class)->findOneBy([]);
        $client->loginUser($user);

        $client->request('GET', '/admin');

        $this->assertResponseRedirects();

        // Ask EasyAdmin what that URL should be rather than hard-coding the
        // slug, so this keeps working if the route path changes.
        $expected = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(ServerCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        $location = (string) $client->getResponse()->headers->get('Location');
        $this->assertSame(
            parse_url($expected, \PHP_URL_PATH),
            parse_url($location, \PHP_URL_PATH),
            'the redirect must still land on the Server index'
        );
    }

    public function testTheRedirectTargetLoads(): void
    {
        $client = static::createClient();

        $user = static::getContainer()->get('doctrine')->getManager()
            ->getRepository(User::class)->findOneBy([]);
        $client->loginUser($user);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
    }
}
