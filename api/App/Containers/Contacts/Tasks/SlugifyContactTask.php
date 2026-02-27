<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Tasks;

use App\Containers\Contacts\Models\Contact;
use Illuminate\Support\Str;

final class SlugifyContactTask
{
    /**
     * @param array $data
     * @param int $key
     *
     * @return string
     */
    public function run(array $data, int $key): string
    {
        return Str::slug($data[Contact::ATTR_FIRST_NAME] . ' ' . $data[Contact::ATTR_LAST_NAME] . ' ' . $key);
    }
}
