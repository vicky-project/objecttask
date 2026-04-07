<div class="container-custom">
  <div class="page-header">
    <a href="{{ route('telegram.home') }}" class="home-button disabled" title="Kembali ke Beranda">
      <i class="bi bi-house-door fs-1"></i>
    </a>
    <h2>Object & Task Code</h2>
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

<script>
  // ================== COPY TO CLIPBOARD ==================
  function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed'; // Hindari scroll ke bawah
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
      const successful = document.execCommand('copy');
      if (successful) {
        showToast ? showToast(`Kode ${text} disalin`, 'success'): alert(`Kode ${text} disalin`);
      } else {
        throw new Error('Fallback copy gagal');
      }
    } catch (err) {
      showToast ? showToast('Gagal menyalin', 'danger'): alert('Gagal menyalin');
    }
    document.body.removeChild(textarea);
  }

  function copyToClipboard(text) {
    if (!navigator.clipboard) {
      fallbackCopy(text);
      return;
    } else {
      navigator.clipboard.writeText(text).then(() => {
      showToast(`Kode ${text} disalin`, 'success') || alert(`Kode ${text} disalin`);
      }).catch(err => {
      fallbackCopy(text);
      });
    }
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

  showLoading('categories-container', 'Memuat object codes...');
  showLoading('tasks-container', 'Memuat task codes...');

  // Inisialisasi: fetch data
  Promise.all([
  fetch('{{ secure_url(config("app.url")) }}/api/data-object/categories', {
  headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
  }
  }).then(res => res.json()),
  fetch('{{ secure_url(config("app.url")) }}/api/data-object/task-codes',{
  headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
  }
  }).then(res => res.json())
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

    showLoading('contents-container', 'Memuat contents...');

    fetch(`{{ secure_url(config("app.url")) }}/api/data-object/categories/${id}/contents`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
    renderContents(data);
    })
    .catch(error => {
    document.getElementById('contents-container').innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat contents</div>';
    showToast('Gagal memuat konten: ' + error.message, 'danger');
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

</script>