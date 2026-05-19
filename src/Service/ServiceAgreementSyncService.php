<?php

namespace App\Service;

use App\Entity\SecurityContract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Syncs service agreements from the Economics API (economics.itkdev.dk)
 * into local SecurityContract entities.
 */
readonly class ServiceAgreementSyncService
{
    /**
     * @param EntityManagerInterface $entityManager Doctrine entity manager for persisting contracts
     * @param HttpClientInterface    $economicsClient scoped HTTP client for the Economics API
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface    $economicsClient,
    ) {
    }

    /**
     * Fetch all service agreements from the Economics API and sync them locally.
     *
     * Creates new, updates existing (matched by economics ID), and removes
     * contracts that no longer exist in the API response.
     *
     * @return int the number of agreements synced
     *
     * @throws \RuntimeException|\Exception if the API request fails
     */
    public function syncAll(): int
    {
        try {
            $response = $this->economicsClient->request('GET', '/api/serviceagreements');
            $agreements = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to fetch service agreements from Economics API: '.$e->getMessage(), 0, $e);
        }

        $existingContracts = $this->entityManager->getRepository(SecurityContract::class)->findAll();
        $existingByEconomicsId = [];
        foreach ($existingContracts as $contract) {
            $existingByEconomicsId[$contract->getEconomicsId()] = $contract;
        }

        $seenIds = [];

        foreach ($agreements as $data) {
            $economicsId = $data['id'];
            $seenIds[] = $economicsId;

            $contract = $existingByEconomicsId[$economicsId] ?? new SecurityContract();

            $this->mapDataToContract($contract, $data);

            if (null === $contract->getEconomicsId()) {
                $contract->setEconomicsId($economicsId);
            }

            $this->entityManager->persist($contract);
        }

        foreach ($existingContracts as $contract) {
            if (!in_array($contract->getEconomicsId(), $seenIds, true)) {
                $this->entityManager->remove($contract);
            }
        }

        $this->entityManager->flush();

        return count($agreements);
    }

    /**
     * Map API response data onto a SecurityContract entity.
     *
     * @param array<string, mixed> $data a single service agreement from the API
     * @throws \Exception
     */
    private function mapDataToContract(SecurityContract $contract, array $data): void
    {
        $contract->setEconomicsId($data['id']);
        $contract->setProjectName($data['projectName'] ?? '');
        $contract->setClientName($data['clientName'] ?? null);
        $contract->setHostingProvider($data['hostingProvider'] ?? null);
        $contract->setDocumentUrl($data['documentUrl'] ?? null);
        $contract->setMonthlyPrice(isset($data['price']) ? (float) $data['price'] : null);
        $contract->setValidFrom($this->parseDate($data['validFrom'] ?? null));
        $contract->setValidTo($this->parseDate($data['validTo'] ?? null));
        $contract->setActive($data['isActive'] ?? false);
        $contract->setEol($data['isEol'] ?? false);
        $contract->setLeantimeUrl($data['leantimeUrl'] ?? null);
        $contract->setClientContactName($data['clientContactName'] ?? null);
        $contract->setClientContactEmail($data['clientContactEmail'] ?? null);
        $contract->setDedicatedServer($data['dedicatedServer'] ?? false);
        $contract->setServerSize($data['serverSize'] ?? null);
        $contract->setGitRepos($data['gitRepos'] ?? null);
        $contract->setSystemOwnerNotices($data['systemOwnerNotices'] ?? null);
        $contract->setProjectTrackerKey($data['projectTrackerKey'] ?? null);

        $cyber = $data['cybersecurityAgreement'] ?? null;
        if (is_array($cyber)) {
            $contract->setQuarterlyHours(isset($cyber['quarterlyHours']) ? (float) $cyber['quarterlyHours'] : null);
            $contract->setCybersecurityPrice(isset($cyber['price']) ? (float) $cyber['price'] : null);
            $contract->setCybersecurityNote($cyber['note'] ?? null);
        }
    }

    /**
     * Parse the Economics API date format ({date, timezone_type, timezone}) into a DateTimeImmutable.
     *
     * @param array{date?: string, timezone_type?: int, timezone?: string}|null $dateData raw date object from the API
     *
     * @return \DateTimeImmutable|null the parsed date, or null if no valid date data was provided
     *
     * @throws \Exception if the date string cannot be parsed
     */
    private function parseDate(?array $dateData): ?\DateTimeImmutable
    {
        if (null === $dateData || !isset($dateData['date'])) {
            return null;
        }

        $timezone = new \DateTimeZone($dateData['timezone'] ?? 'UTC');

        return new \DateTimeImmutable($dateData['date'], $timezone);
    }
}
