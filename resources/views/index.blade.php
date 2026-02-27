@extends('core::layouts.main')

@section('title', 'Data Catalog')


@section('content')
<div class="main-container">
  <div class="container-custom">
    <div class="page-header">
      <h2>Data Catalog</h2>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="dataTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="objects-tab" data-bs-toggle="tab" data-bs-target="#objects" type="button" role="tab" aria-controls="objects" aria-selected="true">
          <i class="bi bi-grid"></i> Objects
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab" aria-controls="tasks" aria-selected="false">
          <i class="bi bi-check2-square"></i> Task Codes
        </button>
      </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content" id="dataTabContent">
      <!-- Objects Tab -->
      <div class="tab-pane fade show active" id="objects" role="tabpanel" aria-labelledby="objects-tab">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="badge bg-primary" id="object-count-badge">0</span>
          <input type="text" class="search-box" id="object-search" placeholder="Cari kategori...">
        </div>

        <div id="back-to-categories" onclick="showCategories()">
          <i class="bi bi-arrow-left"></i> Kembali ke Kategori
        </div>

        <div id="categories-container"></div>
        <div id="contents-container" style="display: none;"></div>
      </div>

      <!-- Tasks Tab -->
      <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="badge bg-primary" id="task-count-badge">0</span>
          <input type="text" class="search-box" id="task-search" placeholder="Cari kode atau deskripsi...">
        </div>
        <div id="tasks-container"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Data
  let categories = [];
  let tasks = [];
  let currentCategory = null;

  // Inisialisasi: fetch data
  Promise.all([
  fetch('{{ secure_url(config("app.url")) }}/api/data-object/categories', {
  headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
  }
  }).then(res => res.json()),
  fetch('{{ secure_url(config("app.url")) }}/api/data-object/task-codes').then(res => res.json())
  ]).then(([cats, tsk]) => {
  categories = cats;
  tasks = tsk;
  document.getElementById('object-count-badge').textContent = categories.length;
  document.getElementById('task-count-badge').textContent = tasks.length;
  renderCategories(categories);
  renderTasks(tasks);
  }).catch(error => {
  showToast('Gagal memuat data: ' + error.message, 'danger');
  });

  // ================== OBJECTS ==================
  function renderCategories(cats) {
    const container = document.getElementById('categories-container');
    const searchTerm = document.getElementById('object-search').value.toLowerCase();

    let filtered = cats;
    if (searchTerm) {
      filtered = cats.filter(cat =>
      cat.name.toLowerCase().includes(searchTerm) ||
      cat.code.toLowerCase().includes(searchTerm)
      );
    }

    if (filtered.length === 0) {
      container.innerHTML = '<div class="text-center p-4 text-muted">Tidak ada kategori</div>';
      return;
    }

    let html = '';
    filtered.forEach(cat => {
    html += `
    <div class="category-item" onclick="showContents('${cat.id}', '${cat.code}')">
    <div>
    <div class="category-name">${cat.name}</div>
    <div class="category-code">${cat.code}</div>
    </div>
    <i class="bi bi-chevron-right" style="color: var(--tg-theme-hint-color, #999);"></i>
    </div>
    `;
    });
    container.innerHTML = html;
  }

  function showContents(id, code) {
    currentCategory = categories.find(c => c.code === code);
    if (!currentCategory) return;

    document.getElementById('categories-container').style.display = 'none';
    document.getElementById('back-to-categories').style.display = 'block';
    document.getElementById('contents-container').style.display = 'block';

    fetch(`{{ secure_url("api/data-object/categories") }}/${id}/contents`)
    .then(response => response.json())
    .then(data => {
    renderContents(data);
    })
    .catch(error => {
    showToast('Gagal memuat konten', 'danger');
    });
  }

  function renderContents(contents) {
    const container = document.getElementById('contents-container');
    if (contents.length === 0) {
      container.innerHTML = '<div class="text-center p-4 text-muted">Tidak ada konten</div>';
      return;
    }

    let html = '';
    contents.forEach(item => {
    html += `
    <div class="content-item" onclick="copyToClipboard('${item.code}')">
    <div class="content-code">${item.code}</div>
    <div class="content-desc">${item.description}</div>
    </div>
    `;
    });
    container.innerHTML = html;
  }

  function showCategories() {
    document.getElementById('categories-container').style.display = 'block';
    document.getElementById('back-to-categories').style.display = 'none';
    document.getElementById('contents-container').style.display = 'none';
    renderCategories(categories);
  }

  document.getElementById('object-search').addEventListener('input', function() {
  if (document.getElementById('categories-container').style.display !== 'none') {
  renderCategories(categories);
  }
  });

  // ================== TASKS ==================
  function renderTasks(taskList) {
    const container = document.getElementById('tasks-container');
    const searchTerm = document.getElementById('task-search').value.toLowerCase();

    let filtered = taskList;
    if (searchTerm) {
      filtered = taskList.filter(task =>
      task.code.toLowerCase().includes(searchTerm) ||
      task.description.toLowerCase().includes(searchTerm)
      );
    }

    if (filtered.length === 0) {
      container.innerHTML = '<div class="text-center p-4 text-muted">Tidak ada task code</div>';
      return;
    }

    let html = '';
    filtered.forEach(task => {
    html += `
    <div class="task-item" onclick="copyToClipboard('${task.code}')">
    <div class="task-desc">${task.description}</div>
    <div class="task-code">${task.code}</div>
    </div>
    `;
    });
    container.innerHTML = html;
  }

  document.getElementById('task-search').addEventListener('input', function() {
  renderTasks(tasks);
  });

  // ================== COPY TO CLIPBOARD ==================
  function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
    showToast(`Kode ${text} disalin`, 'success');
    }).catch(err => {
    showToast('Gagal menyalin', 'danger');
    });
  }

  // Override goBack jika diperlukan, tetapi tidak ada di layout, jadi kita tidak perlu.
  // Kita hanya menggunakan tombol "Kembali ke Kategori" internal.
