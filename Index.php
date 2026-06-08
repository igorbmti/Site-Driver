<?php

require_once __DIR__ . '/includes/dashboard_dados.php';

$now = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$platformTotal = array_sum(array_map(fn($item) => (float)($item['Receita'] ?? 0), $plataformas));
$paymentTotal = array_sum(array_map(fn($item) => (float)($item['Receita'] ?? 0), $pagamentos));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Focus Driver Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark"><i class="fa-solid fa-gauge-high"></i></div>
                <div>
                    <strong>FOCUS</strong>
                    <span>DRIVER</span>
                </div>
            </div>

            <nav class="menu">
                <a href="#dashboard" class="active" data-target="dashboard"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
                <a href="#corridas" data-target="corridas"><i class="fa-solid fa-car-side"></i><span>Corridas</span></a>
                <a href="#motoristas" data-target="motoristas"><i class="fa-solid fa-user-group"></i><span>Motoristas</span></a>
                <a href="#veiculos" data-target="veiculos"><i class="fa-solid fa-car"></i><span>Veiculos</span></a>
                <a href="#pagamentos" data-target="pagamentos"><i class="fa-regular fa-credit-card"></i><span>Pagamentos</span></a>
                <a href="#relatorios" data-target="relatorios"><i class="fa-solid fa-chart-simple"></i><span>Relatorios</span></a>
                <a href="#configuracoes" data-target="configuracoes"><i class="fa-solid fa-gear"></i><span>Configuracoes</span></a>
            </nav>

            <div class="sidebar-footer">
                <div class="profile">
                    <img src="https://i.pravatar.cc/80?img=12" alt="Administrador">
                    <div>
                        <strong>Administrador</strong>
                        <span><i></i> Online</span>
                    </div>
                </div>
                <a href="#sair" class="logout" data-action="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Sair</span></a>
            </div>
        </aside>

        <main class="content">
            <header class="topbar">
                <button class="icon-button" id="menuToggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div class="title-block">
                    <h1>Dashboard Operacional</h1>
                    <p>Visao geral da operacao</p>
                </div>
                <div class="top-actions">
                    <div class="date-pill"><i class="fa-regular fa-calendar"></i><?= $now->format('d/m/Y - H:i'); ?></div>
                    <button class="icon-button alert" id="notificationButton" aria-label="Notificacoes"><i class="fa-regular fa-bell"></i><span>3</span></button>
                    <button class="icon-button" id="themeButton" aria-label="Modo escuro"><i class="fa-regular fa-moon"></i></button>
                    <button class="filter-button" id="filterToggle" aria-expanded="false"><i class="fa-solid fa-filter"></i>Filtros<i class="fa-solid fa-chevron-down"></i></button>
                </div>
            </header>

            <section class="filter-panel" id="configuracoes" aria-label="Filtros do dashboard">
                <label>
                    Ano
                    <select id="filterYear">
                        <?php foreach ($anosDisponiveis as $ano): ?>
                            <option value="<?= (int)$ano; ?>" <?= (int)$ano === (int)$anoSelecionado ? 'selected' : ''; ?>><?= (int)$ano; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Ordenar roscas
                    <select id="filterSort">
                        <option value="value">Maior valor</option>
                        <option value="name">Nome</option>
                    </select>
                </label>
                <button type="button" id="resetFilters"><i class="fa-solid fa-rotate-left"></i>Limpar</button>
            </section>

            <div class="status-line <?= $dbStatus['connected'] ? 'ok' : 'warn'; ?>">
                <i class="fa-solid <?= $dbStatus['connected'] ? 'fa-database' : 'fa-triangle-exclamation'; ?>"></i>
                <?= htmlspecialchars($dbStatus['connected'] ? 'Dados carregados do banco.' : 'Banco indisponivel. Exibindo dados demonstrativos.', ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <section class="filters" id="dashboard" aria-label="Plataformas">
                <button class="active" data-platform="Todos">Todos</button>
                <button data-platform="Uber"><span class="mini-logo uber">Uber</span></button>
                <button data-platform="99"><span class="mini-logo ninenine">99</span></button>
                <button data-platform="InDrive"><span class="mini-logo indrive">InDrive</span></button>
            </section>

            <section class="kpi-grid" id="metricCards">
                <article class="kpi-card green">
                    <i class="fa-solid fa-dollar-sign"></i>
                    <span>Receita Total</span>
                    <strong data-kpi="receita"><?= money_br($receitaTotal, 0); ?></strong>
                    <small class="up">▲ 12,4% este mes</small>
                    <div class="spark"></div>
                </article>
                <article class="kpi-card blue">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                    <span>Lucro Liquido</span>
                    <strong data-kpi="lucro"><?= money_br($lucroLiquido, 0); ?></strong>
                    <small class="up">▲ 8,7% este mes</small>
                    <div class="spark"></div>
                </article>
                <article class="kpi-card purple">
                    <i class="fa-solid fa-taxi"></i>
                    <span>Total Corridas</span>
                    <strong data-kpi="corridas"><?= number_br($totalCorridas); ?></strong>
                    <small class="mixed">▲ 5,2% este mes</small>
                    <div class="spark"></div>
                </article>
                <article class="kpi-card orange">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Ticket Medio</span>
                    <strong data-kpi="ticket"><?= money_br($ticketMedio); ?></strong>
                    <small class="down">▼ 3,1% este mes</small>
                    <div class="spark"></div>
                </article>
                <article class="kpi-card cyan">
                    <i class="fa-solid fa-road"></i>
                    <span>KM Rodados</span>
                    <strong data-kpi="km"><?= number_br($totalKm); ?> km</strong>
                    <small class="up">▲ 7,8% este mes</small>
                    <div class="spark"></div>
                </article>
                <article class="kpi-card yellow">
                    <i class="fa-solid fa-percent"></i>
                    <span>Margem de Lucro</span>
                    <strong data-kpi="margem"><?= number_br($margemLucro, 2); ?>%</strong>
                    <small class="warn">▲ 4,5% este mes</small>
                    <div class="spark"></div>
                </article>
            </section>

            <section class="dashboard-grid">
                <article class="panel panel-large" id="relatorios" data-section="relatorios dashboard">
                    <div class="panel-head">
                        <h2>Receita Mensal (R$)</h2>
                        <select id="yearSelect" aria-label="Ano">
                            <?php foreach ($anosDisponiveis as $ano): ?>
                                <option value="<?= (int)$ano; ?>" <?= (int)$ano === (int)$anoSelecionado ? 'selected' : ''; ?>><?= (int)$ano; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <canvas id="receitaMensal"></canvas>
                </article>

                <article class="panel" id="plataformas" data-section="dashboard">
                    <h2>Receita por Plataforma</h2>
                    <div class="donut-layout">
                        <canvas id="plataformaChart"></canvas>
                        <ul id="platformLegend"></ul>
                    </div>
                </article>

                <article class="panel" id="pagamentos" data-section="pagamentos dashboard">
                    <h2>Formas de Pagamento</h2>
                    <div class="donut-layout compact">
                        <canvas id="pagamentoChart"></canvas>
                        <ul id="paymentLegend"></ul>
                    </div>
                </article>

                <article class="panel" id="custos" data-section="custos dashboard">
                    <h2>Custos por Categoria (R$)</h2>
                    <canvas id="custosChart"></canvas>
                </article>

                <article class="panel" id="corridas" data-section="dashboard">
                    <h2><i class="fa-solid fa-gas-pump"></i> Corridas por Plataforma</h2>
                    <canvas id="corridasChart"></canvas>
                </article>

                <article class="panel table-panel wide-panel" id="ultimasCorridas" data-section="corridas">
                    <div class="panel-head">
                        <h2>Ultimas Corridas</h2>
                        <span class="online"><i></i> Atualizado</span>
                    </div>
                    <table>
                        <thead><tr><th>#</th><th>Data</th><th>Horario</th><th>Plataforma</th><th>Origem</th><th>Destino</th><th>Motorista</th><th>Valor</th></tr></thead>
                        <tbody id="ridesBody"></tbody>
                    </table>
                </article>

                <article class="panel summary-panel" data-section="dashboard">
                    <div class="panel-head">
                        <h2>Resumo do Dia</h2>
                        <span class="online"><i></i> Online</span>
                    </div>
                    <div class="summary-grid">
                        <div><i class="fa-regular fa-calendar-days"></i><span>Corridas Hoje</span><strong><?= $resumoDia['corridas']; ?></strong></div>
                        <div><i class="fa-solid fa-arrow-trend-up"></i><span>Receita Hoje</span><strong><?= money_br($resumoDia['receita']); ?></strong></div>
                        <div><i class="fa-regular fa-clock"></i><span>Tempo Online</span><strong><?= $resumoDia['tempo']; ?></strong></div>
                        <div><i class="fa-solid fa-road"></i><span>KM Hoje</span><strong><?= $resumoDia['km']; ?> km</strong></div>
                        <div><i class="fa-solid fa-hand-holding-dollar"></i><span>Ganho por Hora</span><strong><?= money_br($resumoDia['ganhoHora']); ?></strong></div>
                        <div><i class="fa-regular fa-star"></i><span>Avaliacao Media</span><strong><?= number_br($resumoDia['avaliacao'], 2); ?></strong></div>
                    </div>
                </article>

                <article class="panel table-panel" id="motoristas" data-section="motoristas dashboard">
                    <div class="panel-head">
                        <h2>Top 5 Motoristas</h2>
                        <button type="button" data-action="focus-table" data-target="motoristas">Ver todos</button>
                    </div>
                    <table>
                        <thead><tr><th>#</th><th>Motorista</th><th>Corridas</th><th>Receita (R$)</th><th>Ticket Medio</th><th>Avaliacao</th></tr></thead>
                        <tbody id="driversBody">
                            <?php foreach ($motoristas as $index => $item): ?>
                                <?php $avaliacao = (float)($item['Avaliacao'] ?? (4.98 - ($index * 0.02))); ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= htmlspecialchars($item['Motorista'] ?? $item['NomeMotorista'] ?? $item['Nome'] ?? 'Motorista'); ?></td>
                                    <td><?= number_br((float)($item['Corridas'] ?? 0)); ?></td>
                                    <td><?= money_br((float)($item['Receita'] ?? 0)); ?></td>
                                    <td><?= money_br((float)($item['TicketMedio'] ?? 0)); ?></td>
                                    <td><span class="stars">★★★★★</span> <?= number_br($avaliacao, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <article class="panel table-panel" id="veiculos" data-section="veiculos dashboard">
                    <div class="panel-head">
                        <h2>Top 5 Veiculos</h2>
                        <button type="button" data-action="focus-table" data-target="veiculos">Ver todos</button>
                    </div>
                    <table>
                        <thead><tr><th>#</th><th>Veiculo</th><th>Corridas</th><th>Receita (R$)</th><th>KM Rodados</th></tr></thead>
                        <tbody id="vehiclesBody">
                            <?php foreach ($veiculos as $index => $item): ?>
                                <?php $veiculo = trim(($item['Marca'] ?? '') . ' ' . ($item['Modelo'] ?? '')) ?: ($item['Veiculo'] ?? 'Veiculo'); ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= htmlspecialchars($veiculo); ?></td>
                                    <td><?= number_br((float)($item['Corridas'] ?? 0)); ?></td>
                                    <td><?= money_br((float)($item['Receita'] ?? 0)); ?></td>
                                    <td><?= number_br((float)($item['KMRodados'] ?? $item['TotalKM'] ?? $item['KM'] ?? 0)); ?> km</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <article class="panel table-panel" id="custosVeiculos" data-section="veiculos">
                    <div class="panel-head">
                        <h2>Custos por Veiculo</h2>
                    </div>
                    <table>
                        <thead><tr><th>#</th><th>Veiculo</th><th>Corridas</th><th>Receita</th><th>Custo</th><th>Lucro</th></tr></thead>
                        <tbody id="vehicleCostsBody"></tbody>
                    </table>
                </article>
            </section>
        </main>
    </div>

    <script>
        window.dashboardData = {
            receitaMensal: <?= json_encode($mensal, JSON_NUMERIC_CHECK); ?>,
            receitaMensalCompleta: <?= json_encode($mensalCompleto, JSON_NUMERIC_CHECK); ?>,
            receitaMensalPorPlataforma: <?= json_encode($mensalPorPlataforma, JSON_NUMERIC_CHECK); ?>,
            kpis: <?= json_encode([
                'receita' => $receitaTotal,
                'lucro' => $lucroLiquido,
                'corridas' => $totalCorridas,
                'ticket' => $ticketMedio,
                'km' => $totalKm,
                'margem' => $margemLucro,
            ], JSON_NUMERIC_CHECK); ?>,
            plataforma: <?= json_encode($plataformas, JSON_NUMERIC_CHECK); ?>,
            kpisPorPlataforma: <?= json_encode($kpisPorPlataforma, JSON_NUMERIC_CHECK); ?>,
            pagamentos: <?= json_encode($pagamentos, JSON_NUMERIC_CHECK); ?>,
            pagamentosPorPlataforma: <?= json_encode($pagamentosPorPlataforma, JSON_NUMERIC_CHECK); ?>,
            custos: <?= json_encode($custos, JSON_NUMERIC_CHECK); ?>,
            custosPorPlataforma: <?= json_encode($custosPorPlataforma, JSON_NUMERIC_CHECK); ?>,
            motoristas: <?= json_encode($motoristas, JSON_NUMERIC_CHECK); ?>,
            motoristasPorPlataforma: <?= json_encode($motoristasPorPlataforma, JSON_NUMERIC_CHECK); ?>,
            veiculos: <?= json_encode($veiculos, JSON_NUMERIC_CHECK); ?>,
            veiculosPorPlataforma: <?= json_encode($veiculosPorPlataforma, JSON_NUMERIC_CHECK); ?>,
            ultimasCorridas: <?= json_encode($ultimasCorridas, JSON_NUMERIC_CHECK); ?>,
            custosPorVeiculo: <?= json_encode($custosPorVeiculo, JSON_NUMERIC_CHECK); ?>
        };
    </script>
    <script src="assets/dashboard.js"></script>
</body>
</html>
