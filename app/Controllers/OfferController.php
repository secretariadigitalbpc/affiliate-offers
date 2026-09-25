<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ValidationException;
use App\Models\Offer;
use App\Repositories\AffiliateLinkRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ProductRepository;
use App\Services\OfferValidator;
use OutOfBoundsException;

final class OfferController
{
    public function __construct(
        private readonly OfferRepository $repository,
        private readonly ProductRepository $productRepository,
        private readonly AffiliateLinkRepository $affiliateLinkRepository,
        private readonly OfferValidator $validator,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (Offer $offer): array => $offer->toArray(),
            $this->repository->all()
        );
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): Offer
    {
        return $this->repository->create($this->validatedData($input));
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): Offer
    {
        if ($this->repository->find($id) === null) {
            throw new OutOfBoundsException('Oferta não encontrada.');
        }

        $offer = $this->repository->update($id, $this->validatedData($input));

        if ($offer === null) {
            throw new OutOfBoundsException('Oferta não encontrada.');
        }

        return $offer;
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
        $affiliateLink = $this->affiliateLinkRepository->find(
            (int) $result['data']['affiliate_link_id']
        );

        if ($product === null) {
            throw new ValidationException(['product_id' => 'O produto selecionado não existe.']);
        }

        if ($affiliateLink === null) {
            throw new ValidationException([
                'affiliate_link_id' => 'O link de afiliado selecionado não existe.',
            ]);
        }

        if ($affiliateLink->productId !== $product->id) {
            throw new ValidationException([
                'affiliate_link_id' => 'O link de afiliado não pertence ao produto selecionado.',
            ]);
        }

        return $result['data'];
    }
}