</script>
@endpush

@push('styles')
<style>
  /* Gaya tambahan untuk tab dan elemen lainnya */
  .nav-tabs {
    border-bottom: 1px solid var(--tg-theme-hint-color, #e9ecef);
    margin-bottom: 20px;
    }
    .nav-tabs .nav-link {
    color: var(--tg-theme-hint-color, #999);
    border: none;
    font-weight: 500;
    background-color: transparent;
    }
    .nav-tabs .nav-link.active {
    color: var(--tg-theme-button-color, #40a7e3);
    background-color: transparent;
    border-bottom: 2px solid var(--tg-theme-button-color, #40a7e3);
    }
    .category-item {
    background-color: var(--tg-theme-bg-color, #fff);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 10px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.1s ease;
    border: 1px solid var(--tg-theme-hint-color, #ddd);
    }
    .category-item:active {
    transform: scale(0.98);
    }
    .category-item .category-name {
    font-weight: 500;
    color: var(--tg-theme-text-color, #000);
    }
    .category-item .category-code {
    font-size: 0.8rem;
    color: var(--tg-theme-hint-color, #999);
    }
    .content-item {
    background-color: var(--tg-theme-bg-color, #fff);
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 6px;
    border-left: 4px solid var(--tg-theme-button-color, #40a7e3);
    cursor: pointer;
    transition: background-color 0.2s;
    border: 1px solid var(--tg-theme-hint-color, #ddd);
    }
    .content-item:active {
    background-color: var(--tg-theme-secondary-bg-color, #f0f0f0);
    }
    .content-item .content-code {
    font-weight: 600;
    color: var(--tg-theme-button-color, #40a7e3);
    }
    .content-item .content-desc {
    color: var(--tg-theme-text-color, #000);
    }
    .task-item {
    background-color: var(--tg-theme-bg-color, #fff);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.2s;
    border: 1px solid var(--tg-theme-hint-color, #ddd);
    }
    .task-item:active {
    background-color: var(--tg-theme-secondary-bg-color, #f0f0f0);
    }
    .task-code {
    font-weight: 600;
    color: var(--tg-theme-button-color, #40a7e3);
    background-color: rgba(64, 167, 227, 0.1);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
    }
    .task-desc {
    color: var(--tg-theme-text-color, #000);
    font-weight: 500;
    }
    .search-box {
    background-color: var(--tg-theme-bg-color, #fff);
    border: 1px solid var(--tg-theme-hint-color, #ddd);
    border-radius: 20px;
    padding: 10px 15px;
    color: var(--tg-theme-text-color, #000);
    width: 100%;
    }
    .search-box::placeholder {
    color: var(--tg-theme-hint-color, #999);
    }
    .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    }
    .page-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--tg-theme-text-color, #000);
    }
    #back-to-categories {
    display: none;
    margin-bottom: 20px;
    cursor: pointer;
    color: var(--tg-theme-button-color, #40a7e3);
    font-weight: 500;
    }
    #back-to-categories i {
    font-size: 1.2rem;
    margin-right: 5px;
    }
    .badge.bg-primary {
    background-color: var(--tg-theme-button-color, #40a7e3) !important;
    color: var(--tg-theme-button-text-color, #fff) !important;
    }
    .tab-pane {
    padding: 0;
    }
    .main-container {
    background-color: var(--tg-theme-bg-color, #fff);
    min-height: 100vh;
    }
    .container-custom {
    max-width: 500px;
    margin: 0 auto;
    padding: 1rem;
    }
    </style>
    @endpush