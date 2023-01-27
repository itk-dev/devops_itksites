<?php

namespace App\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;

class RemovedRelationsListener
{
    public function postFlush(PostFlushEventArgs $args): void
    {
        $connection = $args->getObjectManager()->getConnection();

        $statements = [
            // Composer packages
            'DELETE pv FROM package_version pv LEFT JOIN installation_package_version ipv ON pv.id = ipv.package_version_id WHERE ipv.installation_id IS NULL;',
            'DELETE p FROM package p LEFT JOIN package_version pv ON p.id = pv.package_id WHERE pv.id IS NULL;',
            // Drupal modules
            'DELETE mv FROM module_version mv LEFT JOIN installation_module_version imv ON mv.id = imv.module_version_id WHERE imv.installation_id IS NULL;',
            'DELETE m FROM module m LEFT JOIN module_version mv ON m.id = mv.module_id WHERE mv.id IS NULL;',
            // Docker images
            'DELETE dit FROM docker_image_tag dit LEFT JOIN installation_docker_image_tag idit ON dit.id = idit.docker_image_tag_id WHERE idit.installation_id IS NULL;',
            'DELETE di FROM docker_image di LEFT JOIN docker_image_tag dit ON di.id = dit.docker_image_id WHERE dit.id IS NULL;',
//             Git tags
            'DELETE gt FROM git_tag gt LEFT JOIN installation i ON gt.id = i.git_tag_id WHERE i.id IS NULL;',
            'DELETE gr FROM git_repo gr LEFT JOIN git_tag gt ON gr.id = gt.repo_id WHERE gt.id IS NULL',
        ];

        $rows = 0;
        foreach ($statements as $statement) {
            $rows += $connection->executeStatement($statement);
        }
    }
}
