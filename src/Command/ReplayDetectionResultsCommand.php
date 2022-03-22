<?php

namespace App\Command;

use ApiPlatform\Core\Bridge\Symfony\Messenger\ContextStamp;
use App\Entity\DetectionResult;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsCommand(
    name: 'app:replay:detection-results',
    description: 'Add a short description for your command',
)]
class ReplayDetectionResultsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $iterable = SimpleBatchIteratorAggregate::fromQuery(
            $this->entityManager->createQuery('SELECT r FROM App\Entity\DetectionResult r'),
            100 // flush/clear after 100 iterations
        );

        $context = $this->contextBuilder();

        $progressBar = new ProgressBar($output);

        foreach ($progressBar->iterate($iterable) as $result) {
            $this->handle($result, $context);
        }

        $io->success('DetectionResults replayed');

        return Command::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DetectionResult $result, array $context = [])
    {
        $envelope = $this->dispatch(
            (new Envelope($result))
                ->with(new ContextStamp($context))
        );

        $handledStamp = $envelope->last(HandledStamp::class);
        if (!$handledStamp instanceof HandledStamp) {
            return $result;
        }

        return $handledStamp->getResult();
    }

    /**
     * @param object|Envelope $message
     */
    private function dispatch($message)
    {
        try {
            return $this->messageBus->dispatch($message);
        } catch (HandlerFailedException $e) {
            // unwrap the exception thrown in handler for Symfony Messenger >= 4.3
            while ($e instanceof HandlerFailedException) {
                /** @var \Throwable $e */
                $e = $e->getPrevious();
            }

            throw $e;
        }
    }

    private function contextBuilder(): array
    {
        return [
            'resource_class' => DetectionResult::class,
            'has_composite_identifier' => false,
            'identifiers' => [],
            'collection_operation_name' => 'post',
            'receive' => true,
            'respond' => true,
            'persist' => true,
        ];
    }
}
