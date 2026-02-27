<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Transformers;

use App\Containers\Contacts\Models\Contact;
use App\Ship\Parents\Transformers\ApiTransformer;

/**
 * @package App\Containers\Contacts
 */
final class ContactApiTransformer extends ApiTransformer
{
    final public const string PROP_ID = Contact::ATTR_ID;

    final public const string PROP_FIRST_NAME = Contact::ATTR_FIRST_NAME;

    final public const string PROP_LAST_NAME = Contact::ATTR_LAST_NAME;

    final public const string PROP_EMAIL = Contact::ATTR_EMAIL;

    final public const string PROP_PHONE = Contact::ATTR_PHONE;

    final public const string PROP_NOTE = Contact::ATTR_NOTE;

    final public const string PROP_SLUG = Contact::ATTR_SLUG;

    final public const string PROP_CREATED_AT = Contact::ATTR_CREATED_AT;

    final public const string PROP_UPDATED_AT = Contact::ATTR_UPDATED_AT;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     *
     * @return mixed[]
     */
    public function transform(Contact $contact): array
    {
        return [
            self::PROP_ID => $contact->getKey(),
            self::PROP_FIRST_NAME => $contact->getFirstName(),
            self::PROP_LAST_NAME => $contact->getLastName(),
            self::PROP_EMAIL => $contact->getEmail(),
            self::PROP_PHONE => $contact->getPhone(),
            self::PROP_NOTE => $contact->getNote(),
            self::PROP_SLUG => $contact->getSlug(),
            self::PROP_CREATED_AT => $this->formatDateTime($contact->getCreatedAt()),
            self::PROP_UPDATED_AT => $this->formatDateTime($contact->getUpdatedAt()),
        ];
    }
}
