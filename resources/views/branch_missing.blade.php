<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Select Branch</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-xl shadow p-8 max-w-lg w-full text-center">
      <h1 class="text-2xl font-semibold text-gray-900 mb-2">Branch Required</h1>
      <p class="text-gray-600 mb-6">We need a branch to open this page. If you've visited a branch before, we'll try to auto-redirect. Otherwise, pick one from Queueing Info.</p>
      <div class="space-y-4">
        <a href="{{ url('/queueing-info') }}" class="inline-block px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Open Queueing Info</a>
        <div class="text-sm text-gray-500">Or paste a branch slug below</div>
        <div class="flex gap-2 justify-center">
          <input id="branchInput" type="text" class="px-3 py-2 border rounded w-56" placeholder="e.g. olongapo" />
          <button id="goBtn" class="px-3 py-2 rounded bg-gray-800 text-white hover:bg-black">Go</button>
        </div>
      </div>
    </div>
    <script>
      (function(){
        const target = "{{ $target ?? 'printer' }}";
        function redirectTo(slug){ if (!slug) return; window.location.href = `/branch/${encodeURIComponent(slug)}/${target}`; }
        try {
          // Try lastBranchSlug saved by pages
          const saved = localStorage.getItem('lastBranchSlug');
          if (saved && typeof saved === 'string' && saved.length) { redirectTo(saved); return; }
          // Try queueing_info's saved object
          const obj = JSON.parse(localStorage.getItem('queue_branch') || 'null');
          if (obj && obj.slug) { redirectTo(obj.slug); return; }
        } catch(e) {}
        // Allow manual entry
        const input = document.getElementById('branchInput');
        const btn = document.getElementById('goBtn');
        if (btn) btn.addEventListener('click', ()=>redirectTo((input?.value||'').trim()));
        if (input) input.addEventListener('keydown', (ev)=>{ if (ev.key === 'Enter') redirectTo((input?.value||'').trim()); });
        // Also support ?branch=slug for quick deep links
        try {
          const qp = new URLSearchParams(window.location.search);
          if (qp.has('branch')) redirectTo(qp.get('branch'));
        } catch(e) {}
      })();
    </script>
  </body>
</html>
