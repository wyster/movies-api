<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SetCookiesDto
{
    public function __construct(
        #[Assert\NotNull]
        public readonly string $cookies,
    ) {}
}
