<?php declare(strict_types=1);

namespace Orion\Session;

use Psr\Http\Message\ServerRequestInterface;

class DefaultSessionOwnershipSignature
{
    private $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return array_map([$request, 'getAttribute'], $this->attributes);
    }
}
