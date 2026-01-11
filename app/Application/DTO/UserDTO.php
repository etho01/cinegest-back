<?php

namespace App\Application\DTO;

final class UserDTO
{
    public int $id;
    public string $email;
    public string $firstname;
    public string $lastname;
    public ?string $phone;

    public function __construct(
        int $id,
        string $email,
        string $firstname,
        string $lastname,
        ?string $phone = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'phone' => $this->phone,
        ];
    }
}
