<?php

declare(strict_types=1);

use App\Config\Database;
use App\Helpers\ValidationException;
use App\Repositories\CampaignRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\CsvSaleImportService;
use App\Services\SaleValidator;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Database::connect();
$sales = new SaleRepository($pdo);
$service = new CsvSaleImportService(
    $pdo,
    $sales,
    new ProductRepository($pdo),
    new CampaignRepository($pdo),
    new SaleValidator()
);
$token = bin2hex(random_bytes(6));
$referenceOne = 'CSV-ML-' . $token;
$referenceTwo = 'CSV-SHOPEE-' . $token;
$invalidReference = 'CSV-INVALID-' . $token;
$validPath = tempnam(sys_get_temp_dir(), 'sales-import-');
$invalidPath = tempnam(sys_get_temp_dir(), 'sales-invalid-');

if ($validPath === false || $invalidPath === false) {
    throw new RuntimeException('Não foi possível criar os arquivos temporários do teste.');
}

$csv = implode("\n", [
    'marketplace;external_sale_reference;product_id;campaign_id;quantity;gross_value;commission_value;status;sale_date',
    "mercado_livre;$referenceOne;;;1;199,90;24,50;approved;2026-09-25 10:30:00",
    "shopee;$referenceTwo;;;2;89,90;8,99;pending;2026-09-25 11:00:00",
    "mercado_livre;$referenceOne;;;1;199,90;24,50;approved;2026-09-25 10:30:00",
    "mercado_livre;$invalidReference;;;1;10,00;20,00;approved;2026-09-25 12:00:00",
]) . "\n";
$invalidCsv = "marketplace;external_sale_reference\nmercado_livre;INVALID\n";
file_put_contents($validPath, $csv);
file_put_contents($invalidPath, $invalidCsv);

try {
    $result = $service->import($validPath, 'vendas.csv', strlen($csv));

    if (
        $result['total_rows'] !== 4
        || $result['imported'] !== 2
        || $result['ignored'] !== 1
        || $result['invalid'] !== 1
        || $result['errors'][0]['line'] !== 5
    ) {
        throw new RuntimeException('O relatório da primeira importação está incorreto.');
    }

    $imported = array_values(array_filter(
        $sales->all(),
        static fn ($sale): bool => in_array(
            $sale->externalSaleReference,
            [$referenceOne, $referenceTwo],
            true
        )
    ));

    if (count($imported) !== 2 || $imported[0]->importedAt === null || $imported[1]->importedAt === null) {
        throw new RuntimeException('As vendas importadas ou a data de importação estão incorretas.');
    }

    $repeat = $service->import($validPath, 'vendas.csv', strlen($csv));

    if ($repeat['imported'] !== 0 || $repeat['ignored'] !== 3 || $repeat['invalid'] !== 1) {
        throw new RuntimeException('A repetição do arquivo não foi idempotente.');
    }

    try {
        $service->import($invalidPath, 'estrutura.csv', strlen($invalidCsv));
        throw new RuntimeException('Um CSV sem os cabeçalhos obrigatórios foi aceito.');
    } catch (ValidationException) {
        // Comportamento esperado, sem persistência parcial.
    }

    echo "Importação CSV: 2 importadas, 1 ignorada, 1 inválida e repetição idempotente = OK\n";
} finally {
    $statement = $pdo->prepare(
        'DELETE FROM sales WHERE external_sale_reference IN (:reference_one, :reference_two, :invalid_reference)'
    );
    $statement->execute([
        'reference_one' => $referenceOne,
        'reference_two' => $referenceTwo,
        'invalid_reference' => $invalidReference,
    ]);
    unlink($validPath);
    unlink($invalidPath);
}
