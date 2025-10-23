<?php

namespace App\Service\Leantime;

use App\Exception\LeantimeApiException;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    private const string API_PATH_JSONRPC = '/api/jsonrpc/';

    public function __construct(
        private readonly HttpClientInterface $leantimeClient,
    ) {
    }

    /**
     * Get all projects, including archived.
     *
     * @return array<int, ProjectDto>
     *
     * @throws LeantimeApiException
     */
    public function getProjects(): array
    {
        $projects = [];

        $data = $this->request(self::API_PATH_JSONRPC, 'POST', 'leantime.rpc.projects.getAll', []);

        foreach ($data as $project) {
            $projects[$project->id] = ProjectDto::fromStdClass($project);
        }

        return $projects;
    }

    /**
     * Get project.
     *
     * @param $key
     *             A project key or id
     *
     * @throws LeantimeApiException
     */
    public function getProject(int $key): ProjectDto
    {
        $data = $this->request(self::API_PATH_JSONRPC, 'POST', 'leantime.rpc.projects.getProject', ['id' => $key]);

        if (is_array($data) && false === $data[0]) {
            throw new LeantimeApiException(sprintf('Project (id: %d) not found', $key), 404);
        }

        return ProjectDto::fromStdClass($data);
    }

    /**
     * Get from Leantime.
     *
     * @throws LeantimeApiException
     */
    private function request(string $path, string $type, string $method, array $params = []): mixed
    {
        try {
            $response = $this->leantimeClient->request($type, $path,
                ['json' => [
                    'jsonrpc' => '2.0',
                    'method' => $method,
                    'id' => new Ulid()->jsonSerialize(),
                    'params' => $params,
                ]]
            );

            $body = $response->getContent();

            if ($body) {
                $data = json_decode($body, null, 512, JSON_THROW_ON_ERROR);

                if (isset($data->error)) {
                    $message = $data->error->message;
                    if (isset($data->error->data)) {
                        $message .= ': '.(is_scalar($data->error->data) ? $data->error->data : json_encode($data->error->data));
                    }
                    throw new LeantimeApiException($message, $data->error->code);
                }

                return $data->result;
            }
        } catch (\Throwable $e) {
            throw new LeantimeApiException('Error from Leantime API: '.$e->getMessage(), (int) $e->getCode(), $e);
        }

        return null;
    }
}
