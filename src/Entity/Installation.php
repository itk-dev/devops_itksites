<?php

namespace App\Entity;

use App\Repository\InstallationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstallationRepository::class)]
#[ORM\UniqueConstraint(name: 'server_rootdir_idx', fields: ['server', 'rootDir'])]
class Installation extends AbstractHandlerResult
{
}
