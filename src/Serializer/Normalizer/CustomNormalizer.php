<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\ServiceCertificate;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CustomNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
    ) {
    }

    public function normalize($data, ?string $format = null, array $context = []): array
    {
        $normalized = $this->normalizer->normalize($data, $format, $context);

        if (isset($normalized['services']) && is_array($normalized['services'])) {
            $normalized['services'] = implode(',', array_column($normalized['services'], 'type'));
        }

        return $normalized;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ServiceCertificate;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ServiceCertificate::class => true];
    }
}
