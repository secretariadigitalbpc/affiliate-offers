<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ValidationException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductValidator;
use DomainException;
use OutOfBoundsException;
use PDOException;

final class ProductController
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly ProductValidator $validator,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (Product $product): array => $product->toArray(),
            $this->repository->all()
        );
    }

    public function get(int $id): Product
    {
        $product = $this->repository->find($id);

        if ($product === null) {
            throw new OutOfBoundsException('Produto não encontrado.');
        }

        return $product;
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): Product
    {
        $data = $this->validatedData($input);

        try {
            return $this->repository->create($data);
        } catch (PDOException $exception) {
            $this->throwConflictWhenDuplicate($exception);
            throw $exception;
        }
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): Product
    {
        $this->get($id);
        $data = $this->validatedData($input);

        try {
            $product = $this->repository->update($id, $data);
        } catch (PDOException $exception) {
            $this->throwConflictWhenDuplicate($exception);
            throw $exception;
        }

        if ($product === null) {
            throw new OutOfBoundsException('Produto não encontrado.');
        }

        return $product;
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
            throw new DomainException('Este produto já está cadastrado para o marketplace.');
        }
    }
}

