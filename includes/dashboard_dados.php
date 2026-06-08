<?php

require_once __DIR__ . '/conexao.php';

function money_br(float $value, int $decimals = 2): string
{
    return 'R$ ' . number_format($value, $decimals, ',', '.');
}

function number_br(float $value, int $decimals = 0): string
{
    return number_format($value, $decimals, ',', '.');
}

function fetch_one(?PDO $pdo, string $sql, array $fallback): array
{
    if (!$pdo) {
        return $fallback;
    }

    try {
        $row = $pdo->query($sql)->fetch();
        return $row ?: $fallback;
    } catch (Throwable $e) {
        return $fallback;
    }
}

function fetch_all(?PDO $pdo, string $sql, array $fallback): array
{
    if (!$pdo) {
        return $fallback;
    }

    try {
        $rows = $pdo->query($sql)->fetchAll();
        return $rows ?: $fallback;
    } catch (Throwable $e) {
        return $fallback;
    }
}

function row_first_value(array $row, array $keys, mixed $fallback = null): mixed
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null) {
            return $row[$key];
        }
    }

    return $fallback;
}

function normalize_monthly_revenue(array $rows): array
{
    $grouped = [];

    foreach ($rows as $row) {
        $year = (int)row_first_value($row, ['Ano', 'ano', 'Year'], 2026);
        $month = (int)row_first_value($row, ['Mes', 'mes', 'Month'], 0);
        $value = (float)row_first_value($row, ['Receita', 'Total', 'Valor'], 0);

        if ($month < 1 || $month > 12) {
            continue;
        }

        $key = sprintf('%04d-%02d', $year, $month);
        if (!isset($grouped[$key])) {
            $grouped[$key] = ['Ano' => $year, 'Mes' => $month, 'Receita' => 0];
        }

        $grouped[$key]['Receita'] += $value;
    }

    ksort($grouped);
    return array_values($grouped);
}

$fallbackKpi = [
    'ReceitaTotal' => 150330,
    'TotalCorridas' => 3000,
    'TicketMedio' => 50.11,
    'TotalKM' => 61250,
];

$fallbackLucro = ['Lu' => 90385];

$fallbackMensal = [
    ['Ano' => 2026, 'Mes' => 1, 'Receita' => 5000],
    ['Ano' => 2026, 'Mes' => 2, 'Receita' => 10200],
    ['Ano' => 2026, 'Mes' => 3, 'Receita' => 8200],
    ['Ano' => 2026, 'Mes' => 4, 'Receita' => 9000],
    ['Ano' => 2026, 'Mes' => 5, 'Receita' => 13200],
    ['Ano' => 2026, 'Mes' => 6, 'Receita' => 10300],
    ['Ano' => 2026, 'Mes' => 7, 'Receita' => 11200],
    ['Ano' => 2026, 'Mes' => 8, 'Receita' => 15300],
    ['Ano' => 2026, 'Mes' => 9, 'Receita' => 13100],
    ['Ano' => 2026, 'Mes' => 10, 'Receita' => 14900],
    ['Ano' => 2026, 'Mes' => 11, 'Receita' => 11300],
    ['Ano' => 2026, 'Mes' => 12, 'Receita' => 17100],
];

$fallbackPlataformas = [
    ['NomePlataforma' => 'Uber', 'Receita' => 92712, 'Corridas' => 1833],
    ['NomePlataforma' => '99', 'Receita' => 52638, 'Corridas' => 1061],
    ['NomePlataforma' => 'InDrive', 'Receita' => 4980, 'Corridas' => 106],
];

$fallbackPagamentos = [
    ['Tipo_Pagamento' => 'PIX', 'Receita' => 67648],
    ['Tipo_Pagamento' => 'Cartao', 'Receita' => 60132],
    ['Tipo_Pagamento' => 'Dinheiro', 'Receita' => 22550],
];

$fallbackCustos = [
    ['Descricao' => 'Combustivel', 'Total' => 22000],
    ['Descricao' => 'Manutencao', 'Total' => 12000],
    ['Descricao' => 'Seguro', 'Total' => 10000],
    ['Descricao' => 'Pedagio', 'Total' => 8000],
    ['Descricao' => 'Lavagem', 'Total' => 7000],
];

