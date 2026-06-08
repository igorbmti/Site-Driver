const data = window.dashboardData || {};
const receitaMensalCompleta = data.receitaMensalCompleta || data.receitaMensal || [];
const receitaMensalPorPlataforma = data.receitaMensalPorPlataforma || [];
const plataformaBase = data.plataforma || [];
const kpisPorPlataforma = data.kpisPorPlataforma || [];
const pagamentosBase = data.pagamentos || [];
const pagamentosPorPlataforma = data.pagamentosPorPlataforma || [];
const custos = data.custos || [];
const custosPorPlataforma = data.custosPorPlataforma || [];
const motoristasBase = data.motoristas || [];
const motoristasPorPlataforma = data.motoristasPorPlataforma || [];
const veiculosBase = data.veiculos || [];
const veiculosPorPlataforma = data.veiculosPorPlataforma || [];
const ultimasCorridas = data.ultimasCorridas || [];
const custosPorVeiculo = data.custosPorVeiculo || [];
const baseKpis = data.kpis || {};

Chart.defaults.color = "#dce8f4";
Chart.defaults.borderColor = "rgba(126, 190, 229, .14)";
Chart.defaults.font.family = "'Segoe UI', Arial, sans-serif";

const currency = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" });
const currencyNoDecimals = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL", maximumFractionDigits: 0 });
const integer = new Intl.NumberFormat("pt-BR", { maximumFractionDigits: 0 });
const decimal = new Intl.NumberFormat("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const monthNames = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

const platformColors = {
    uber: "#00dd6f",
    "99": "#1688ff",
    indrive: "#ff8a00"
};

const paymentColors = {
    pix: "#00dd6f",
    cartao: "#1688ff",
    cartão: "#1688ff",
    dinheiro: "#ff8a00"
};

let selectedPlatform = "Todos";
let currentSection = "dashboard";
let sortMode = "value";
let selectedYear = Number(document.getElementById("yearSelect")?.value) || latestYear();
let receitaChart;
let plataformaChart;
let pagamentoChart;
let corridasChart;
let costsChart;

function asNumber(value) {
    return Number(String(value ?? 0).replace(",", ".")) || 0;
}

function field(row, names, fallback = 0) {
    const name = names.find((key) => row[key] !== undefined && row[key] !== null);
    return name ? row[name] : fallback;
}

function normalizeName(value) {
    return String(value ?? "").trim();
}

function key(value) {
    return normalizeName(value).toLowerCase();
}

function latestYear() {
    const years = receitaMensalCompleta.map((item) => asNumber(field(item, ["Ano"], 0))).filter(Boolean);
    return years.length ? Math.max(...years) : new Date().getFullYear();
}

function platformName(row) {
    return normalizeName(field(row, ["NomePlataforma", "Plataforma"], ""));
}

function platformColor(name) {
    return platformColors[key(name)] || "#17d6de";
}

function paymentName(row) {
    return normalizeName(field(row, ["Tipo_Pagamento", "Pagamento"], ""));
}

function paymentColor(name) {
    const normalized = key(name).normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    return paymentColors[normalized] || "#ffc400";
}

function sortedRows(rows, labelGetter, valueGetter) {
    const copy = [...rows];
    if (sortMode === "name") {
        return copy.sort((a, b) => labelGetter(a).localeCompare(labelGetter(b), "pt-BR"));
    }
    return copy.sort((a, b) => valueGetter(b) - valueGetter(a));
}

function makeGradient(ctx, color) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 230);
    gradient.addColorStop(0, `${color}88`);
    gradient.addColorStop(.72, `${color}18`);
    gradient.addColorStop(1, `${color}00`);
    return gradient;
}

function rowsForYear() {
    const source = selectedPlatform === "Todos"
        ? receitaMensalCompleta
        : receitaMensalPorPlataforma.filter((item) => platformName(item) === selectedPlatform);

    return source
        .filter((item) => asNumber(field(item, ["Ano"])) === selectedYear)
        .sort((a, b) => asNumber(field(a, ["Mes"])) - asNumber(field(b, ["Mes"])));
}

function platformRows() {
    const rows = sortedRows(
        plataformaBase,
        platformName,
        (row) => asNumber(field(row, ["Receita", "Total"]))
    );

    if (selectedPlatform === "Todos") {
        return rows;
    }

    return rows.filter((row) => platformName(row) === selectedPlatform);
}

