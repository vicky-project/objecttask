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