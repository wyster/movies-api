<?php

declare(strict_types=1);

namespace App\Helper;

use Sentry\State\Scope;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

use function Sentry\captureException;
use function Sentry\captureMessage;
use function Sentry\configureScope;
use function Sentry\withScope;

class SentryHelper
{
    public static function configureExtraDetails(array $extra = []): void
    {
        configureScope(static function (Scope $scope) use ($extra): void {
            foreach ($extra as $key => $value) {
                $scope->setExtra($key, $value);
            }
        });
    }
}
