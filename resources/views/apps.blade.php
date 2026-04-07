<div id="task-module-root" class="container-custom">
  <div class="text-center p-4" id="task-loading">
    Memuat data...
  </div>
</div>

<script>
  (function() {
  const root = document.getElementById('task-module-root');
  if (!root) return;

  async function load() {
  const token = localStorage.getItem('telegram_token');
  if (!token) {
  root.innerHTML = '<div class="alert alert-danger">Token tidak ditemukan. Silakan kembali ke halaman utama.</div>';
  return;
  }

  try {
  const [categoriesRes, tasksRes] = await Promise.all([
  fetch('/api/data-object/categories', { headers: { 'Authorization': `Bearer ${token}` } }),
  fetch('/api/data-object/task-codes', { headers: { 'Authorization': `Bearer ${token}` } })
  ]);

  if (!categoriesRes.ok || !tasksRes.ok) throw new Error('Gagal mengambil data');

  const categories = await categoriesRes.json();
  const tasks = await tasksRes.json();

  // Render sederhana
  let html = `<h5>Kategori (${categories.length})</h5><ul>`;
  categories.forEach(c => html += `<li>${c.name} (${c.code})</li>`);
  html += `</ul><h5>Task Codes (${tasks.length})</h5><ul>`;
  tasks.forEach(t => html += `<li>${t.description} - <strong>${t.code}</strong></li>`);
  html += `</ul>`;
  root.innerHTML = html;
  } catch (err) {
  root.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
  }
  }

  load();
  })();
</script>