$fallbackMotoristas = [
    ['Motorista' => 'Joao Silva', 'Corridas' => 342, 'Receita' => 16174, 'TicketMedio' => 47.30, 'Avaliacao' => 4.98],
    ['Motorista' => 'Lucas Oliveira', 'Corridas' => 331, 'Receita' => 16116, 'TicketMedio' => 48.68, 'Avaliacao' => 4.96],
    ['Motorista' => 'Carlos Souza', 'Corridas' => 325, 'Receita' => 15987, 'TicketMedio' => 49.19, 'Avaliacao' => 4.93],
    ['Motorista' => 'Bruno Ferreira', 'Corridas' => 298, 'Receita' => 14276, 'TicketMedio' => 47.89, 'Avaliacao' => 4.92],
    ['Motorista' => 'Rafael Martins', 'Corridas' => 290, 'Receita' => 13842, 'TicketMedio' => 47.73, 'Avaliacao' => 4.90],
];

$fallbackVeiculos = [
    ['Veiculo' => 'Toyota Corolla', 'Corridas' => 542, 'Receita' => 26418, 'KMRodados' => 10245],
    ['Veiculo' => 'Hyundai HB20', 'Corridas' => 487, 'Receita' => 22371, 'KMRodados' => 8824],
    ['Veiculo' => 'Honda Civic', 'Corridas' => 382, 'Receita' => 18562, 'KMRodados' => 7912],
    ['Veiculo' => 'Chevrolet Onix', 'Corridas' => 301, 'Receita' => 14963, 'KMRodados' => 6231],
    ['Veiculo' => 'VW Virtus', 'Corridas' => 245, 'Receita' => 12457, 'KMRodados' => 5128],
];

$kpi = fetch_one($pdo, 'SELECT TOP 1 * FROM vw_KPI_Geral', $fallbackKpi);
$lucro = fetch_one($pdo, 'SELECT TOP 1 * FROM vw_LucroGeral', $fallbackLucro);
$plataformas = fetch_all($pdo, 'SELECT * FROM vw_ReceitaPlataforma', $fallbackPlataformas);
$mensalCompleto = normalize_monthly_revenue(fetch_all($pdo, 'SELECT * FROM vw_ReceitaMensal ORDER BY Ano, Mes', $fallbackMensal));
$anosDisponiveis = array_values(array_unique(array_map(fn($row) => (int)$row['Ano'], $mensalCompleto)));
$anoSelecionado = $anosDisponiveis ? max($anosDisponiveis) : 2026;
$mensal = array_values(array_filter($mensalCompleto, fn($row) => (int)$row['Ano'] === $anoSelecionado));
$motoristas = fetch_all($pdo, 'SELECT TOP 5 * FROM vw_TopMotoristas', $fallbackMotoristas);
$veiculos = fetch_all($pdo, 'SELECT TOP 5 * FROM vw_TopVeiculos', $fallbackVeiculos);
$pagamentos = fetch_all($pdo, 'SELECT * FROM vw_FormaPagamento', $fallbackPagamentos);
$custos = fetch_all($pdo, 'SELECT * FROM vw_CustosCategoria', $fallbackCustos);

$kpisPorPlataforma = fetch_all($pdo, "
    SELECT
        p.NomePlataforma,
        COUNT(c.idCorrida) AS TotalCorridas,
        SUM(c.Valor_Corrida) AS ReceitaTotal,
        AVG(c.Valor_Corrida) AS TicketMedio,
        SUM(c.Distancia_KM) AS TotalKM
    FROM Corrida c
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    GROUP BY p.NomePlataforma
", array_map(fn($row) => [
    'NomePlataforma' => $row['NomePlataforma'],
    'TotalCorridas' => $row['Corridas'] ?? 0,
    'ReceitaTotal' => $row['Receita'] ?? 0,
    'TicketMedio' => ($row['Corridas'] ?? 0) ? ($row['Receita'] ?? 0) / $row['Corridas'] : 0,
    'TotalKM' => 0,
], $fallbackPlataformas));

