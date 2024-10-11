<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\AbstractQuery;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class Exporter
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function export(AbstractQuery $query, string $className, string $format = CsvEncoder::FORMAT): Response
    {
        $contentType = match ($format) {
            CsvEncoder::FORMAT => 'text/csv; charset=utf-8',
            default => throw new \InvalidArgumentException(sprintf('Invalid format: %s', $format)),
        };

        $entities = $query->execute();
        $content = $this->serializer->serialize($entities, $format, [
            'groups' => ['export'],
        ]);

        $filename = sprintf(
            'itksites-export-%s-%s.%s',
            preg_replace('@^.+\\\\([^\\\\]+)$@', '$1', $className),
            // Windows cannot handle colons in filenames so we use . to separate time parts.
            (new \DateTimeImmutable())->format('Y-m-d\TH.i.s'),
            $format
        );

        return new Response($content, Response::HTTP_OK, [
            'content-type' => $contentType,
            'content-disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename
            ),
        ]);
    }
}
