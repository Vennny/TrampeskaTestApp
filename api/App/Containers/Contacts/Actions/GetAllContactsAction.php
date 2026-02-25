<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @package App\Containers\Contacts
 */
final readonly class GetAllContactsAction
{
    /**
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     */
    public function __construct(
        private ContactsRepositoryInterface $contactsRepository
    ) {
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): Builder
    {
        return $this->contactsRepository->query();
    }
}
