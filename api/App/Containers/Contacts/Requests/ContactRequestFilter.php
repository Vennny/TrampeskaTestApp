<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Requests;

use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\ValidationException;

/**
 * @package App\Containers\Contacts
 */
final class ContactRequestFilter
{
    /**
     * Fields.
     */
    final public const string FIELD_FIRST_NAME = Contact::ATTR_FIRST_NAME;

    final public const string FIELD_LAST_NAME = Contact::ATTR_LAST_NAME;

    final public const string FIELD_EMAIL = Contact::ATTR_EMAIL;

    final public const string FIELD_PHONE = Contact::ATTR_PHONE;

    final public const string FIELD_NOTE = Contact::ATTR_NOTE;

    /**
     * Limits.
     */
    final public const int LIMIT_FIRST_NAME = Contact::LIMIT_FIRST_NAME;

    final public const int LIMIT_LAST_NAME = Contact::LIMIT_LAST_NAME;

    final public const int LIMIT_EMAIL = Contact::LIMIT_EMAIL;

    final public const int LIMIT_PHONE = Contact::LIMIT_PHONE;

    final public const int LIMIT_NOTE = Contact::LIMIT_NOTE;

    /**
     * @param \Illuminate\Validation\Factory $validatorFactory
     */
    public function __construct(
        private readonly ValidatorFactory $validatorFactory
    ) {
    }

    /**
     * Get values for model.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Containers\Contacts\Values\DTOs\ContactDTO
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getValidatedData(
        Request $request
    ): ContactDTO {
        $fields = $this->validate($request);
        $rawData = $request->only($fields);
        $dto = new ContactDTO();

        if (\array_key_exists(self::FIELD_FIRST_NAME, $rawData)) {
            $dto->setFirstName($rawData[self::FIELD_FIRST_NAME]);
        }

        if (\array_key_exists(self::FIELD_LAST_NAME, $rawData)) {
            $dto->setLastName($rawData[self::FIELD_LAST_NAME]);
        }

        if (\array_key_exists(self::FIELD_EMAIL, $rawData)) {
            $dto->setEmail($rawData[self::FIELD_EMAIL]);
        }

        if (\array_key_exists(self::FIELD_PHONE, $rawData)) {
            $dto->setPhone($rawData[self::FIELD_PHONE]);
        }

        if (\array_key_exists(self::FIELD_NOTE, $rawData)) {
            $dto->setNote($rawData[self::FIELD_NOTE]);
        }

        return $dto;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string[]
     */
    public function validate(Request $request): array
    {
        $rules = $this->getRules($request);
        $validator = $this->validatorFactory->make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return \array_keys($rules);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed[]
     */
    private function getRules(Request $request): array
    {
        $sometimesPresent = $request->isMethod(Request::METHOD_PATCH) ? 'sometimes' : 'present';

        return [
            self::FIELD_FIRST_NAME => [
                $sometimesPresent,
                'string',
                'max:' . self::LIMIT_FIRST_NAME,
            ],
            self::FIELD_LAST_NAME => [
                $sometimesPresent,
                'string',
                'max:' . self::LIMIT_LAST_NAME,
            ],
            self::FIELD_EMAIL => [
                $sometimesPresent,
                Email::default()->rfcCompliant(),
                'max:' . self::LIMIT_EMAIL,
            ],
            self::FIELD_PHONE => [
                $sometimesPresent,
                'string',
                'max:' . self::LIMIT_PHONE,
                'regex:/^\+[1-9]\d{1,3}[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{1,4}[\s\-]?\d{1,9}$/',
                'nullable',
            ],
            self::FIELD_NOTE => [
                $sometimesPresent,
                'string',
                'max:' . self::LIMIT_NOTE,
                'nullable',
            ],
        ];
    }
}
