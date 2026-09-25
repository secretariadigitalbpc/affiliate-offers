<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ValidationException;
use App\Models\AffiliateLink;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\ProductRepository;
use App\Services\AffiliateLinkValidator;
use OutOfBoundsException;

final class AffiliateLinkController
{
    public function __construct(
        private readonly AffiliateLinkRepository $repository,
        private readonly ProductRepository $productRepository,
        private readonly AffiliateLinkValidator $validator,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (AffiliateLink $link): array => $link->toArray(),
            $this->repository->all()
        );
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): AffiliateLink
    {
        return $this->repository->create($this->validatedData($input));
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): AffiliateLink
    {
        if ($this->repository->find($id) === null) {
            throw new OutOfBoundsException('Link de afiliado não encontrado.');
        }

        $link = $this->repository->update($id, $this->validatedData($input));

        if ($link === null) {
            throw new OutOfBoundsException('Link de afiliado não encontrado.');
        }

        return $link;
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

        $product = $this->productRepository->find((int) $result['data']['product_id']);

        if ($product === null) {
            throw new ValidationException(['product_id' => 'O produto selecionado não existe.']);
        }

        if ($product->marketplace !== $result['data']['marketplace']) {
            throw new ValidationException([
                'marketplace' => 'O marketplace do link deve ser o mesmo do produto.',
            ]);
        }

        return $result['data'];
    }
}

