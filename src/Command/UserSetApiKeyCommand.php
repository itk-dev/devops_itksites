<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:set-api-key',
    description: 'Set API key for a user',
)]
readonly class UserSetApiKeyCommand
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument]
        string $userId,
    ): int {
        $user = $this->userRepository->findOneBy(['email' => $userId])
            ?? $this->userRepository->findOneBy(['name' => $userId]);

        if (null === $user) {
            throw new InvalidArgumentException(sprintf('Cannot load user with id %s', $userId));
        }

        $question = sprintf('Really set API key on user %s', $user->getUserIdentifier());
        if (!$io->confirm($question)) {
            return Command::SUCCESS;
        }

        $user->setApiKey($user->generateApiKey());
        $this->entityManager->flush();

        $io->success([
            sprintf('API key for user %s set to', $user->getUserIdentifier()),
            $user->getApiKey(),
        ]);

        return Command::SUCCESS;
    }
}
