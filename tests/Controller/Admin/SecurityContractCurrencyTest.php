<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\SecurityContractCrudController;
use App\Entity\Project;
use App\Entity\SecurityContract;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Service agreement amounts are Danish kroner, and the admin has to say so.
 *
 * The values come from Economics with no unit attached, and the CRUD has no
 * form pages — NEW and EDIT are disabled — so index and detail are the only
 * places a reader ever sees them.
 */
class SecurityContractCurrencyTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testAmountsRenderAsDanishKroner(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client->loginUser($entityManager->getRepository(User::class)->findOneBy([]));

        $project = new Project();
        $project->setEconomicsId(4711);
        $project->setName('Kroner probe');

        $contract = new SecurityContract();
        $contract->setEconomicsId(4711);
        $contract->setProject($project);
        $contract->setMonthlyPrice(12500.5);

        $entityManager->persist($project);
        $entityManager->persist($contract);
        $entityManager->flush();

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(SecurityContractCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        // Danish grouping and separator, not the application locale's 12,500.5.
        // Amount and unit are asserted apart because Intl joins them with a
        // non-breaking space.
        $this->assertStringContainsString('12.500,50', $content);
        $this->assertStringContainsString('kr.', $content);
    }
}
