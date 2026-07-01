    </div>
  </main>
</div>
<script>
async function api(action, data = {}) {
  const form = new URLSearchParams();
  form.append('action', action);
  for (const k in data) form.append(k, data[k]);
  const r = await fetch('api_handler.php', { method:'POST', body: form, credentials:'same-origin' });
  return r.json();
}
function toast(msg, type='info') {
  const t = document.createElement('div');
  t.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-xl text-sm font-medium shadow-2xl ' +
    (type==='success' ? 'bg-green-900/90 text-green-200 border border-green-700' :
     type==='error'   ? 'bg-red-900/90 text-red-200 border border-red-700' :
                        'bg-indigo-900/90 text-indigo-200 border border-indigo-700');
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}
</script>
</body>
</html>
