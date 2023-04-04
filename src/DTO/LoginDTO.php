<?php
namespace App\DTO;
use Symfony\Component\HttpFoundation\Request;

class LoginDTO {

    private ?string $email;

    private ?string $password;

    private ?string $name;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public static function fromRequest(Request $request): self
    {
        $dto = new self();

        $dto->email = $request->get('email');
        $dto->password = $request->get('password');
        $dto->name = $request->get("full_name");

        return $dto;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}