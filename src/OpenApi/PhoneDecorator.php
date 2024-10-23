<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;

final class PhoneDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        return $this->buildSchemas($openApi);
    }

    private function buildSchemas(OpenApi $openApi): OpenApi
    {
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
                    'readOnly' => true,
                ],
                'mercure_token' => [
                    'type' => 'string',
                    'example' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdLCJzdWJzY3JpYmUiOlsiL3VzZXJzLzFlZDFiYjQ5LTljNDgtNjE5OC05MGQ3LTMzYjY2NGMyNzAzNiJdfX0.z3I0aWXMzto1u0EUAuvui6UXWA5ATo-xgCO_xktKKYU',
                    'readOnly' => true,
                ],
                'refresh_token' => [
                    'type' => 'string',
                    'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
                    'readOnly' => true,
                ],
                'refresh_token_expiration' => [
                    'type' => 'int',
                    'example' => 1_234_569_554_495,
                    'readOnly' => true,
                ],
            ],
        ]);

        $schemas['PhoneNumber'] = new ArrayObject([
            'type' => 'string',
            'description' => 'The phone number with country code',
            'example' => '+33654955566',
        ]);

        return $openApi;
    }
}