function total(rows, valueFields) {
    return rows.reduce((sum, row) => sum + asNumber(field(row, valueFields)), 0);
}

function paymentRows() {
    const source = selectedPlatform === "Todos"
        ? pagamentosBase
        : pagamentosPorPlataforma.filter((row) => platformName(row) === selectedPlatform);

    return sortedRows(
        source,
        paymentName,
        (row) => asNumber(field(row, ["Receita", "Total"]))
    );
}

function costRows() {
    const source = selectedPlatform === "Todos"
        ? custos
        : custosPorPlataforma.filter((row) => platformName(row) === selectedPlatform);

    return sortedRows(
        source,
        (row) => normalizeName(field(row, ["Descricao", "Categoria"], "")),
        (row) => asNumber(field(row, ["Total", "Valor"]))
    );
}

function driverRows() {
    const source = selectedPlatform === "Todos"
        ? motoristasBase
        : motoristasPorPlataforma.filter((row) => platformName(row) === selectedPlatform);

    return [...source]
        .sort((a, b) => asNumber(field(b, ["Receita", "Total"])) - asNumber(field(a, ["Receita", "Total"])))
        .slice(0, 5);
}

function vehicleRows() {
    const source = selectedPlatform === "Todos"
        ? veiculosBase
        : veiculosPorPlataforma.filter((row) => platformName(row) === selectedPlatform);

    return [...source]
        .sort((a, b) => asNumber(field(b, ["Receita", "Total"])) - asNumber(field(a, ["Receita", "Total"])))
        .slice(0, 5);
}

function vehicleCostRows() {
    const source = selectedPlatform === "Todos"
        ? custosPorVeiculo
        : custosPorVeiculo.filter((row) => platformName(row) === selectedPlatform);

    return [...source]
        .sort((a, b) => asNumber(field(b, ["CustoTotal", "Total", "Valor"])) - asNumber(field(a, ["CustoTotal", "Total", "Valor"])))
        .slice(0, 8);
}

function latestRideRows() {
    const source = selectedPlatform === "Todos"
        ? ultimasCorridas
        : ultimasCorridas.filter((row) => platformName(row) === selectedPlatform);

    return source.slice(0, 12);
}

function setText(selector, value) {
    const node = document.querySelector(selector);
    if (node) {
        node.textContent = value;
    }
}

function dashboardMetrics() {
    if (selectedPlatform === "Todos") {
        return [
            ["Receita Total", currencyNoDecimals.format(asNumber(baseKpis.receita)), "▲ 12,4% este mes"],
            ["Lucro Liquido", currencyNoDecimals.format(asNumber(baseKpis.lucro)), "▲ 8,7% este mes"],
            ["Total Corridas", integer.format(asNumber(baseKpis.corridas)), "▲ 5,2% este mes"],
            ["Ticket Medio", currency.format(asNumber(baseKpis.ticket)), "▼ 3,1% este mes"],
            ["KM Rodados", `${integer.format(asNumber(baseKpis.km))} km`, "▲ 7,8% este mes"],
            ["Margem de Lucro", `${decimal.format(asNumber(baseKpis.margem))}%`, "▲ 4,5% este mes"]
        ];
    }

    const row = kpisPorPlataforma.find((item) => platformName(item) === selectedPlatform) || {};
    const receita = asNumber(field(row, ["ReceitaTotal", "Receita", "Total"]));
    const corridas = asNumber(field(row, ["TotalCorridas", "Corridas", "Quantidade"]));
    const costs = total(costRows(), ["Total", "Valor"]);
    const lucro = receita - costs;
    const km = asNumber(field(row, ["TotalKM", "KM", "KMRodados"]));
    const ticket = asNumber(field(row, ["TicketMedio"], corridas ? receita / corridas : 0));
    const margem = receita ? (lucro / receita) * 100 : 0;

    return [
        ["Receita Total", currencyNoDecimals.format(receita), `Filtro ${selectedPlatform}`],
        ["Lucro Liquido", currencyNoDecimals.format(lucro), `Filtro ${selectedPlatform}`],
        ["Total Corridas", integer.format(corridas), `Filtro ${selectedPlatform}`],
        ["Ticket Medio", currency.format(ticket), `Filtro ${selectedPlatform}`],
        ["KM Rodados", `${integer.format(km)} km`, `Filtro ${selectedPlatform}`],
        ["Margem de Lucro", `${decimal.format(margem)}%`, `Filtro ${selectedPlatform}`]
    ];
}

