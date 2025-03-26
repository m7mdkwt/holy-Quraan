<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>القرآن الكريم - تفسير وتشغيل</title>
  <link rel="icon" type="image/png" href="favicon.png" />

  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
  <style>
    body { font-family: 'Tajawal', sans-serif; }

    .ayah {
      display: inline;
      font-size: 1.3rem;
      margin: 0 4px;
      padding: 4px 6px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .ayah.highlight { background-color: #dbeafe; }
    .ayah:hover { background-color: #fcd34d; }
    .dark .ayah.highlight { background-color: #1e40af; color: #fff; }
    .dark .ayah:hover { background-color: #facc15; color: #000; }

    .quran-border {
      border: 8px solid transparent;
      border-image: url('https://i.imgur.com/k8bqIpK.png') 30 stretch;
      padding: 20px;
      background-color: #ffffff;
      border-radius: 8px;
    }

    .dark .quran-border { background-color: #1e1e1e; }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 50;
    }

    .modal-content {
      background: #fff;
      padding: 1.5rem;
      border-radius: 0.5rem;
      max-width: 500px;
      width: 90%;
    }

    .dark .modal-content {
      background: #1f2937;
      color: white;
    }

    #tafsirContent {
      font-size: 1.2rem;
      line-height: 2;
    }

    /* Animation for page flip */
    @keyframes flipPage {
      0%   { transform: rotateY(0deg); opacity: 1; }
      50%  { transform: rotateY(90deg); opacity: 0.3; }
      100% { transform: rotateY(0deg); opacity: 1; }
    }

    .page-flip-anim {
      animation: flipPage 0.6s ease-in-out;
    }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

<div class="max-w-4xl mx-auto p-4">
  <h1 class="text-3xl font-bold mb-4 text-center text-emerald-700 dark:text-emerald-300">📖 القرآن الكريم</h1>

  <div class="flex flex-wrap gap-4 mb-4 items-center justify-between">
    <select id="surahSelect" class="p-2 border rounded text-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
      <option value="" disabled selected>اختر سورة</option>
    </select>
    <select id="reciterSelect" class="p-2 border rounded text-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
      <option value="ar.alafasy">العفاسي</option>
      <option value="ar.hudhaify">الحذيفي</option>
      <option value="ar.abdurrahmaansudais">السديس</option>
      <option value="ar.shaatree">الشاطري</option>
      <option value="ar.abdulsamad">عبدالباسط (ترتيل)</option>
      <option value="ar.mahermuaiqly">المعيقلي</option>
    </select>
    <button id="togglePlayBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">▶️ تشغيل</button>
    <button id="darkToggle" class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 dark:text-white rounded">🌙 الوضع الليلي</button>
  </div>

  <div id="surahInfo" class="mb-4 text-center text-xl font-semibold"></div>

  <div class="quran-border mb-4">
    <div id="ayahContainer" class="text-xl text-justify leading-loose"></div>
  </div>

  <div class="mt-4 flex justify-between items-center">
    <button id="prevPageBtn" class="px-4 py-1 bg-gray-300 dark:bg-gray-700 dark:text-white rounded">◀ السابق</button>
    <span id="pageInfo" class="text-sm text-gray-600 dark:text-gray-300"></span>
    <button id="nextPageBtn" class="px-4 py-1 bg-gray-300 dark:bg-gray-700 dark:text-white rounded">التالي ▶</button>
  </div>
</div>

<!-- نافذة التفسير -->
<div id="modal" class="modal hidden">
  <div class="modal-content">
    <h2 class="text-xl font-bold mb-2">📝 التفسير</h2>
    <div id="tafsirContent"></div>
    <div class="text-right mt-4">
      <button onclick="closeModal()" class="px-3 py-1 bg-red-600 text-white rounded">إغلاق</button>
    </div>
  </div>
</div>

<!-- تنبيه التفسير -->
<div id="tafsirHint" class="fixed top-5 right-5 bg-yellow-200 text-yellow-900 px-4 py-2 rounded shadow hidden z-50 text-sm">
  ℹ️ اضغط على أي آية لعرض التفسير
</div>

<!-- أصوات محلية -->
<audio id="openTafsirAudio" src="audio/open-button.mp3" preload="auto"></audio>
<audio id="closeTafsirAudio" src="audio/button.mp3" preload="auto"></audio>
<audio id="pageFlipAudio" src="audio/turnpage.mp3" preload="auto"></audio>

<!-- الفوتر -->
<footer class="mt-10 py-6 text-center bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded">
  <p class="text-sm">© <span id="year"></span> المصحف الإلكتروني - برمجة: محمد باسم.</p>
</footer>

<!-- سكربت -->
<script>
const surahSelect = document.getElementById('surahSelect');
const reciterSelect = document.getElementById('reciterSelect');
const ayahContainer = document.getElementById('ayahContainer');
const surahInfo = document.getElementById('surahInfo');
const togglePlayBtn = document.getElementById('togglePlayBtn');
const darkToggle = document.getElementById('darkToggle');
const modal = document.getElementById('modal');
const tafsirContent = document.getElementById('tafsirContent');
const prevPageBtn = document.getElementById('prevPageBtn');
const nextPageBtn = document.getElementById('nextPageBtn');
const pageInfo = document.getElementById('pageInfo');
const tafsirHint = document.getElementById('tafsirHint');

const openTafsirAudio = document.getElementById('openTafsirAudio');
const closeTafsirAudio = document.getElementById('closeTafsirAudio');
const pageFlipAudio = document.getElementById('pageFlipAudio');

let allAyahs = [], tafsirData = [], audioElements = [];
let currentAudioIndex = 0, playingAll = false;
let currentPage = 0, ayahsPerPage = 10;
let selectedReciter = reciterSelect.value;

fetch('https://api.alquran.cloud/v1/surah')
  .then(res => res.json())
  .then(data => {
    data.data.forEach(surah => {
      const option = document.createElement('option');
      option.value = surah.number;
      option.textContent = `${surah.number}. ${surah.englishName} (${surah.name})`;
      surahSelect.appendChild(option);
    });
  });

reciterSelect.addEventListener('change', async () => {
  selectedReciter = reciterSelect.value;
  if (surahSelect.value) {
    await loadSurah(surahSelect.value, true);
  }
});

surahSelect.addEventListener('change', async e => {
  await loadSurah(e.target.value);
  tafsirHint.classList.remove('hidden');
  setTimeout(() => tafsirHint.classList.add('hidden'), 4000);
});

async function loadSurah(surahNum, keepPosition = false) {
  const [ayahsRes, tafsirRes] = await Promise.all([
    fetch(`https://api.alquran.cloud/v1/surah/${surahNum}/${selectedReciter}`),
    fetch(`https://quranenc.com/api/v1/translation/sura/arabic_moyassar/${surahNum}`)
  ]);

  const ayahData = await ayahsRes.json();
  const tafsirJson = await tafsirRes.json();

  surahInfo.textContent = `${ayahData.data.englishName} - ${ayahData.data.name}`;
  allAyahs = ayahData.data.ayahs;
  tafsirData = tafsirJson.result;

  if (!keepPosition) {
    currentPage = 0;
    currentAudioIndex = 0;
  }

  renderPage();
}

function renderPage() {
  // تطبيق أنيميشن عند كل صفحة جديدة
  ayahContainer.classList.remove('page-flip-anim');
  void ayahContainer.offsetWidth;
  ayahContainer.classList.add('page-flip-anim');

  ayahContainer.innerHTML = '';
  audioElements = [];

  const start = currentPage * ayahsPerPage;
  const pageAyahs = allAyahs.slice(start, start + ayahsPerPage);

  const paragraph = document.createElement('div');
  pageAyahs.forEach((ayah, index) => {
    const span = document.createElement('span');
    span.className = 'ayah';
    span.title = '📘 اضغط لعرض التفسير';
    span.innerHTML = `(${ayah.numberInSurah}) ${ayah.text}`;
    span.onclick = () => {
      const tafsir = tafsirData[start + index]?.translation || 'لا يوجد تفسير.';
      tafsirContent.innerHTML = `<strong>الآية (${ayah.numberInSurah}):</strong><br>${tafsir}`;
      modal.classList.remove('hidden');
      openTafsirAudio.currentTime = 0;
      openTafsirAudio.play();
    };

    const audio = document.createElement('audio');
    audio.src = ayah.audio;
    audio.className = 'hidden';
    audioElements.push(audio);

    paragraph.appendChild(span);
    paragraph.appendChild(audio);
  });

  ayahContainer.appendChild(paragraph);
  togglePlayBtn.textContent = '▶️ تشغيل';
  playingAll = false;

  pageInfo.textContent = `صفحة ${currentPage + 1} من ${Math.ceil(allAyahs.length / ayahsPerPage)}`;
  prevPageBtn.disabled = currentPage === 0;
  nextPageBtn.disabled = (currentPage + 1) * ayahsPerPage >= allAyahs.length;
}

togglePlayBtn.addEventListener('click', () => {
  if (!audioElements.length) return;
  const currentAudio = audioElements[currentAudioIndex];
  if (playingAll) {
    currentAudio.pause();
    playingAll = false;
    togglePlayBtn.textContent = '▶️ تشغيل';
  } else {
    playingAll = true;
    togglePlayBtn.textContent = '⏸️ إيقاف';
    playNext();
  }
});

function playNext() {
  if (currentAudioIndex >= audioElements.length) {
    playingAll = false;
    togglePlayBtn.textContent = '▶️ تشغيل';
    return;
  }

  const audio = audioElements[currentAudioIndex];
  highlightAyah(currentAudioIndex);
  audio.play();
  audio.onended = () => {
    currentAudioIndex++;
    if (playingAll) playNext();
  };
}

function highlightAyah(index) {
  const ayahs = document.querySelectorAll('.ayah');
  ayahs.forEach(a => a.classList.remove('highlight'));
  if (ayahs[index]) ayahs[index].classList.add('highlight');
}

prevPageBtn.addEventListener('click', () => {
  if (currentPage > 0) {
    currentPage--;
    renderPage();
    pageFlipAudio.currentTime = 0;
    pageFlipAudio.play();
  }
});

nextPageBtn.addEventListener('click', () => {
  const maxPage = Math.ceil(allAyahs.length / ayahsPerPage) - 1;
  if (currentPage < maxPage) {
    currentPage++;
    renderPage();
    pageFlipAudio.currentTime = 0;
    pageFlipAudio.play();
  }
});

function closeModal() {
  closeTafsirAudio.currentTime = 0;
  closeTafsirAudio.play();
  modal.classList.add('hidden');
}

document.getElementById('year').textContent = new Date().getFullYear();

const root = document.documentElement;
if (localStorage.getItem('theme') === 'dark') root.classList.add('dark');
darkToggle.addEventListener('click', () => {
  root.classList.toggle('dark');
  localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
});
</script>

</body>
</html>
