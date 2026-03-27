<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\Model\Server;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        #[Autowire('%env(default::COMPOSE_SERVER_DOMAIN)%')]
        private ?string $serverDomain,
        #[Autowire('%env(default::COMPOSE_DOMAIN)%')]
        private ?string $fallbackDomain,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $domain = ($this->serverDomain ?? '') !== '' ? $this->serverDomain : $this->fallbackDomain;

        if (null !== $domain && '' !== $domain) {
            $openApi = $openApi->withServers([new Server('https://'.$domain)]);
        }

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes();
        if ($securitySchemes instanceof \ArrayObject && isset($securitySchemes['apiKey']) && $securitySchemes['apiKey'] instanceof SecurityScheme) {
            $securitySchemes['apiKey'] = $securitySchemes['apiKey']->withDescription('Server API key. Use the format: Apikey {your-api-key}');
        }

        return $openApi;
    }
}