function vehicleMetrics() {
    const rows = vehicleCostRows();
    const receita = total(rows, ["Receita", "Total"]);
    const custo = total(rows, ["CustoTotal", "Total", "Valor"]);
    const lucro = receita - custo;
    const km = total(rows, ["KM", "TotalKM", "KMRodados"]);
    const corridas = total(rows, ["Corridas", "TotalCorridas"]);
    const custoKm = km ? custo / km : 0;

    return [
        ["Custo Total", currencyNoDecimals.format(custo), "Por veiculo"],
        ["Receita", currencyNoDecimals.format(receita), "Veiculos filtrados"],
        ["Lucro", currencyNoDecimals.format(lucro), "Receita - custos"],
        ["Corridas", integer.format(corridas), "Total"],
        ["KM Rodados", `${integer.format(km)} km`, "Total"],
        ["Custo por KM", currency.format(custoKm), "Media"]
    ];
}

function paymentMetrics() {
    const rows = paymentRows();
    const receita = total(rows, ["Receita", "Total"]);
    const quantidade = total(rows, ["Quantidade", "Corridas", "TotalCorridas"]);
    const ticket = quantidade ? receita / quantidade : 0;
    const top = rows[0] || {};
    const topName = paymentName(top) || "-";
    const topValue = asNumber(field(top, ["Receita", "Total"]));
    const topPercent = receita ? (topValue / receita) * 100 : 0;

    return [
        ["Receita Recebida", currencyNoDecimals.format(receita), "Pagamentos"],
        ["Transacoes", integer.format(quantidade), "Total"],
        ["Ticket Medio", currency.format(ticket), "Por pagamento"],
        ["Mais Usado", topName, `${decimal.format(topPercent)}% da receita`],
        ["Maior Receita", currencyNoDecimals.format(topValue), topName],
        ["Formas Ativas", integer.format(rows.length), "Tipos"]
    ];
}

function updateMetricCards() {
    const metricCards = document.getElementById("metricCards");
    const showCards = ["dashboard", "veiculos", "pagamentos"].includes(currentSection);
    metricCards.hidden = !showCards;
    if (!showCards) {
        return;
    }

    const metrics = currentSection === "veiculos"
        ? vehicleMetrics()
        : currentSection === "pagamentos"
            ? paymentMetrics()
            : dashboardMetrics();

    document.querySelectorAll(".kpi-card").forEach((card, index) => {
        const metric = metrics[index] || ["-", "-", ""];
        card.querySelector("span").textContent = metric[0];
        card.querySelector("strong").textContent = metric[1];
        card.querySelector("small").textContent = metric[2];
    });
}

function updateLegend(containerId, rows, labelGetter, valueGetter, colorGetter, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) {
        return;
    }

    const visibleRows = rows.filter((row) => valueGetter(row) > 0);
    const legendRows = visibleRows.length ? visibleRows : rows;
    const sum = total(legendRows, options.totalFields || ["Receita", "Total"]);

    container.innerHTML = legendRows.map((row) => {
        const label = labelGetter(row);
        const value = valueGetter(row);
        const percent = sum ? (value / sum) * 100 : 0;
        const active = label === selectedPlatform ? " active" : "";
        const small = options.money === false ? "" : `<small>${currency.format(value)}</small>`;
        return `
            <li class="${active}" data-legend="${label}">
                <i class="legend-dot" style="background:${colorGetter(label)}"></i>
                <span>${label}</span>
                <strong>${decimal.format(percent)}%</strong>
                ${small}
            </li>
        `;
    }).join("");
}

