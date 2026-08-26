<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use App\Trait\ApiKeyEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends AbstractBaseEntity implements UserInterface
{
    use ApiKeyEntityTrait;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column(type: 'string', length: 180, unique: true)]
        private string $email,
        #[ORM\Column(type: 'json')]
        private array $roles = [],
    ) {
        $this->setApiKey($this->generateApiKey());
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
