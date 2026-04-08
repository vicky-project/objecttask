<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Task Module</title>
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      background: var(--tg-theme-bg-color, #ffffff);
      color: var(--tg-theme-text-color, #000000);
      padding: 16px;
      }
      .container-custom {
      max-width: 600px;
      margin: 0 auto;
      }
      .nav-tabs {
      display: flex;
      gap: 16px;
      border-bottom: 1px solid var(--tg-theme-hint-color, #e5e5ea);
      margin-bottom: 20px;
      }
      .nav-tabs button {
      background: none;
      border: none;
      padding: 10px 0;
      font-size: 16px;
      cursor: pointer;
      color: var(--tg-theme-hint-color, #8e8e93);
      }
      .nav-tabs button.active {
      color: var(--tg-theme-link-color, #007aff);
      border-bottom: 2px solid var(--tg-theme-link-color, #007aff);
      }
      .search-box {
      width: 100%;
      padding: 8px 12px;
      border-radius: 20px;
      border: 1px solid var(--tg-theme-hint-color, #e5e5ea);
      background: var(--tg-theme-secondary-bg-color, #e9e9ef);
      color: var(--tg-theme-text-color, #000);
      margin-bottom: 16px;
      }
      .category-item, .content-item, .task-item {
      background: var(--tg-theme-secondary-bg-color, #f5f5f5);
      border-radius: 12px;
      padding: 12px 16px;
      margin-bottom: 8px;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      }
      .category-name { font-weight: 600; }
      .category-code { font-size: 12px; color: var(--tg-theme-hint-color); }
      .content-code, .task-code {
      font-family: monospace;
      background: var(--tg-theme-bg-color, #fff);
      padding: 4px 8px;
      border-radius: 8px;
      }
      .back-link {
      cursor: pointer;
      color: var(--tg-theme-link-color, #007aff);
      margin-bottom: 12px;
      display: inline-block;
      }
      .loading {
      text-align: center;
      padding: 40px;
      color: var(--tg-theme-hint-color);
      }
      .badge {
      background: var(--tg-theme-link-color, #007aff);
      color: white;
      padding: 4px 8px;
      border-radius: 20px;
      font-size: 12px;
      }
      .flex-between {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      }
      </style>
      </head>
      <body>
      <div class="container-custom">
      <!-- Tab Navigation -->
      <div class="nav-tabs">
      <button id="tab-objects" class="active">Objects</button>
      <button id="tab-tasks">Task Codes</button>
      </div>

      <!-- Objects Panel -->
      <div id="objects-panel">
      <div class="flex-between">
      <span class="badge" id="object-count">0</span>
      <input type="text" id="search-object" class="search-box" placeholder="Cari kategori...">
      </div>
      <div id="categories-container"></div>
      <div id="contents-container" style="display:none;">
      <div id="back-to-categories" class="back-link">← Kembali ke Kategori</div>
      <div id="contents-list"></div>
      </div>
      </div>

      <!-- Tasks Panel (hidden initially) -->
      <div id="tasks-panel" style="display:none;">
      <div class="flex-between">
      <span class="badge" id="task-count">0</span>
      <input type="text" id="search-task" class="search-box" placeholder="Cari kode atau deskripsi...">
      </div>
      <div id="tasks-container"></div>
      </div>
      </div>

      <script>
      // Helper untuk toast (fallback)
      function showToast(msg) {
      if (window.Telegram?.WebApp?.showAlert) window.Telegram.WebApp.showAlert(msg);
      else alert(msg);
      }

      // Ambil token dari localStorage (disimpan oleh parent SPA)
      function getToken() {
      return localStorage.getItem('telegram_token');
      }

      async function fetchWithAuth(url) {
      const token = getToken();
      const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
      if (token) headers['Authorization'] = `Bearer ${token}`;
      const res = await fetch(url, { headers });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
      }

      // State
      let categories = [];
      let tasks = [];
      let currentCategory = null;
      let activeTab = 'objects';

      // DOM elements
      const objectsPanel = document.getElementById('objects-panel');
      const tasksPanel = document.getElementById('tasks-panel');
      const tabObjects = document.getElementById('tab-objects');
      const tabTasks = document.getElementById('tab-tasks');
      const categoriesContainer = document.getElementById('categories-container');
      const contentsContainerDiv = document.getElementById('contents-container');
      const contentsList = document.getElementById('contents-list');
      const backBtn = document.getElementById('back-to-categories');
      const tasksContainer = document.getElementById('tasks-container');
      const objectCountSpan = document.getElementById('object-count');
      const taskCountSpan = document.getElementById('task-count');
      const searchObjectInput = document.getElementById('search-object');
      const searchTaskInput = document.getElementById('search-task');

      // Render categories
      function renderCategories() {
      const searchTerm = searchObjectInput.value.toLowerCase();
      let filtered = categories;
      if (searchTerm) {
      filtered = categories.filter(c => c.name.toLowerCase().includes(searchTerm) || c.code.toLowerCase().includes(searchTerm));
      }
      objectCountSpan.textContent = filtered.length;
      if (filtered.length === 0) {
      categoriesContainer.innerHTML = '<div class="loading">Tidak ada kategori</div>';
      return;
      }
      let html = '';
      filtered.forEach(cat => {
      html += `
      <div class="category-item" data-id="${cat.id}" data-code="${escapeHtml(cat.code)}">
      <div>
      <div class="category-name">${escapeHtml(cat.name)}</div>
      <div class="category-code">${escapeHtml(cat.code)}</div>
      </div>
      <i class="bi bi-chevron-right"></i>
      </div>
      `;
      });
      categoriesContainer.innerHTML = html;
      // attach event
      document.querySelectorAll('.category-item').forEach(el => {
      el.addEventListener('click', () => {
      const id = el.dataset.id;
      const code = el.dataset.code;
      showContents(id, code);
      });
      });
      }

      async function showContents(categoryId, categoryCode) {
      currentCategory = categories.find(c => c.id == categoryId);
      if (!currentCategory) return;
      categoriesContainer.style.display = 'none';
      contentsContainerDiv.style.display = 'block';
      contentsList.innerHTML = '<div class="loading">Memuat konten...</div>';
      try {
      const data = await fetchWithAuth(`{{ config("app.url") }}/api/data-object/categories/${categoryId}/contents`);
      renderContents(data);
      } catch (err) {
      contentsList.innerHTML = `<div class="loading">Gagal memuat konten: ${err.message}</div>`;
      }
      }

      function renderContents(contents) {
      if (contents.length === 0) {
      contentsList.innerHTML = '<div class="loading">Tidak ada konten</div>';
      return;
      }
      let html = '';
      contents.forEach(item => {
      html += `
      <div class="content-item" data-code="${escapeHtml(item.code)}">
      <div>
      <div class="content-desc">${escapeHtml(item.description)}</div>
      <div class="content-code">${escapeHtml(item.code)}</div>
      </div>
      <i class="bi bi-copy"></i>
      </div>
      `;
      });
      contentsList.innerHTML = html;
      // attach copy event
      document.querySelectorAll('.content-item').forEach(el => {
      el.addEventListener('click', (e) => {
      e.stopPropagation();
      const code = el.dataset.code;
      copyToClipboard(code);
      });
      });
      }

      function backToCategories() {
      categoriesContainer.style.display = 'block';
      contentsContainerDiv.style.display = 'none';
      renderCategories(); // refresh search
      }
      backBtn.addEventListener('click', backToCategories);

      // Render tasks
      function renderTasks() {
      const searchTerm = searchTaskInput.value.toLowerCase();
      let filtered = tasks;
      if (searchTerm) {
      filtered = tasks.filter(t => t.code.toLowerCase().includes(searchTerm) || t.description.toLowerCase().includes(searchTerm));
      }
      taskCountSpan.textContent = filtered.length;
      if (filtered.length === 0) {
      tasksContainer.innerHTML = '<div class="loading">Tidak ada task code</div>';
      return;
      }
      let html = '';
      filtered.forEach(task => {
      html += `
      <div class="task-item" data-code="${escapeHtml(task.code)}">
      <div>
      <div class="task-desc">${escapeHtml(task.description)}</div>
      <div class="task-code">${escapeHtml(task.code)}</div>
      </div>
      <i class="bi bi-copy"></i>
      </div>
      `;
      });
      tasksContainer.innerHTML = html;
      document.querySelectorAll('.task-item').forEach(el => {
      el.addEventListener('click', () => {
      const code = el.dataset.code;
      copyToClipboard(code);
      });
      });
      }

      function copyToClipboard(text) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(() => showToast(`Kode ${text} disalin`));
      } else {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
      showToast(`Kode ${text} disalin`);
      }
      }

      function escapeHtml(str) {
      if (!str) return '';
      return str.replace(/[&<>]/g, function(m) {
      if (m === '&') return '&amp;';
      if (m === '<') return '&lt;';
      if (m === '>') return '&gt;';
      return m;
      });
      }

      // Tab switching
      tabObjects.addEventListener('click', () => {
      activeTab = 'objects';
      tabObjects.classList.add('active');
      tabTasks.classList.remove('active');
      objectsPanel.style.display = 'block';
      tasksPanel.style.display = 'none';
      if (categories.length > 0) renderCategories();
      });
      tabTasks.addEventListener('click', () => {
      activeTab = 'tasks';
      tabTasks.classList.add('active');
      tabObjects.classList.remove('active');
      objectsPanel.style.display = 'none';
      tasksPanel.style.display = 'block';
      if (tasks.length > 0) renderTasks();
      });

      // Search events
      searchObjectInput.addEventListener('input', () => renderCategories());
      searchTaskInput.addEventListener('input', () => renderTasks());

      // Load data
      async function loadData() {
      try {
      const [cats, tsk] = await Promise.all([
      fetchWithAuth('/api/data-object/categories'),
      fetchWithAuth('/api/data-object/task-codes')
      ]);
      categories = cats;
      tasks = tsk;
      renderCategories();
      renderTasks();
      objectCountSpan.textContent = categories.length;
      taskCountSpan.textContent = tasks.length;
      } catch (err) {
      console.error(err);
      categoriesContainer.innerHTML = `<div class="loading">Gagal memuat data: ${err.message}</div>`;
      tasksContainer.innerHTML = `<div class="loading">Gagal memuat data: ${err.message}</div>`;
      }
      }

      loadData();
      </script>
      </body>
      </html>