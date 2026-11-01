<?php

declare(strict_types=1);

namespace App\Helper;

use Sentry\State\Scope;

use function Sentry\configureScope;

class SentryHelper
{
    /**
     * @param array<mixed> $extra
     */
    public static function configureExtraDetails(array $extra = []): void
    {
        configureScope(static function (Scope $scope) use ($extra): void {
            foreach ($extra as $key => $value) {
                $scope->setExtra($key, $value);
            }
        });
    }
}