function updateLineChart() {
    const ctx = document.getElementById("receitaMensal")?.getContext("2d");
    if (!ctx) {
        return;
    }

    const rows = rowsForYear();
    const chartData = {
        labels: rows.map((item) => monthNames[(asNumber(field(item, ["Mes"])) || 1) - 1]),
        datasets: [{
            label: selectedPlatform === "Todos" ? "Receita" : `Receita ${selectedPlatform}`,
            data: rows.map((item) => asNumber(field(item, ["Receita", "Total"]))),
            borderColor: "#ffc400",
            backgroundColor: makeGradient(ctx, "#ffc400"),
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "#ffc400",
            pointBorderWidth: 3,
            pointRadius: 4,
            fill: true,
            tension: .38
        }]
    };

    if (receitaChart) {
        receitaChart.data = chartData;
        receitaChart.update();
        return;
    }

    receitaChart = new Chart(ctx, {
        type: "line",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => currency.format(context.parsed.y) } }
            },
            scales: {
                x: { grid: { color: "rgba(126, 190, 229, .10)" } },
                y: { beginAtZero: true, ticks: { callback: (value) => `${value / 1000}k` } }
            }
        }
    });
}

function updatePlatformChart() {
    const canvas = document.getElementById("plataformaChart");
    if (!canvas) {
        return;
    }

    const rows = platformRows();
    const chartData = {
        labels: rows.map(platformName),
        datasets: [{
            data: rows.map((row) => asNumber(field(row, ["Receita", "Total"]))),
            backgroundColor: rows.map((row) => platformColor(platformName(row))),
            borderWidth: 0,
            hoverOffset: 8
        }]
    };

    if (plataformaChart) {
        plataformaChart.data = chartData;
        plataformaChart.update();
    } else {
        plataformaChart = new Chart(canvas, doughnutConfig(chartData));
    }

    updateLegend(
        "platformLegend",
        rows,
        platformName,
        (row) => asNumber(field(row, ["Receita", "Total"])),
        platformColor
    );
}

function updatePaymentChart() {
    const canvas = document.getElementById("pagamentoChart");
    if (!canvas) {
        return;
    }

    const rows = paymentRows();
    const chartData = {
        labels: rows.map(paymentName),
        datasets: [{
            data: rows.map((row) => asNumber(field(row, ["Receita", "Total"]))),
            backgroundColor: rows.map((row) => paymentColor(paymentName(row))),
            borderWidth: 0,
            hoverOffset: 8
        }]
    };

    if (pagamentoChart) {
        pagamentoChart.data = chartData;
        pagamentoChart.update();
    } else {
        pagamentoChart = new Chart(canvas, doughnutConfig(chartData));
    }

    updateLegend(
        "paymentLegend",
        rows,
        paymentName,
        (row) => asNumber(field(row, ["Receita", "Total"])),
        paymentColor
    );
}

