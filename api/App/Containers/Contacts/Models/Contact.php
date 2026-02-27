<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Models;

use App\Ship\Values\CastTypesEnum;
use Carbon\CarbonImmutable;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @package App\Containers\Contact
 */
final class Contact extends Model
{
    use HasFactory;

    /**
     * Attributes of the model.
     */
    final public const string ATTR_ID = 'id';

    final public const string ATTR_FIRST_NAME = 'first_name';

    final public const string ATTR_LAST_NAME = 'last_name';

    final public const string ATTR_EMAIL = 'email';

    final public const string ATTR_PHONE = 'phone';

    final public const string ATTR_NOTE = 'note';

    final public const string ATTR_SLUG = 'slug';

    final public const string ATTR_CREATED_AT = self::CREATED_AT;

    final public const string ATTR_UPDATED_AT = self::UPDATED_AT;

    /**
     * Model limits.
     */
    final public const int LIMIT_FIRST_NAME = 50;

    final public const int LIMIT_LAST_NAME = 50;

    final public const int LIMIT_EMAIL = 255;

    final public const int LIMIT_PHONE = 255;

    final public const int LIMIT_NOTE = 65535;

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'contact';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::ATTR_FIRST_NAME,
        self::ATTR_LAST_NAME,
        self::ATTR_EMAIL,
        self::ATTR_PHONE,
        self::ATTR_NOTE,
        self::ATTR_SLUG,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var string[]
     */
    protected $casts = [
        self::ATTR_FIRST_NAME => CastTypesEnum::STRING->value,
        self::ATTR_LAST_NAME => CastTypesEnum::STRING->value,
        self::ATTR_EMAIL => CastTypesEnum::STRING->value,
        self::ATTR_PHONE => CastTypesEnum::STRING->value,
        self::ATTR_NOTE => CastTypesEnum::STRING->value,
        self::ATTR_SLUG => CastTypesEnum::STRING->value,
    ];

    /**
     * @inheritDoc
     */
    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    /**
     * Fill model with compact data.
     *
     * @param array<string,mixed> $data
     */
    public function compactFill(array $data): void
    {
        //default data
        $this->fill($data);
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->getAttributeValue(self::ATTR_FIRST_NAME);
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->getAttributeValue(self::ATTR_LAST_NAME);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getAttributeValue(self::ATTR_EMAIL);
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->getAttributeValue(self::ATTR_PHONE);
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->getAttributeValue(self::ATTR_NOTE);
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->getAttributeValue(self::ATTR_SLUG);
    }

    /**
     * @return \Carbon\CarbonImmutable
     */
    public function getCreatedAt(): CarbonImmutable
    {
        return $this->getAttributeValue(self::ATTR_CREATED_AT);
    }

    /**
     * @return \Carbon\CarbonImmutable
     */
    public function getUpdatedAt(): CarbonImmutable
    {
        return $this->getAttributeValue(self::ATTR_UPDATED_AT);
    }
}
