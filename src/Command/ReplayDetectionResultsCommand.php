<?php

namespace App\Command;

use ApiPlatform\Core\Bridge\Symfony\Messenger\ContextStamp;
use App\Entity\DetectionResult;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsCommand(
    name: 'app:replay:detection-results',
    description: 'Replay all detection results to trigger re-processing',
)]
class ReplayDetectionResultsCommand extends Command
{
    /**
     * ReplayDetectionResultsCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface    $messageBus
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    /** {@inheritDoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $detectionResults = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from('App\Entity\DetectionResult', 'r')
            ->orderBy('r.id', 'ASC');

        $type = $input->getOption('type');
        if (false !== $type) { // option passed
            if (null === $type) { // option passed but no value specified
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Select type to replay ',
                    array_flip(DetectionType::CHOICES),
                );
                $question->setErrorMessage('Type %s is invalid.');

                $type = $helper->ask($input, $output, $question);
                $output->writeln('You have just selected: '.$type);
            }
            if (in_array($type, DetectionType::CHOICES)) {
                $detectionResults
                    ->where('r.type = ?1')
                    ->setParameter(1, $type);
            } else {
                $io->error('Invalid type');

                return Command::INVALID;
            }
        }

        $iterable = SimpleBatchIteratorAggregate::fromQuery(
            $detectionResults->getQuery(),
            10 // flush/clear after 100 iterations
        );

        $context = $this->contextBuilder();

        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('debug_nomax');

        foreach ($progressBar->iterate($iterable) as $result) {
            $this->handle($result, $context);
        }

        $io->success('DetectionResults replayed');

        return Command::SUCCESS;
    }

    /**
     * Handle and dispatch the DetectionResult.
     *
     * @throws \Throwable
     */
    private function handle(DetectionResult $result, array $context = []): mixed
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
     * Dispatch message to the message bus.
     *
     * @param object|Envelope $message
     *
     * @throws \Throwable
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

    /**
     * Build context for the envelope context stamp.
     *
     * Static values as passed in ApiPlatform\Core\Bridge\Symfony\Messenger\DataPersister.
     */
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

    protected function configure(): void
    {
        $this
            ->addOption('type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Limit replay to this type. Options are ['.join(', ', DetectionType::CHOICES).']',
                false);
    }
}
