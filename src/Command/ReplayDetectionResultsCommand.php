<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\DetectionResult;
use App\Message\ProcessDetectionResult;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Ulid;

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

    protected function configure(): void
    {
        $this
            ->addOption(
                'id',
                'id',
                InputOption::VALUE_OPTIONAL,
                'Limit replay to this ID',
                false
            )
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Limit replay to this type. Options are ['.join(', ', DetectionType::CHOICES).']',
                false
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit number of results to replay',
                false
            )
        ;
    }

    /** {@inheritDoc} */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('r.id')
            ->from(DetectionResult::class, 'r')
            ->orderBy('r.id', 'ASC');

        $criteria = [];

        $type = $input->getOption('type');
        if (false !== $type) { // option passed
            if (null === $type) { // option passed but no value specified
                /** @var QuestionHelper $helper */
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
                $queryBuilder
                    ->where('r.type = :type')
                    ->setParameter('type', $type);
                $criteria = ['type' => $type];
            } else {
                $io->error('Invalid type');

                return Command::INVALID;
            }
        }

        $id = $input->getOption('id');
        if (is_string($id)) {
            $ulid = self::fromString($id);
            $queryBuilder
                ->where('r.id = :id')
                ->setParameter('id', $ulid->toBinary());
            $criteria = ['id' => $ulid];
        }

        $count = $this->entityManager->getRepository(DetectionResult::class)->count($criteria);

        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares([new \Doctrine\DBAL\Logging\Middleware(new \Psr\Log\NullLogger())]);
        $iterable = $queryBuilder->getQuery()->toIterable();

        $limit = intval($input->getOption('limit'));
        if (0 !== $limit) {
            $queryBuilder->setMaxResults($limit);
            $max = min($count, $limit);
        } else {
            $max = $count;
        }

        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('debug');

        $count = 0;

        foreach ($progressBar->iterate($iterable, $max) as $result) {
            $this->dispatch($result['id']);

            ++$count;
        }

        $io->success($count.' DetectionResults replayed');

        return Command::SUCCESS;
    }

    /**
     * Dispatch message to the message bus.
     *
     * @param Ulid $detectionResultID
     *
     * @return Envelope
     *
     * @throws \Throwable
     */
    private function dispatch(Ulid $detectionResultID): Envelope
    {
        try {
            $message = new ProcessDetectionResult($detectionResultID);

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
     * Get a Ulid from a string with/without dashes.
     *
     * @param string $ulid
     *
     * @return Ulid
     */
    private static function fromString(string $ulid): Ulid
    {
        if (32 === strlen($ulid)) {
            $ulid = substr($ulid, 0, 8)
                .'-'.substr($ulid, 8, 4)
                .'-'.substr($ulid, 12, 4)
                .'-'.substr($ulid, 16, 4)
                .'-'.substr($ulid, 20)
            ;
        }

        return Ulid::fromString($ulid);
    }
}
