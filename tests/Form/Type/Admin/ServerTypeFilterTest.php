<?php

declare(strict_types=1);

namespace App\Tests\Form\Type\Admin;

use App\Entity\Installation;
use App\Form\Type\Admin\ServerTypeFilter;
use App\Form\Type\Admin\ServerTypeFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServerTypeFilterTest extends KernelTestCase
{
    public function testApplyOnFlatPropertyComparesAgainstRootAlias(): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, 'type', 'prod');

        $dql = $qb->getDQL();
        self::assertStringContainsString('entity.type = :type_0', $dql);
        self::assertStringNotContainsString('JOIN', $dql, 'A flat property must not trigger any JOIN');
        self::assertSame('prod', $qb->getParameter('type_0')?->getValue());
    }

    public function testApplyOnNestedPropertyJoinsTheRelation(): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, 'server.type', 'stg');

        $dql = $qb->getDQL();
        self::assertStringContainsString('LEFT JOIN entity.server server', $dql, 'Nested filter must auto-join the relation');
        self::assertStringContainsString('server.type = :server_type_0', $dql);
        self::assertSame('stg', $qb->getParameter('server_type_0')?->getValue());
    }

    public function testApplyReusesExistingJoinAliasInsteadOfDuplicating(): void
    {
        $qb = $this->makeInstallationQueryBuilder()
            ->leftJoin('entity.server', 'server');

        $this->apply($qb, 'server.type', 'devops');

        $dql = $qb->getDQL();
        self::assertSame(1, substr_count($dql, 'LEFT JOIN entity.server'), 'No duplicate join when alias already exists');
        self::assertStringContainsString('server.type = :server_type_0', $dql);
    }

    private function apply(QueryBuilder $qb, string $property, string $value): void
    {
        $filterDto = new FilterDto();
        $filterDto->setProperty($property);
        $filterDto->setFormType(ServerTypeFilterType::class);

        ServerTypeFilter::new($property)->apply(
            $qb,
            FilterDataDto::new(0, $filterDto, 'entity', [
                'comparison' => '=',
                'value' => $value,
            ]),
            null,
            new EntityDto(Installation::class, $this->getEntityManager()->getClassMetadata(Installation::class)),
        );
    }

    private function makeInstallationQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->getRepository(Installation::class)->createQueryBuilder('entity');
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}
