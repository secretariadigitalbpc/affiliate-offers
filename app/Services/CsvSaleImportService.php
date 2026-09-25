<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ValidationException;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use DateTimeImmutable;
use PDO;
use RuntimeException;
use Throwable;

final class CsvSaleImportService
{
    private const MAX_BYTES = 2097152;
    private const MAX_ROWS = 5000;
    private const HEADERS = [
        'marketplace',
        'external_sale_reference',
        'product_id',
        'campaign_id',
        'quantity',
        'gross_value',
        'commission_value',
        'status',
        'sale_date',
    ];

    public function __construct(
        private readonly PDO $pdo,
        private readonly SaleRepository $sales,
        private readonly ProductRepository $products,
        private readonly CampaignRepository $campaigns,
        private readonly SaleValidator $validator,
    ) {
    }

    /**
     * @return array{total_rows: int, imported: int, ignored: int, invalid: int, errors: list<array{line: int, fields: array<string, string>}>}
     */
    public function import(string $path, string $originalName, int $size): array
    {
        $this->validateFile($path, $originalName, $size);
        [$headers, $rows] = $this->readRows($path);
        $headerPositions = array_flip($headers);
        $prepared = [];
        $errors = [];
        $ignored = 0;
        $references = [];

        foreach ($rows as $row) {
            $line = $row['line'];
            $values = $row['values'];
            $input = [];

            if (!$row['columns_valid']) {
                $errors[] = [
                    'line' => $line,
                    'fields' => ['row' => 'A quantidade de colunas não corresponde ao cabeçalho.'],
                ];
                continue;
            }

            foreach (self::HEADERS as $header) {
                $input[$header] = $values[$headerPositions[$header]] ?? '';
            }

            $result = $this->validator->validate($input);

            if (trim((string) $input['external_sale_reference']) === '') {
                $result['errors']['external_sale_reference'] =
                    'A referência externa é obrigatória na importação.';
            }

            if ($result['errors'] === []) {
                $this->validateRelationships($result['data'], $result['errors']);
            }

            if ($result['errors'] !== []) {
                $errors[] = ['line' => $line, 'fields' => $result['errors']];
                continue;
            }

            $data = $result['data'];
            $referenceKey = $data['marketplace'] . "\0" . $data['external_sale_reference'];

            if (
                isset($references[$referenceKey])
                || $this->sales->existsByReference(
                    (string) $data['marketplace'],
                    (string) $data['external_sale_reference']
                )
            ) {
                $ignored++;
                continue;
            }

            $references[$referenceKey] = true;
            $data['imported_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $prepared[] = $data;
        }

        $imported = count($prepared);

        if ($prepared !== []) {
            $concurrentIgnored = $this->persist($prepared);
            $ignored += $concurrentIgnored;
            $imported -= $concurrentIgnored;
        }

        return [
            'total_rows' => count($rows),
            'imported' => $imported,
            'ignored' => $ignored,
            'invalid' => count($errors),
            'errors' => $errors,
        ];
    }

    private function validateFile(string $path, string $originalName, int $size): void
    {
        $errors = [];

        if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'csv') {
            $errors['sales_file'] = 'Envie um arquivo com extensão .csv.';
        }

        if ($size < 1 || $size > self::MAX_BYTES) {
            $errors['sales_file'] = 'O CSV deve ter entre 1 byte e 2 MB.';
        }

        if (!is_file($path) || !is_readable($path)) {
            $errors['sales_file'] = 'O arquivo enviado não pôde ser lido.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @return array{0: list<string>, 1: list<array{line: int, values: list<string>, columns_valid: bool}>}
     */
    private function readRows(string $path): array
    {
        $contents = file_get_contents($path);

        if ($contents === false || preg_match('//u', $contents) !== 1) {
            throw new ValidationException([
                'sales_file' => 'O CSV deve estar codificado em UTF-8.',
            ]);
        }

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Não foi possível preparar a leitura do CSV.');
        }

        fwrite($stream, $contents);
        rewind($stream);
        $firstLine = fgets($stream);

        if ($firstLine === false) {
            fclose($stream);
            throw new ValidationException(['sales_file' => 'O CSV está vazio.']);
        }

        $delimiter = count(str_getcsv($firstLine, ';')) >= count(str_getcsv($firstLine, ','))
            ? ';'
            : ',';
        rewind($stream);
        $headers = fgetcsv($stream, 0, $delimiter, '"', '\\');

        if ($headers === false) {
            fclose($stream);
            throw new ValidationException(['sales_file' => 'O cabeçalho do CSV é inválido.']);
        }

        $headers = array_map(
            static fn (string $header): string => trim($header),
            $headers
        );
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]) ?? $headers[0];
        $missing = array_values(array_diff(self::HEADERS, $headers));

        if ($missing !== [] || count($headers) !== count(array_unique($headers))) {
            fclose($stream);
            throw new ValidationException([
                'sales_file' => $missing !== []
                    ? 'Cabeçalhos obrigatórios ausentes: ' . implode(', ', $missing) . '.'
                    : 'O CSV contém cabeçalhos duplicados.',
            ]);
        }

        $rows = [];
        $line = 1;

        while (($values = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
            $line++;

            if ($this->blankRow($values)) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                fclose($stream);
                throw new ValidationException([
                    'sales_file' => 'O CSV pode conter no máximo 5.000 linhas de dados.',
                ]);
            }

            if (count($values) !== count($headers)) {
                $rows[] = [
                    'line' => $line,
                    'values' => array_pad($values, count($headers), ''),
                    'columns_valid' => false,
                ];
                continue;
            }

            $rows[] = ['line' => $line, 'values' => $values, 'columns_valid' => true];
        }

        fclose($stream);

        if ($rows === []) {
            throw new ValidationException(['sales_file' => 'O CSV não contém linhas de dados.']);
        }

        return [$headers, $rows];
    }

    /** @param list<string|null> $values */
    private function blankRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $errors
     */
    private function validateRelationships(array $data, array &$errors): void
    {
        if ($data['product_id'] !== null) {
            $product = $this->products->find($data['product_id']);

            if ($product === null) {
                $errors['product_id'] = 'O produto informado não existe.';
            } elseif ($product->marketplace !== $data['marketplace']) {
                $errors['product_id'] = 'O produto não pertence ao marketplace da venda.';
            }
        }

        if ($data['campaign_id'] !== null && $this->campaigns->find($data['campaign_id']) === null) {
            $errors['campaign_id'] = 'A campanha informada não existe.';
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function persist(array $rows): int
    {
        $ignored = 0;
        $this->pdo->beginTransaction();

        try {
            foreach ($rows as $row) {
                try {
                    $this->sales->create($row);
                } catch (\PDOException $exception) {
                    if (($exception->errorInfo[1] ?? null) === 1062) {
                        $ignored++;
                        continue;
                    }

                    throw $exception;
                }
            }

            $this->pdo->commit();

            return $ignored;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
