    </div>
  </main>
</div>
<script>
// Confirm dialogs
document.querySelectorAll('form[data-confirm]').forEach(f => {
  f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
});
// Close sidebar when clicking backdrop or a nav link on mobile
document.querySelectorAll('[data-close-sidebar]').forEach(el => {
  el.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
});
document.querySelectorAll('.admin-sidebar nav a').forEach(a => {
  a.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
});
// ESC closes sidebar
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.body.classList.remove('sidebar-open'); });

// Live table filter — attach to any [data-filter-input] pointing at [data-filter-target]
document.querySelectorAll('[data-filter-input]').forEach(inp => {
  const targetSel = inp.getAttribute('data-filter-target');
  inp.addEventListener('input', () => {
    const q = inp.value.trim().toLowerCase();
    document.querySelectorAll(targetSel).forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
});

// Drag & drop file zones — visual feedback
document.querySelectorAll('.drop-zone').forEach(zone => {
  const input = zone.querySelector('input[type=file]');
  if (!input) return;
  const showFiles = () => {
    const list = Array.from(input.files || []).map(f => f.name).join(', ');
    const info = zone.querySelector('.drop-info');
    if (info) info.textContent = list || info.getAttribute('data-empty') || 'No files chosen';
  };
  ['dragenter','dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('is-drag'); }));
  ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('is-drag'); }));
  zone.addEventListener('drop', e => { input.files = e.dataTransfer.files; showFiles(); });
  input.addEventListener('change', showFiles);
});

// Image preview when picking a file
document.querySelectorAll('input[type=file][data-preview]').forEach(inp => {
  const target = document.querySelector(inp.getAttribute('data-preview'));
  if (!target) return;
  inp.addEventListener('change', () => {
    const f = inp.files && inp.files[0];
    if (!f) return;
    const img = target.querySelector('img') || target.appendChild(document.createElement('img'));
    img.src = URL.createObjectURL(f);
    target.classList.add('has-preview');
  });
});
</script>
</body>
</html>
