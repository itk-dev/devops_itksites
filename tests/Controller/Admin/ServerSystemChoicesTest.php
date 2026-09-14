<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ServerCrudController;
use App\Entity\User;
use App\Types\SystemType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Only supported operating systems are offered on the server form.
 *
 * Ubuntu LTS is even-numbered releases only, so the odd ones must not be
 * selectable. The list is expanded into radios, which is the one place a
 * reader ever sees the full set.
 */
class ServerSystemChoicesTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testFormOffersEveryLtsUbuntuAndNoOther(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client->loginUser($entityManager->getRepository(User::class)->findOneBy([]));

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(ServerCrudController::class)
            ->setAction(Crud::PAGE_NEW)
            ->generateUrl();

        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        $offered = $crawler->filter('input[name="Server[system]"]')->extract(['value']);

        $this->assertNotEmpty($offered, 'The system field renders no radios at all.');

        $ubuntu = array_values(array_filter(
            $offered,
            static fn (string $value): bool => str_starts_with($value, 'ubuntu'),
        ));

        $this->assertSame(
            ['ubuntu1604', 'ubuntu1804', 'ubuntu2004', 'ubuntu2204', 'ubuntu2404', 'ubuntu2604'],
            $ubuntu,
        );
    }

    /**
     * The label is what a reader picks by, so it has to name the value's release.
     */
    public function testEveryLabelMatchesItsValue(): void
    {
        foreach (SystemType::CHOICES as $label => $value) {
            [$name, $release] = explode(' ', $label, 2);

            $this->assertSame(
                strtolower($name).str_replace('.', '', $release),
                str_replace('deb', 'debian', $value),
                sprintf('Choice "%s" is stored as "%s".', $label, $value),
            );
        }
    }
}
