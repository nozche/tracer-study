const questions = [
  {
    id: "q1",
    text: "Status pekerjaan saat ini",
    type: "choice",
    chart: "bar",
    options: [
      "Bekerja penuh waktu",
      "Bekerja paruh waktu",
      "Melanjutkan studi",
      "Mencari pekerjaan",
      "Wirausaha"
    ]
  },
  {
    id: "q2",
    text: "Kesesuaian pekerjaan dengan bidang studi",
    type: "scale",
    chart: "bar",
    min: 1,
    max: 5
  },
  {
    id: "q3",
    text: "Rentang pendapatan bulanan",
    type: "choice",
    chart: "pie",
    options: [
      "< 5 juta",
      "5 - 8 juta",
      "8 - 12 juta",
      "> 12 juta"
    ]
  },
  {
    id: "q4",
    text: "Seberapa bermanfaat kurikulum dalam pekerjaan",
    type: "scale",
    chart: "bar",
    min: 1,
    max: 5
  }
];

const responses = [
  {
    angkatan: "2020",
    fakultas: "Teknik",
    periode: "2023",
    answers: { q1: "Bekerja penuh waktu", q2: 4, q3: "8 - 12 juta", q4: 5 }
  },
  {
    angkatan: "2020",
    fakultas: "Teknik",
    periode: "2023",
    answers: { q1: "Bekerja penuh waktu", q2: 5, q3: "8 - 12 juta", q4: 5 }
  },
  {
    angkatan: "2020",
    fakultas: "Ekonomi",
    periode: "2023",
    answers: { q1: "Wirausaha", q2: 4, q3: "5 - 8 juta", q4: 4 }
  },
  {
    angkatan: "2021",
    fakultas: "Teknik",
    periode: "2023",
    answers: { q1: "Mencari pekerjaan", q2: 3, q3: "< 5 juta", q4: 3 }
  },
  {
    angkatan: "2021",
    fakultas: "Ekonomi",
    periode: "2023",
    answers: { q1: "Melanjutkan studi", q2: 4, q3: "5 - 8 juta", q4: 4 }
  },
  {
    angkatan: "2022",
    fakultas: "Hukum",
    periode: "2024",
    answers: { q1: "Bekerja paruh waktu", q2: 3, q3: "< 5 juta", q4: 3 }
  },
  {
    angkatan: "2022",
    fakultas: "Teknik",
    periode: "2024",
    answers: { q1: "Bekerja penuh waktu", q2: 4, q3: "5 - 8 juta", q4: 4 }
  },
  {
    angkatan: "2022",
    fakultas: "Hukum",
    periode: "2024",
    answers: { q1: "Bekerja penuh waktu", q2: 2, q3: "5 - 8 juta", q4: 3 }
  },
  {
    angkatan: "2022",
    fakultas: "Ekonomi",
    periode: "2024",
    answers: { q1: "Wirausaha", q2: 5, q3: "8 - 12 juta", q4: 5 }
  },
  {
    angkatan: "2021",
    fakultas: "Hukum",
    periode: "2023",
    answers: { q1: "Bekerja penuh waktu", q2: 3, q3: "< 5 juta", q4: 3 }
  },
  {
    angkatan: "2020",
    fakultas: "Teknik",
    periode: "2024",
    answers: { q1: "Melanjutkan studi", q2: 4, q3: "5 - 8 juta", q4: 4 }
  },
  {
    angkatan: "2021",
    fakultas: "Ekonomi",
    periode: "2024",
    answers: { q1: "Bekerja paruh waktu", q2: 2, q3: "< 5 juta", q4: 3 }
  }
];

const charts = {};

const filterSelects = {
  angkatan: document.getElementById("filter-angkatan"),
  fakultas: document.getElementById("filter-fakultas"),
  periode: document.getElementById("filter-periode")
};

const summaryEl = document.getElementById("summary");
const questionsContainer = document.getElementById("questions-container");
const overallCountEl = document.getElementById("overall-response-count");
const resetButton = document.getElementById("reset-filters");

document.addEventListener("DOMContentLoaded", () => {
  populateFilters();
  renderQuestionCards();
  bindEvents();
  refreshDashboard();
});