function doughnutConfig(chartData) {
    return {
        type: "doughnut",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "58%",
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: ${currency.format(context.parsed)}`
                    }
                }
            }
        }
    };
}

function updateRidesChart() {
    const canvas = document.getElementById("corridasChart");
    if (!canvas) {
        return;
    }

    const rows = platformRows();
    const chartData = {
        labels: rows.map(platformName),
        datasets: [{
            data: rows.map((item) => asNumber(field(item, ["Corridas", "TotalCorridas", "Quantidade"]))),
            backgroundColor: rows.map((row) => platformColor(platformName(row))),
            borderRadius: 5,
            barThickness: 42
        }]
    };

    if (corridasChart) {
        corridasChart.data = chartData;
        corridasChart.update();
        return;
    }

    corridasChart = new Chart(canvas, {
        type: "bar",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: (value) => `${value / 1000}k`.replace("0k", "0") } }
            }
        }
    });
}

function updateTables() {
    const driversBody = document.getElementById("driversBody");
    if (driversBody) {
        driversBody.innerHTML = driverRows().map((item, index) => {
            const name = field(item, ["Motorista", "NomeMotorista", "Nome"], "Motorista");
            const avaliacao = asNumber(field(item, ["Avaliacao"], 4.98 - (index * 0.02)));
            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>${name}</td>
                    <td>${integer.format(asNumber(field(item, ["Corridas", "TotalCorridas"])))}</td>
                    <td>${currency.format(asNumber(field(item, ["Receita", "Total"])))}</td>
                    <td>${currency.format(asNumber(field(item, ["TicketMedio"], 0)))}</td>
                    <td><span class="stars">★★★★★</span> ${decimal.format(avaliacao)}</td>
                </tr>
            `;
        }).join("");
    }

    const vehiclesBody = document.getElementById("vehiclesBody");
    if (vehiclesBody) {
        vehiclesBody.innerHTML = vehicleRows().map((item, index) => {
            const vehicle = normalizeName(`${field(item, ["Marca"], "")} ${field(item, ["Modelo"], "")}`) || field(item, ["Veiculo"], "Veiculo");
            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>${vehicle}</td>
                    <td>${integer.format(asNumber(field(item, ["Corridas", "TotalCorridas"])))}</td>
                    <td>${currency.format(asNumber(field(item, ["Receita", "Total"])))}</td>
                    <td>${integer.format(asNumber(field(item, ["KMRodados", "TotalKM", "KM"])))} km</td>
                </tr>
            `;
        }).join("");
    }

    const vehicleCostsBody = document.getElementById("vehicleCostsBody");
    if (vehicleCostsBody) {
        vehicleCostsBody.innerHTML = vehicleCostRows().map((item, index) => {
            const vehicle = normalizeName(`${field(item, ["Marca"], "")} ${field(item, ["Modelo"], "")}`) || field(item, ["Veiculo"], "Veiculo");
            const receita = asNumber(field(item, ["Receita", "Total"]));
            const custo = asNumber(field(item, ["CustoTotal", "Total", "Valor"]));
            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>${vehicle}</td>
                    <td>${integer.format(asNumber(field(item, ["Corridas", "TotalCorridas"])))}</td>
                    <td>${currency.format(receita)}</td>
                    <td>${currency.format(custo)}</td>
                    <td>${currency.format(receita - custo)}</td>
                </tr>
            `;
        }).join("");
    }

    const ridesBody = document.getElementById("ridesBody");
    if (ridesBody) {
        ridesBody.innerHTML = latestRideRows().map((item) => {
            const id = asNumber(field(item, ["idCorrida"], 0));
            const hour = String((id * 7) % 24).padStart(2, "0");
            const minute = String((id * 13) % 60).padStart(2, "0");
            const origem = `${field(item, ["Origem"], "")} - ${field(item, ["BairroOrigem"], "")}`;
            const destino = `${field(item, ["Destino"], "")} - ${field(item, ["BairroDestino"], "")}`;
            const date = new Date(field(item, ["Data_Completa"], ""));
            const formattedDate = Number.isNaN(date.getTime()) ? field(item, ["Data_Completa"], "") : date.toLocaleDateString("pt-BR");
            return `
                <tr>
                    <td>${id}</td>
                    <td>${formattedDate}</td>
                    <td>${hour}:${minute}</td>
                    <td><span class="platform-pill">${platformName(item)}</span></td>
                    <td>${origem}</td>
                    <td>${destino}</td>
                    <td>${field(item, ["Motorista"], "")}</td>
                    <td>${currency.format(asNumber(field(item, ["Valor_Corrida", "Receita", "Total"])))}</td>
                </tr>
            `;
        }).join("");
    }
}

function initCostsChart() {
    const canvas = document.getElementById("custosChart");
    if (!canvas) {
        return;
    }

    costsChart = new Chart(canvas, {
        type: "bar",
        data: costChartData(),
        options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => currency.format(context.parsed.x) } }
            },
            scales: {
                x: { beginAtZero: true, ticks: { callback: (value) => `${value / 1000}k` } },
                y: { grid: { display: false } }
            }
        }
    });
}

function costChartData() {
    const rows = costRows();
    return {
        labels: rows.map((item) => field(item, ["Descricao", "Categoria"], "")),
        datasets: [{
            data: rows.map((item) => asNumber(field(item, ["Total", "Valor"]))),
            backgroundColor: "#ff1744",
            borderRadius: 5,
            barThickness: 14
        }]
    };
}

function updateCostsChart() {
    if (!costsChart) {
        return;
    }

    costsChart.data = costChartData();
    costsChart.update();
}

function refreshDashboard() {
    updateMetricCards();
    updateLineChart();
    updatePlatformChart();
    updatePaymentChart();
    updateCostsChart();
    updateRidesChart();
    updateTables();
}

function setPlatform(name) {
    selectedPlatform = name;
    document.querySelectorAll("[data-platform]").forEach((button) => {
        button.classList.toggle("active", button.dataset.platform === name);
    });
    refreshDashboard();
}

function flashPanel(id) {
    const panel = document.getElementById(id);
    if (!panel) {
        return;
    }

    panel.scrollIntoView({ behavior: "smooth", block: "center" });
    panel.classList.remove("flash");
    requestAnimationFrame(() => panel.classList.add("flash"));
}

