<?php

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ApiKeyEntityTrait
{
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Length(
        min: 40,
        max: 255,
        minMessage: 'Api key must be at least {{ limit }} characters long',
        maxMessage: 'Api key cannot be longer than {{ limit }} characters',
    )]
    private string $apiKey;

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function generateApiKey(): string
    {
        return sha1(\random_bytes(40));
    }
}
