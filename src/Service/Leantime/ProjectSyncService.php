<?php

namespace App\Service\Leantime;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class ProjectSyncService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApiService $leantimeApiService,
        private ObjectMapperInterface $objectMapper,
        private LeantimeProjectUrlFactory $leantimeProjectUrlFactory,
    ) {
    }

    public function syncAllProjects(): int
    {
        $projectDtoArray = $this->leantimeApiService->getProjects();
        $count = count($projectDtoArray);

        $projects = $this->entityManager->getRepository(Project::class)->findBy([], ['id' => 'ASC']);

        foreach ($projects as $project) {
            if (isset($projectDtoArray[$project->getLeantimeId()])) {
                $dto = $projectDtoArray[$project->getLeantimeId()];
                $this->setProperties($project, $dto);

                unset($projectDtoArray[$project->getLeantimeId()]);
            } else {
                // Delete projects that are not in Leantime
                $this->entityManager->remove($project);
            }
        }

        foreach ($projectDtoArray as $id => $projectDto) {
            $project = $this->objectMapper->map($projectDto, Project::class);
            $this->setProperties($project, $projectDto);
            $this->entityManager->persist($project);
        }

        $this->entityManager->flush();

        return $count;
    }

    public function syncProject(int $leantimeId): Project
    {
        $projectDto = $this->leantimeApiService->getProject($leantimeId);
        $project = $this->entityManager->getRepository(Project::class)->findOneBy(['leantimeId' => $leantimeId]);

        if (null === $project) {
            $project = $this->objectMapper->map($projectDto, Project::class);
            $this->entityManager->persist($project);
        }

        $this->setProperties($project, $projectDto);

        $this->entityManager->flush();

        return $project;
    }

    private function setProperties(Project $project, ProjectDto $projectDto): void
    {
        $project->setLeantimeId($projectDto->id);
        $project->setName($projectDto->name);
        $project->setDetails($projectDto->details);
        $project->setLeantimeModifiedAt($projectDto->modified);

        $url = $this->leantimeProjectUrlFactory->getProjectUrl($project->getLeantimeId());
        $project->setLeantimeUrl($url);
    }
}
