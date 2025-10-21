<?php

namespace App\Service\Leantime;

final class LeantimeProjectUrlFactory
{
    private const string PROJECT_URL_FORMAT = '%s/tickets/showKanban?search=true&projectId=%d&sprint=all';

    public function __construct(private readonly string $baseUrl)
    {
    }

    public function getProjectUrl(int $projectId): string
    {
        return sprintf(self::PROJECT_URL_FORMAT, $this->baseUrl, $projectId);
    }
}
