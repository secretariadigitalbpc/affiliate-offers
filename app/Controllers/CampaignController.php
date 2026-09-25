<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ValidationException;
use App\Models\Campaign;
use App\Repositories\CampaignRepository;
use App\Services\CampaignValidator;
use DomainException;
use OutOfBoundsException;
use PDOException;

final class CampaignController
{
    public function __construct(
        private readonly CampaignRepository $repository,
        private readonly CampaignValidator $validator,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (Campaign $campaign): array => $campaign->toArray(),
            $this->repository->all()
        );
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): Campaign
    {
        try {
            return $this->repository->create($this->validatedData($input));
        } catch (PDOException $exception) {
            $this->throwConflictWhenDuplicate($exception);
            throw $exception;
        }
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): Campaign
    {
        if ($this->repository->find($id) === null) {
            throw new OutOfBoundsException('Campanha não encontrada.');
        }

        try {
            $campaign = $this->repository->update($id, $this->validatedData($input));
        } catch (PDOException $exception) {
            $this->throwConflictWhenDuplicate($exception);
            throw $exception;
        }

        if ($campaign === null) {
            throw new OutOfBoundsException('Campanha não encontrada.');
        }

        return $campaign;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function validatedData(array $input): array
    {
        $result = $this->validator->validate($input);

        if ($result['errors'] !== []) {
            throw new ValidationException($result['errors']);
        }

        return $result['data'];
    }

    private function throwConflictWhenDuplicate(PDOException $exception): void
    {
        if ((string) $exception->getCode() === '23000') {
            throw new DomainException('Já existe uma campanha com esse slug.');
        }
    }
}

