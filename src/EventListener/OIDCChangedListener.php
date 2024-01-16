<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\OIDC;
use App\Repository\SiteRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preFlush, method: 'preFlush', entity: OIDC::class)]
class OIDCChangedListener
{
    public function __construct(
        private readonly SiteRepository $siteRepository
    ) {
    }

    public function preFlush(OIDC $oidc, PreFlushEventArgs $event): void
    {
        $site = $this->siteRepository->findOneBy(['primaryDomain' => $oidc->getDomain()]);

        if (null !== $site) {
            $type = $site->getServer()->getType();
            $oidc->setType($type);
        }
    }
}
