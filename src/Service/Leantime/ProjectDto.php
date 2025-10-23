<?php

namespace App\Service\Leantime;

use App\Entity\Project;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Project::class)]
readonly class ProjectDto
{
    private const string DATE_FORMAT = 'Y-m-d H:i:s';
    private const string TIMEZONE = 'UTC';
    private const string DETAILS_DEFAULT = '<p>Udfyld beskrivelse</p>';

    public function __construct(
        #[Map(target: 'leantimeId')]
        #[Map(target: 'leantimeUrl', transform: LeantimeProjectUrlFactory::class)]
        public int $id,
        #[Map(target: 'name')]
        public string $name,
        #[Map(target: 'details')]
        public ?string $details,
        public int $clientId,
        public ?int $state,
        public float $hourBudget,
        public float $dollarBudget,
        public string $menuType,
        public string $type,
        public ?int $parent,
        #[Map(target: 'leantimeModifiedAt')]
        public \DateTimeImmutable $modified,
        public ?string $start,
        public ?string $end,
        public string $clientName,
        public ?int $parentId,
        public ?string $parentName,
        public int $isFavorite,
    ) {
    }

    /**
     * Create a LeantimeProjectDto from JSON data.
     */
    public static function fromStdClass(\stdClass $data): self
    {
        $data->details = self::DETAILS_DEFAULT === $data->details ? null : $data->details;
        $data->hourBudget = floatval($data->hourBudget);
        $data->dollarBudget = floatval($data->dollarBudget);
        $data->modified = \DateTimeImmutable::createFromFormat(
            self::DATE_FORMAT,
            $data->modified,
            new \DateTimeZone(self::TIMEZONE)
        );

        return new self(
            id: $data->id,
            name: $data->name,
            details: $data->details ?? null,
            clientId: $data->clientId,
            state: $data->state,
            hourBudget: $data->hourBudget,
            dollarBudget: $data->dollarBudget,
            menuType: $data->menuType,
            type: $data->type,
            parent: $data->parent,
            modified: $data->modified,
            start: $data->start ?? null,
            end: $data->end ?? null,
            clientName: $data->clientName,
            parentId: $data->parentId ?? null,
            parentName: $data->parentName ?? null,
            isFavorite: $data->isFavorite,
        );
    }
}
