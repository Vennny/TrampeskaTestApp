<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Repositories;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @package App\Containers\Contacts
 */
final readonly class ContactsRepository implements ContactsRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function get(int $id): Contact
    {
        /** @var \App\Containers\Contacts\Models\Contact $contact */
        $contact = $this->query()->findOrFail($id);
        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return $this->query()->get();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Contact
    {
        $contact = new Contact();
        $contact->compactFill($data);
        $this->save($contact);

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function update(Contact $contact, array $data): Contact
    {
        $contact->compactFill($data);
        $this->save($contact);

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function save(Contact $contact): void
    {
        $contact->save();
    }

    /**
     * @inheritDoc
     */
    public function delete(Contact $contact): void
    {
        $contact->delete();
    }

    /**
     * @inheritDoc
     */
    public function query(): Builder
    {
        return new Contact()::query();
    }
}
