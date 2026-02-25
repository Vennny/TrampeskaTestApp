<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Contracts;

use App\Containers\Contacts\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @package App\Containers\Contacts
 */
interface ContactsRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get(int $id): Contact;

    /**
     * @return \Illuminate\Support\Collection<\App\Containers\Contacts\Models\Contact>
     */
    public function getAll(): Collection;

    /**
     * @param array<string,mixed> $data
     * @return \App\Containers\Contacts\Models\Contact
     */
    public function create(array $data): Contact;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     * @param array<string,mixed> $data
     *
     * @return \App\Containers\Contacts\Models\Contact
     */
    public function update(Contact $contact, array $data): Contact;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     */
    public function save(Contact $contact): void;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     */
    public function delete(Contact $contact): void;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(): Builder;
}
