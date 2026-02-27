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
final readonly class UpdateContactAction
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
     * @param \App\Containers\Contacts\Models\Contact $contact
     * @param \App\Containers\Contacts\Values\DTOs\ContactDTO $dto
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Throwable
     */
    public function run(Contact $contact, ContactDTO $dto): Contact
    {
        return $this->databaseManager->transaction(function () use ($contact, $dto): Contact {
            $data = $dto->getAttributes();

            $this->prepareSlug($data, $contact);

            $this->contactsRepository->update($contact, $data);

            return $contact;
        });
    }

    /**
     * @param array $data
     * @param \App\Containers\Contacts\Models\Contact $contact
     */
    private function prepareSlug(array &$data, Contact $contact): void
    {
        if (
            ! isset($data[Contact::ATTR_FIRST_NAME])
            && ! isset($data[Contact::ATTR_LAST_NAME])
            && $contact->getSlug()
        ) {
            return;
        }

        $data[Contact::ATTR_SLUG] = $this->slugifyContactTask->run($data, $contact->getKey());
    }
}
