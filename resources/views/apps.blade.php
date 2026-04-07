<div class="container-custom">
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

      <div id="back-to-categories" onclick="taskModule.showCategories()" style="display: none;">
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

<style>
  /* Gaya mengikuti tema Telegram */
  .container-custom {
    background: var(--tg-bg, #ffffff);
    color: var(--tg-text, #000000);
    border-radius: 12px;
    padding: 16px;
    }
    .nav-tabs {
    border-bottom-color: var(--tg-border, #e5e5ea);
    }
    .nav-tabs .nav-link {
    color: var(--tg-secondary-text, #8e8e93);
    background: transparent;
    border: none;
    padding: 10px 16px;
    }
    .nav-tabs .nav-link.active {
    color: var(--tg-link, #007aff);
    border-bottom: 2px solid var(--tg-link, #007aff);
    background: transparent;
    }
    .search-box {
    width: 100%;
    padding: 8px 12px;
    border-radius: 20px;
    border: 1px solid var(--tg-border, #e5e5ea);
    background: var(--tg-button-bg, #e9e9ef);
    color: var(--tg-text, #000000);
    font-size: 14px;
    }
    .category-item, .content-item, .task-item {
    background: var(--tg-card-bg, #ffffff);
    border: 1px solid var(--tg-border, #e5e5ea);
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
    }
    .category-item:active, .content-item:active, .task-item:active {
    background: var(--tg-button-active, #d1d1d6);
    }
    .category-name {
    font-weight: 600;
    font-size: 16px;
    }
    .category-code {
    font-size: 12px;
    color: var(--tg-secondary-text, #8e8e93);
    }
    .content-code, .task-code {
    font-family: monospace;
    font-size: 14px;
    background: var(--tg-button-bg, #e9e9ef);
    padding: 4px 8px;
    border-radius: 8px;
    display: inline-block;
    }
    .content-desc, .task-desc {
    font-size: 14px;
    margin-bottom: 4px;
    }
    #back-to-categories {
    cursor: pointer;
    margin-bottom: 12px;
    color: var(--tg-link, #007aff);
    font-size: 14px;
    }
    .loading-container {
    text-align: center;
    padding: 40px;
    color: var(--tg-secondary-text, #8e8e93);
    }
    .badge.bg-primary {
    background-color: var(--tg-link, #007aff) !important;
    }
    </style>

    <script>
    // Namespace untuk menghindari konflik global
    window.taskModule = window.taskModule || {};

    (function() {
    // Ambil token dari localStorage (dari parent SPA)
    function getAuthToken() {
    return localStorage.getItem('telegram_token');
    }

    // Fungsi toast: gunakan parent jika ada, fallback alert
    function showToast(message, type = 'info') {
    if (typeof window.showToast === 'function') {
    window.showToast(message);
    } else {
    alert(message);
    }
    }

    // Copy to clipboard dengan fallback
    function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(() => {
    showToast(`Kode ${text} disalin`, 'success');
    }).catch(() => fallbackCopy(text));
    } else {
    fallbackCopy(text);
    }
    }

    function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    document.body.appendChild(textarea);
    textarea.select();
    try {
    document.execCommand('copy');
    showToast(`Kode ${text} disalin`, 'success');
    } catch (err) {
    showToast('Gagal menyalin', 'danger');
    }
    document.body.removeChild(textarea);
    }

    function showLoading(containerId, message = 'Memuat data...') {
    const container = document.getElementById(containerId);
    if (container) {
    container.innerHTML = `
    <div class="loading-container">
    <div class="spinner-border loading-spinner" role="status">
    <span class="visually-hidden">Loading...</span>
    </div>
    <div>${message}</div>
    </div>
    `;
    }
    }

    // Data
    let categories = [];
    let tasks = [];
    let currentCategory = null;

    // Fetch dengan autentikasi token
    async function fetchWithAuth(url) {
    const token = getAuthToken();
    const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
    };
    if (token) {
    headers['Authorization'] = `Bearer ${token}`;
    }
    const response = await fetch(url, { headers });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
    }

    // Render daftar kategori
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
    <div class="category-item" onclick="taskModule.showContents('${cat.id}', '${cat.code}')">
    <div>
    <div class="category-name">${escapeHtml(cat.name)}</div>
    <div class="category-code">${escapeHtml(cat.code)}</div>
    </div>
    <i class="bi bi-chevron-right" style="color: var(--tg-secondary-text, #999);"></i>
    </div>
    `;
    });
    container.innerHTML = html;
    }

    // Render daftar task
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
    <div class="task-item" onclick="taskModule.copyToClipboard('${escapeHtml(task.code)}')">
    <div>
    <div class="task-desc">${escapeHtml(task.description)}</div>
    <div class="task-code">${escapeHtml(task.code)}</div>
    </div>
    <i class="bi bi-copy" style="color: var(--tg-link, #007aff);"></i>
    </div>
    `;
    });
    container.innerHTML = html;
    }

    // Tampilkan konten dari kategori yang dipilih
    window.taskModule.showContents = async function(id, code) {
    currentCategory = categories.find(c => c.code === code);
    if (!currentCategory) return;

    document.getElementById('categories-container').style.display = 'none';
    document.getElementById('back-to-categories').style.display = 'block';
    document.getElementById('contents-container').style.display = 'block';

    showLoading('contents-container', 'Memuat contents...');

    try {
    const data = await fetchWithAuth(`/api/data-object/categories/${id}/contents`);
    renderContents(data);
    } catch (error) {
    document.getElementById('contents-container').innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat contents</div>';
    showToast('Gagal memuat konten: ' + error.message, 'danger');
    }
    };

    function renderContents(contents) {
    const container = document.getElementById('contents-container');
    if (contents.length === 0) {
    container.innerHTML = '<div class="text-center p-4 text-muted">Tidak ada konten</div>';
    return;
    }

    let html = '';
    contents.forEach(item => {
    html += `
    <div class="content-item" onclick="taskModule.copyToClipboard('${escapeHtml(item.code)}')">
    <div>
    <div class="content-desc">${escapeHtml(item.description)}</div>
    <div class="content-code">${escapeHtml(item.code)}</div>
    </div>
    <i class="bi bi-copy" style="color: var(--tg-link, #007aff);"></i>
    </div>
    `;
    });
    container.innerHTML = html;
    }

    // Kembali ke daftar kategori
    window.taskModule.showCategories = function() {
    document.getElementById('categories-container').style.display = 'block';
    document.getElementById('back-to-categories').style.display = 'none';
    document.getElementById('contents-container').style.display = 'none';
    renderCategories(categories);
    };

    window.taskModule.copyToClipboard = copyToClipboard;

    function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
    });
    }

    // Inisialisasi: fetch data dari API
    showLoading('categories-container', 'Memuat object codes...');
    showLoading('tasks-container', 'Memuat task codes...');

    Promise.all([
    fetchWithAuth('/api/data-object/categories'),
    fetchWithAuth('/api/data-object/task-codes')
    ]).then(([cats, tsk]) => {
    categories = cats;
    tasks = tsk;
    document.getElementById('object-count-badge').textContent = categories.length;
    document.getElementById('task-count-badge').textContent = tasks.length;
    renderCategories(categories);
    renderTasks(tasks);
    }).catch(error => {
    document.getElementById('categories-container').innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat object codes</div>';
    document.getElementById('tasks-container').innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat task codes</div>';
    showToast('Gagal memuat data: ' + error.message, 'danger');
    });

    // Event listener untuk pencarian
    document.getElementById('object-search').addEventListener('input', function() {
    if (document.getElementById('categories-container').style.display !== 'none') {
    renderCategories(categories);
    }
    });
    document.getElementById('task-search').addEventListener('input', function() {
    renderTasks(tasks);
    });

    // Inisialisasi Bootstrap tabs (jika Bootstrap JS tersedia)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
    const triggerTabList = [].slice.call(document.querySelectorAll('#dataTab button'));
    triggerTabList.forEach(triggerEl => {
    new bootstrap.Tab(triggerEl);
    });
    } else {
    // Fallback manual jika Bootstrap JS tidak ada
    document.querySelectorAll('#dataTab button').forEach(btn => {
    btn.addEventListener('click', function(e) {
    e.preventDefault();
    const targetId = this.getAttribute('data-bs-target');
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
    document.querySelector(targetId).classList.add('show', 'active');
    document.querySelectorAll('#dataTab button').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    });
    });
    }
    })();
    </script>