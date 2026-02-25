<?php

declare(strict_types=1);

namespace App\Ship\Parents\Transformers;

use App\Containers\Contacts\Models\Contact;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

abstract class ApiTransformer
{
    final public const string DATETIME_FORMAT = 'Y-m-d\TH:i:sO';

    /**
     * @param mixed $item
     *
     * @return mixed[]
     */
    public function runTransformation(mixed $item): array
    {
        if (! \method_exists($this, 'transform')) {
            throw new \RuntimeException();
        }

        return $this->transform($item);
    }


    /**
     * @param \Carbon\CarbonImmutable|null $datetime
     *
     * @return string|null
     */
    public static function formatDateTime(?CarbonImmutable $datetime): ?string
    {
        return $datetime?->format(self::DATETIME_FORMAT);
    }
}
