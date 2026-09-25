<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ValidationException;
use App\Models\Sale;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\SaleValidator;
use DomainException;
use PDOException;

final class SaleController
{
    public function __construct(
        private readonly SaleRepository $sales,
        private readonly ProductRepository $products,
        private readonly CampaignRepository $campaigns,
        private readonly SaleValidator $validator,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (Sale $sale): array => $sale->toArray(),
            $this->sales->all()
        );
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): Sale
    {
        $result = $this->validator->validate($input);

        if ($result['errors'] !== []) {
            throw new ValidationException($result['errors']);
        }

        $data = $result['data'];

        if ($data['product_id'] !== null) {
            $product = $this->products->find($data['product_id']);

            if ($product === null) {
                throw new ValidationException(['product_id' => 'O produto selecionado não existe.']);
            }

            if ($product->marketplace !== $data['marketplace']) {
                throw new ValidationException([
                    'product_id' => 'O produto deve pertencer ao marketplace da venda.',
                ]);
            }
        }

        if ($data['campaign_id'] !== null && $this->campaigns->find($data['campaign_id']) === null) {
            throw new ValidationException(['campaign_id' => 'A campanha selecionada não existe.']);
        }

        try {
            return $this->sales->create($data);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw new DomainException(
                    'Já existe uma venda deste marketplace com a mesma referência externa.'
                );
            }

            throw $exception;
        }
    }
}