function setSection(target) {
    currentSection = target;
    document.body.dataset.section = target;
    window.scrollTo(0, 0);
    document.querySelectorAll("[data-section]").forEach((panel) => {
        const sections = panel.dataset.section.split(" ");
        panel.hidden = target !== "dashboard" && !sections.includes(target);
    });

    if (target === "configuracoes") {
        document.querySelectorAll("[data-section]").forEach((panel) => {
            panel.hidden = true;
        });
    }
    updateMetricCards();
}

function showToast(message) {
    let toast = document.querySelector(".toast");
    if (!toast) {
        toast = document.createElement("div");
        toast.className = "toast";
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add("show");
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => toast.classList.remove("show"), 2200);
}

function bindEvents() {
    document.querySelectorAll("[data-platform]").forEach((button) => {
        button.addEventListener("click", () => setPlatform(button.dataset.platform));
    });

    document.getElementById("platformLegend")?.addEventListener("click", (event) => {
        const item = event.target.closest("[data-legend]");
        if (item) {
            setPlatform(item.dataset.legend);
        }
    });

    document.getElementById("paymentLegend")?.addEventListener("click", (event) => {
        const item = event.target.closest("[data-legend]");
        if (item) {
            flashPanel("pagamentos");
        }
    });

    document.querySelectorAll(".menu a[data-target]").forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            document.querySelectorAll(".menu a").forEach((item) => item.classList.remove("active"));
            link.classList.add("active");
            if (link.dataset.target === "configuracoes") {
                const panel = document.getElementById("configuracoes");
                panel?.classList.add("open");
                document.getElementById("filterToggle")?.setAttribute("aria-expanded", "true");
            }
            setSection(link.dataset.target);
        });
    });

    document.querySelectorAll("[data-action='focus-table']").forEach((button) => {
        button.addEventListener("click", () => flashPanel(button.dataset.target));
    });

    document.getElementById("menuToggle")?.addEventListener("click", () => {
        document.body.classList.toggle("nav-collapsed");
    });

    document.getElementById("themeButton")?.addEventListener("click", () => {
        document.body.classList.toggle("light-mode");
        showToast(document.body.classList.contains("light-mode") ? "Visual alternativo ativado." : "Visual escuro ativado.");
    });

    document.getElementById("notificationButton")?.addEventListener("click", (event) => {
        event.currentTarget.querySelector("span")?.remove();
        showToast("Notificacoes visualizadas.");
    });

    document.getElementById("filterToggle")?.addEventListener("click", (event) => {
        const panel = document.getElementById("configuracoes");
        const open = !panel.classList.contains("open");
        panel.classList.toggle("open", open);
        event.currentTarget.setAttribute("aria-expanded", String(open));
    });

    ["yearSelect", "filterYear"].forEach((id) => {
        document.getElementById(id)?.addEventListener("change", (event) => {
            selectedYear = Number(event.target.value);
            document.getElementById("yearSelect").value = String(selectedYear);
            document.getElementById("filterYear").value = String(selectedYear);
            updateLineChart();
        });
    });

    document.getElementById("filterSort")?.addEventListener("change", (event) => {
        sortMode = event.target.value;
        refreshDashboard();
    });

    document.getElementById("resetFilters")?.addEventListener("click", () => {
        setPlatform("Todos");
        sortMode = "value";
        document.getElementById("filterSort").value = "value";
        selectedYear = latestYear();
        document.getElementById("yearSelect").value = String(selectedYear);
        document.getElementById("filterYear").value = String(selectedYear);
        refreshDashboard();
    });

    document.querySelector("[data-action='logout']")?.addEventListener("click", (event) => {
        event.preventDefault();
        showToast("Acao de sair pronta para ligar ao login.");
    });
}

initCostsChart();
refreshDashboard();
bindEvents();
const initialSection = window.location.hash.replace("#", "");
if (initialSection) {
    const targetLink = document.querySelector(`.menu a[data-target="${initialSection}"]`);
    if (targetLink) {
        document.querySelectorAll(".menu a").forEach((item) => item.classList.remove("active"));
        targetLink.classList.add("active");
        setSection(initialSection);
        window.scrollTo(0, 0);
        window.setTimeout(() => window.scrollTo(0, 0), 50);
    }
} else {
    setSection("dashboard");
}
