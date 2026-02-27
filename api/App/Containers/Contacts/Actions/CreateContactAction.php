<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Tasks\SlugifyContactTask;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use Illuminate\Database\DatabaseManager;

/**
 * @package App\Containers\Contacts
 */
final readonly class CreateContactAction
{
    /**
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     * @param \Illuminate\Database\DatabaseManager $databaseManager
     * @param \App\Containers\Contacts\Tasks\SlugifyContactTask $slugifyContactTask
     */
    public function __construct(
        private ContactsRepositoryInterface $contactsRepository,
        private DatabaseManager $databaseManager,
        private SlugifyContactTask $slugifyContactTask
    ) {
    }

    /**
     * @param \App\Containers\Contacts\Values\DTOs\ContactDTO $dto
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Throwable
     */
    public function run(ContactDTO $dto): Contact
    {
        return $this->databaseManager->transaction(function () use ($dto): Contact {
            $data = $dto->getAttributes();
            $contact = $this->contactsRepository->create($data);
            return $this->contactsRepository->update($contact, [
                Contact::ATTR_SLUG => $this->slugifyContactTask->run($data, $contact->getKey()),
            ]);
        });
    }
}