function populateFilters() {
  populateSelect(filterSelects.angkatan, uniqueValues(responses.map((r) => r.angkatan)).sort());
  populateSelect(filterSelects.fakultas, uniqueValues(responses.map((r) => r.fakultas)).sort());
  populateSelect(filterSelects.periode, uniqueValues(responses.map((r) => r.periode)).sort());
}

function populateSelect(select, values) {
  select.innerHTML = '<option value="">Semua</option>' + values.map((value) => `<option value="${value}">${value}</option>`).join("");
}

function uniqueValues(array) {
  return [...new Set(array)];
}

function bindEvents() {
  Object.values(filterSelects).forEach((select) => {
    select.addEventListener("change", refreshDashboard);
  });

  resetButton.addEventListener("click", () => {
    Object.values(filterSelects).forEach((select) => (select.value = ""));
    refreshDashboard();
  });
}

function renderQuestionCards() {
  questionsContainer.innerHTML = "";

  questions.forEach((question) => {
    const card = document.createElement("article");
    card.className = "card";
    card.id = `card-${question.id}`;

    card.innerHTML = `
      <div class="card-header">
        <div>
          <p class="eyebrow">Pertanyaan</p>
          <h3>${question.text}</h3>
        </div>
        <span class="badge">${question.type === "scale" ? "Skala" : "Pilihan"}</span>
      </div>
      <p class="metric" id="metric-${question.id}"></p>
      <div class="chart-wrapper"><canvas id="chart-${question.id}" aria-label="Grafik ${question.text}" role="img"></canvas></div>
      <table class="table" aria-live="polite">
        <thead id="table-head-${question.id}"></thead>
        <tbody id="table-body-${question.id}"></tbody>
      </table>
    `;

    questionsContainer.appendChild(card);
  });
}

function refreshDashboard() {
  const filtered = applyFilters();
  updateOverallCount(filtered.length);
  renderSummary(filtered);
  questions.forEach((question) => renderQuestion(question, filtered));
}

function getActiveFilters() {
  return {
    angkatan: filterSelects.angkatan.value,
    fakultas: filterSelects.fakultas.value,
    periode: filterSelects.periode.value
  };
}

function applyFilters() {
  const { angkatan, fakultas, periode } = getActiveFilters();

  return responses.filter((response) => {
    const matchAngkatan = angkatan ? response.angkatan === angkatan : true;
    const matchFakultas = fakultas ? response.fakultas === fakultas : true;
    const matchPeriode = periode ? response.periode === periode : true;
    return matchAngkatan && matchFakultas && matchPeriode;
  });
}

function renderSummary(data) {
  const total = data.length;
  const angkatan = uniqueValues(data.map((r) => r.angkatan)).length;
  const fakultas = uniqueValues(data.map((r) => r.fakultas)).length;
  const periode = uniqueValues(data.map((r) => r.periode)).length;

  const summaryItems = [
    { label: "Respon terfilter", value: total },
    { label: "Jumlah angkatan", value: angkatan || 0 },
    { label: "Jumlah fakultas", value: fakultas || 0 },
    { label: "Jumlah periode", value: periode || 0 }
  ];

  summaryEl.innerHTML = summaryItems
    .map(
      (item) => `
      <div class="summary-card">
        <strong>${item.value}</strong>
        <p>${item.label}</p>
      </div>
    `
    )
    .join("");
}

function renderQuestion(question, data) {
  const metricEl = document.getElementById(`metric-${question.id}`);
  const tableHead = document.getElementById(`table-head-${question.id}`);
  const tableBody = document.getElementById(`table-body-${question.id}`);
  const chartCanvas = document.getElementById(`chart-${question.id}`);

  const aggregate = aggregateQuestion(question, data);
  const totalResponses = aggregate.total;

  if (!totalResponses) {
    metricEl.textContent = "Belum ada data untuk kombinasi filter ini.";
    tableHead.innerHTML = "";
    tableBody.innerHTML = `<tr><td colspan="3" class="empty-state">Tidak ada data</td></tr>`;
    destroyChart(question.id);
    return;
  }

  if (question.type === "choice") {
    tableHead.innerHTML = `
      <tr>
        <th>Opsi</th>
        <th>Jumlah</th>
        <th>Persentase</th>
      </tr>
    `;
    tableBody.innerHTML = renderChoiceRows(aggregate);
    metricEl.textContent = `${aggregate.answered} jawaban dari ${totalResponses} responden.`;
    renderChart(question, aggregate, chartCanvas);
  } else {
    tableHead.innerHTML = `
      <tr>
        <th>Skala</th>
        <th>Jumlah</th>
        <th>Persentase</th>
      </tr>
    `;
    tableBody.innerHTML = renderScaleRows(question, aggregate);
    const averageText = aggregate.answered ? aggregate.average.toFixed(1) : "-";
    metricEl.textContent = `Rata-rata skala: ${averageText} dari ${aggregate.answered} jawaban (total ${totalResponses} responden).`;
    renderChart(question, aggregate, chartCanvas);
  }
}

