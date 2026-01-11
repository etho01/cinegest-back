<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Email;

final class User
{
    private UserId $id;
    private Email $email;
    private string $firstname;
    private string $lastname;
    private ?string $phone;
    private string $type;
    private int $originId;

    public function __construct(
        UserId $id,
        Email $email,
        string $firstname,
        string $lastname,
        ?string $phone,
        string $type,
        int $originId
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
        $this->type = $type;
        $this->originId = $originId;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function firstname(): string
    {
        return $this->firstname;
    }

    public function lastname(): string
    {
        return $this->lastname;
    }

    public function fullName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function originId(): int
    {
        return $this->originId;
    }

    public function isWebsiteUser(): bool
    {
        return $this->type === 'website';
    }
}