$mensalPorPlataforma = fetch_all($pdo, "
    SELECT
        d.Ano,
        d.Mes,
        p.NomePlataforma,
        SUM(c.Valor_Corrida) AS Receita
    FROM Corrida c
    INNER JOIN Data d ON d.idData = c.idData
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    GROUP BY d.Ano, d.Mes, p.NomePlataforma
    ORDER BY d.Ano, d.Mes, p.NomePlataforma
", []);

$pagamentosPorPlataforma = fetch_all($pdo, "
    SELECT
        p.NomePlataforma,
        fp.Tipo_Pagamento,
        COUNT(c.idCorrida) AS Quantidade,
        SUM(c.Valor_Corrida) AS Receita
    FROM Corrida c
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    INNER JOIN FormaPagamento fp ON fp.idFormaPagamento = c.idFormaPagamento
    GROUP BY p.NomePlataforma, fp.Tipo_Pagamento
    ORDER BY p.NomePlataforma, fp.Tipo_Pagamento
", []);

$custosPorPlataforma = fetch_all($pdo, "
    SELECT
        p.NomePlataforma,
        tc.Descricao,
        SUM(cu.Valor) AS Total
    FROM Custo cu
    INNER JOIN Corrida c ON c.idCorrida = cu.idCorrida
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    INNER JOIN TipoCusto tc ON tc.idCusto = cu.idCusto
    GROUP BY p.NomePlataforma, tc.Descricao
    ORDER BY p.NomePlataforma, tc.Descricao
", []);

$motoristasPorPlataforma = fetch_all($pdo, "
    SELECT
        p.NomePlataforma,
        m.Nome,
        COUNT(c.idCorrida) AS Corridas,
        SUM(c.Valor_Corrida) AS Receita,
        AVG(c.Valor_Corrida) AS TicketMedio
    FROM Corrida c
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    INNER JOIN Motorista m ON m.idMotorista = c.idMotorista
    GROUP BY p.NomePlataforma, m.Nome
    ORDER BY p.NomePlataforma, Receita DESC
", []);

$veiculosPorPlataforma = fetch_all($pdo, "
    SELECT
        p.NomePlataforma,
        v.Marca,
        v.Modelo,
        COUNT(c.idCorrida) AS Corridas,
        SUM(c.Valor_Corrida) AS Receita,
        SUM(c.Distancia_KM) AS KM
    FROM Corrida c
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    INNER JOIN Veiculo v ON v.idVeiculo = c.idVeiculo
    GROUP BY p.NomePlataforma, v.Marca, v.Modelo
    ORDER BY p.NomePlataforma, Receita DESC
", []);

$ultimasCorridas = fetch_all($pdo, "
    SELECT TOP 20
        c.idCorrida,
        d.Data_Completa,
        d.Dia,
        d.Mes,
        d.Ano,
        p.NomePlataforma,
        m.Nome AS Motorista,
        fp.Tipo_Pagamento,
        o.Endereco AS Origem,
        o.Bairro AS BairroOrigem,
        o.Cidade AS CidadeOrigem,
        de.Endereco AS Destino,
        de.Bairro AS BairroDestino,
        de.Cidade AS CidadeDestino,
        c.Valor_Corrida,
        c.Distancia_KM
    FROM Corrida c
    INNER JOIN Data d ON d.idData = c.idData
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    INNER JOIN Motorista m ON m.idMotorista = c.idMotorista
    INNER JOIN FormaPagamento fp ON fp.idFormaPagamento = c.idFormaPagamento
    INNER JOIN Local o ON o.idLocal = c.idOrigem
    INNER JOIN Local de ON de.idLocal = c.idDestino
    ORDER BY d.Data_Completa DESC, c.idCorrida DESC
", []);

$custosPorVeiculo = fetch_all($pdo, "
    SELECT
        p.NomePlataforma,
        v.Marca,
        v.Modelo,
        COUNT(DISTINCT c.idCorrida) AS Corridas,
        SUM(c.Valor_Corrida) AS Receita,
        SUM(c.Distancia_KM) AS KM,
        SUM(cu.Valor) AS CustoTotal
    FROM Corrida c
    INNER JOIN Plataforma p ON p.idPlataforma = c.idPlataforma
    INNER JOIN Veiculo v ON v.idVeiculo = c.idVeiculo
    LEFT JOIN Custo cu ON cu.idCorrida = c.idCorrida
    GROUP BY p.NomePlataforma, v.Marca, v.Modelo
    ORDER BY CustoTotal DESC
", []);

$receitaTotal = (float)($kpi['ReceitaTotal'] ?? 0);
$lucroLiquido = (float)($lucro['Lu'] ?? $lucro['LucroLiquido'] ?? 0);
$totalCorridas = (int)($kpi['TotalCorridas'] ?? 0);
$ticketMedio = (float)($kpi['TicketMedio'] ?? ($totalCorridas ? $receitaTotal / $totalCorridas : 0));
$totalKm = (float)($kpi['TotalKM'] ?? $kpi['KMRodados'] ?? 0);
$margemLucro = $receitaTotal > 0 ? ($lucroLiquido / $receitaTotal) * 100 : 0;

$resumoDia = [
    'corridas' => 48,
    'receita' => 2413,
    'tempo' => '10h 25m',
    'km' => 287,
    'ganhoHora' => 233.50,
    'avaliacao' => 4.94,
];
