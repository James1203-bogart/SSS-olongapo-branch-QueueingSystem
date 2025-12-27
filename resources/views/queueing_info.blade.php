<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Queueing Info</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <button onclick="location.href='{{ url('/') }}'" class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to Selection
      </button>
      <div class="px-4 py-2 rounded-lg bg-indigo-600 text-white">Queueing Info</div>
    </div>

    <!-- Intro -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 mb-2">Pick your Branch</h1>
      <p class="text-gray-600">Choose a branch to get its designated links (Display Board, Caller, With Thermal, Without Thermal). The branch is appended as a query parameter so each device can be configured per site.</p>
    </div>

    <!-- Branch Picker -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Search Branch</label>
          <input id="branchSearch" type="text" class="w-full px-4 py-2 border rounded-lg" placeholder="Type to filter…" />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Select Branch</label>
          <select id="branchSelect" class="w-full px-4 py-2 border rounded-lg"></select>
        </div>
      </div>
    </div>

    <!-- Links -->
    <div id="linksWrap" class="bg-white rounded-xl shadow p-6 hidden">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Links for <span id="branchName" class="text-indigo-600"></span></h2>
        <span id="branchSlug" class="text-gray-500 text-sm mono"></span>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="border rounded-lg p-4">
          <p class="text-gray-700 mb-2">Display Board</p>
          <div class="flex items-center gap-2">
            <input id="urlDisplay" class="flex-1 px-3 py-2 border rounded mono" readonly>
            <button data-copy="urlDisplay" class="copy-btn px-3 py-2 bg-gray-800 text-white rounded hover:bg-black">Copy</button>
            <a id="openDisplay" target="_blank" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" href="#">Open</a>
          </div>
        </div>
        <div class="border rounded-lg p-4">
          <p class="text-gray-700 mb-2">Caller</p>
          <div class="flex items-center gap-2">
            <input id="urlCaller" class="flex-1 px-3 py-2 border rounded mono" readonly>
            <button data-copy="urlCaller" class="copy-btn px-3 py-2 bg-gray-800 text-white rounded hover:bg-black">Copy</button>
            <a id="openCaller" target="_blank" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" href="#">Open</a>
          </div>
        </div>
        <div class="border rounded-lg p-4">
          <p class="text-gray-700 mb-2">With Thermal</p>
          <div class="flex items-center gap-2">
            <input id="urlPrinter" class="flex-1 px-3 py-2 border rounded mono" readonly>
            <button data-copy="urlPrinter" class="copy-btn px-3 py-2 bg-gray-800 text-white rounded hover:bg-black">Copy</button>
            <a id="openPrinter" target="_blank" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" href="#">Open</a>
          </div>
        </div>
        <div class="border rounded-lg p-4">
          <p class="text-gray-700 mb-2">Without Thermal</p>
          <div class="flex items-center gap-2">
            <input id="urlScreen" class="flex-1 px-3 py-2 border rounded mono" readonly>
            <button data-copy="urlScreen" class="copy-btn px-3 py-2 bg-gray-800 text-white rounded hover:bg-black">Copy</button>
            <a id="openScreen" target="_blank" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" href="#">Open</a>
          </div>
        </div>
      </div>

      <p class="text-sm text-gray-500 mt-4">Tip: After opening a link on a device, bookmark it so the device is permanently tied to this branch. We can also make other pages read the <span class="mono">branch</span> query for display headers on request.</p>
    </div>
  </div>

  <script>
    const BRANCHES = [
      "Batasan Hills","Congressional","Cubao","Deparo","Diliman","Eastwood","Fairview","Kalookan","Malabon","Navotas","Novaliches","Paso de Blas","San Francisco del Monte","Valenzuela","Antipolo","New Panaderos","Mandaluyong – Shaw","Marikina","Marikina – Malanday","Ortigas","Pasig – Pioneer","Pasig – Rosario","Pasig – Mabini","San Juan","Tanay","Binondo","Legarda","Manila","Pasay – CCP Complex","Pasay – Taft","Sta. Mesa","Tondo","Welcome","Alabang – Muntinlupa","Alabang – Zapote","Bicutan – Sun Valley","Las Piñas","Makati – Chino Roces","Makati – Gil Puyat","Makati – Guadalupe","Makati – JP Rizal","Parañaque","Parañaque – Tambo","Taguig","Taguig – Gate 3","Agoo","Baguio","Bangued","Bontoc","Candon","La Trinidad","La Union","Laoag","Vigan","Cauayan","Ilagan","Santiago","Solano","Tuguegarao","Alaminos","Balanga","Baler","Cabanatuan","Camiling","Dagupan","Iba","Mariveles","San Carlos (Pangasinan)","San Jose (Nueva Ecija)","Tarlac","Urdaneta","Angeles","Baliuag","Bocaue","Dau","Malolos","Meycauayan","Olongapo","Pampanga","San Jose del Monte","Sta. Maria","Bacoor","Biñan","Calamba","Carmona","Dasmariñas","Infanta","Lucena","Rosario (Cavite)","San Pablo","San Pedro","Santa Rosa","Sta. Cruz","Tagaytay","Batangas","Boac","Calapan","Lemery","Lipa","Odiongan","Puerto Princesa","San Jose (Occidental Mindoro)","Daet","Iriga","Legazpi","Masbate","Naga","Sorsogon","Tabaco","Virac","Bogo","Cebu","Cebu – NRA","Danao","Lapu-Lapu","Mandaue","Tagbilaran","Talisay","Toledo","Calbayog","Catbalogan","Maasin","Ormoc","Tacloban","Bacolod","Bacolod East","Bago","Bais","Dumaguete","Kabankalan","Sagay","San Carlos (Negros Occidental)","Victorias","Antique","Iloilo – Central","Iloilo – Molo","Kalibo","Roxas","Butuan","Cagayan de Oro","CDO – Lapasan","Gingoog","Iligan","Oroquieta","Ozamis","San Francisco (Agusan)","Surigao","Tandag","Valencia","Bislig","Davao","Davao – San Pedro","Digos","Mati","Panabo","Tagum","Toril","Cotabato","General Santos","Kidapawan","Koronadal","Tacurong","Dipolog","Ipil","Pagadian","Zamboanga"
    ];

    const select = document.getElementById('branchSelect');
    const search = document.getElementById('branchSearch');
    const linksWrap = document.getElementById('linksWrap');
    const branchName = document.getElementById('branchName');
    const branchSlug = document.getElementById('branchSlug');

    const inputDisplay = document.getElementById('urlDisplay');
    const inputCaller = document.getElementById('urlCaller');
    const inputPrinter = document.getElementById('urlPrinter');
    const inputScreen = document.getElementById('urlScreen');

    const openDisplay = document.getElementById('openDisplay');
    const openCaller = document.getElementById('openCaller');
    const openPrinter = document.getElementById('openPrinter');
    const openScreen = document.getElementById('openScreen');

    function slugify(str) {
      return str
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^\w\s-]/g, '')
        .trim()
        .replace(/[\s_]+/g, '-')
        .toLowerCase();
    }

    function fullUrl(path) {
      const base = window.location.origin;
      return new URL(path, base).toString();
    }

    function renderOptions(list) {
      select.innerHTML = '<option value="">-- Select a branch --</option>';
      list.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name; opt.textContent = name; select.appendChild(opt);
      });
    }

    function updateLinks(name) {
      if (!name) { linksWrap.classList.add('hidden'); return; }
      const slug = slugify(name);
      branchName.textContent = name;
      branchSlug.textContent = `/branch/${slug}`;
      inputDisplay.value = fullUrl(`/branch/${slug}/display`);
      inputCaller.value = fullUrl(`/branch/${slug}/caller`);
      inputPrinter.value = fullUrl(`/branch/${slug}/printer`);
      inputScreen.value = fullUrl(`/branch/${slug}/screen`);
      openDisplay.href = inputDisplay.value;
      openCaller.href = inputCaller.value;
      openPrinter.href = inputPrinter.value;
      openScreen.href = inputScreen.value;
      linksWrap.classList.remove('hidden');
      try { localStorage.setItem('queue_branch', JSON.stringify({ name, slug })); } catch(e) {}
    }

    // Copy buttons
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.copy-btn');
      if (!btn) return;
      const id = btn.getAttribute('data-copy');
      const el = document.getElementById(id);
      if (el) {
        el.select(); el.setSelectionRange(0, 99999);
        document.execCommand('copy');
        btn.textContent = 'Copied';
        setTimeout(() => btn.textContent = 'Copy', 1200);
      }
    });

    // Filter as you type
    search.addEventListener('input', () => {
      const q = search.value.trim().toLowerCase();
      const filtered = BRANCHES.filter(n => n.toLowerCase().includes(q));
      renderOptions(filtered);
    });

    select.addEventListener('change', () => updateLinks(select.value));

    // Init
    renderOptions(BRANCHES);
    try {
      const saved = JSON.parse(localStorage.getItem('queue_branch') || 'null');
      if (saved && saved.name && BRANCHES.includes(saved.name)) {
        select.value = saved.name;
        updateLinks(saved.name);
      }
    } catch(e) {}
  </script>
</body>
</html>