function aggregateQuestion(question, data) {
  if (question.type === "choice") {
    const counts = Object.fromEntries(question.options.map((option) => [option, 0]));
    let answered = 0;

    data.forEach((response) => {
      const answer = response.answers[question.id];
      if (answer) {
        counts[answer] = (counts[answer] || 0) + 1;
        answered += 1;
      }
    });

    return { counts, total: data.length, answered };
  }

  const counts = {};
  for (let scale = question.min; scale <= question.max; scale += 1) {
    counts[scale] = 0;
  }

  let answered = 0;
  let sum = 0;

  data.forEach((response) => {
    const value = Number(response.answers[question.id]);
    if (!Number.isNaN(value)) {
      counts[value] = (counts[value] || 0) + 1;
      answered += 1;
      sum += value;
    }
  });

  return {
    counts,
    total: data.length,
    answered,
    average: answered ? sum / answered : 0
  };
}

function renderChoiceRows(aggregate) {
  const total = aggregate.answered || aggregate.total || 1;

  return Object.entries(aggregate.counts)
    .map(([option, count]) => {
      const percent = formatPercent(count, total);
      return `
        <tr>
          <td>${option}</td>
          <td>${count}</td>
          <td>${percent}</td>
        </tr>
      `;
    })
    .join("");
}

function renderScaleRows(question, aggregate) {
  const total = aggregate.answered || aggregate.total || 1;
  const rows = [];

  for (let scale = question.min; scale <= question.max; scale += 1) {
    const count = aggregate.counts[scale] || 0;
    const percent = formatPercent(count, total);
    rows.push(`
      <tr>
        <td>${scale}</td>
        <td>${count}</td>
        <td>${percent}</td>
      </tr>
    `);
  }

  return rows.join("");
}

function renderChart(question, aggregate, canvas) {
  const labels = question.type === "choice" ? Object.keys(aggregate.counts) : Object.keys(aggregate.counts).map((scale) => `Skala ${scale}`);
  const data = Object.values(aggregate.counts);
  const backgroundColors = generatePalette(data.length, question.chart === "pie");

  destroyChart(question.id);

  charts[question.id] = new Chart(canvas, {
    type: question.chart === "pie" ? "pie" : "bar",
    data: {
      labels,
      datasets: [
        {
          label: "Jumlah",
          data,
          backgroundColor: backgroundColors,
          borderRadius: 8,
          borderWidth: 1,
          borderColor: "#e5e7eb"
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: question.chart === "pie",
          position: "bottom"
        },
        tooltip: {
          callbacks: {
            label: (ctx) => `${ctx.label}: ${ctx.parsed} responden`
          }
        }
      },
      scales: question.chart === "pie" ? undefined : {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        },
        x: {
          ticks: {
            maxRotation: 45,
            minRotation: 0
          }
        }
      }
    }
  });
}

function destroyChart(id) {
  if (charts[id]) {
    charts[id].destroy();
    delete charts[id];
  }
}

function generatePalette(length, softer = false) {
  const palette = [
    "#2563eb",
    "#10b981",
    "#f59e0b",
    "#ef4444",
    "#8b5cf6",
    "#14b8a6",
    "#f97316",
    "#0ea5e9"
  ];

  const colors = new Array(length).fill(null).map((_, index) => palette[index % palette.length]);

  if (softer) {
    return colors.map((color) => `${color}cc`);
  }

  return colors.map((color) => `${color}`);
}

function updateOverallCount(count) {
  const total = responses.length;
  overallCountEl.textContent = `${count} dari ${total} responden`; 
}

function formatPercent(count, total) {
  if (!total) return "0%";
  return `${((count / total) * 100).toFixed(1)}%`;
}
