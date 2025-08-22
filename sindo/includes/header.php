<?php 
$current_page = basename($_SERVER['PHP_SELF']);

// ====== PROFILE CHECK ======
$profile_complete = true;
$profile_message = '';
$missing_fields = [];

if (isLoggedIn()) {
    $user = getUserData($pdo, $_SESSION['user_id']);
    $required_fields = [
        'nim' => 'NIM',
        'fakultas' => 'Fakultas',
        'prodi' => 'Program Studi',
        'semester' => 'Semester'
    ];
    foreach ($required_fields as $field => $label) {
        if (empty($user[$field])) $missing_fields[] = $label;
    }
    if (!empty($missing_fields)) {
        $profile_complete = false;
        $profile_message = 'Lengkapi data profil Anda: ' . implode(', ', $missing_fields);
    }
}

// ====== MUSIC SCAN ======
$musicWebPath = "/sindo/assets/music/"; // <-- ubah jika perlu
$musicFsPath  = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $musicWebPath;
$tracks = [];
$allowed = ['mp3','wav','ogg','m4a','aac'];

if (is_dir($musicFsPath)) {
    foreach (scandir($musicFsPath) as $f) {
        if ($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        $rawTitle = preg_replace('/\.[^.]+$/', '', $f);
        $pretty = trim(preg_replace('/[_-]+/',' ', $rawTitle));
        $pretty = preg_replace('/\s{2,}/',' ', $pretty);

        $tracks[] = [
            'file'  => $f,
            'title' => $pretty,
            'name'  => $rawTitle
        ];
    }
    // urutkan alfabetis berdasarkan title
    usort($tracks, fn($a,$b)=> strcasecmp($a['title'],$b['title']));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>SI-NDO - <?= isset($page_title) ? htmlspecialchars($page_title) : 'Manajemen Tugas Mahasiswa' ?></title>
<link rel="icon" href="/sindo/assets/images/logo.png" type="image/png"/>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"/>

<style>
/* ---------- Navbar styles (sama seperti sebelumnya) ---------- */
.navbar-custom{background:linear-gradient(135deg,rgba(13,110,253,.9),rgba(0,191,255,.9));backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,.1);transition:all .4s}
.navbar-custom.scrolled{background:linear-gradient(135deg,rgba(13,110,253,1),rgba(0,191,255,.9));box-shadow:0 4px 15px rgba(0,0,0,.15)}
.navbar-brand{font-weight:700;letter-spacing:.5px;display:flex;align-items:center;gap:8px}
.nav-link{position:relative;font-weight:500;color:#fff!important;padding:8px 12px}
.dropdown-menu{border-radius:12px;overflow:hidden;background:rgba(255,255,255,.95);backdrop-filter:blur(8px);border:none;box-shadow:0 8px 18px rgba(0,0,0,.15)}
.profile-icon{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0d6efd,#42aec1ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem}
.profile-notification{position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:#ff4757;color:#fff;font-size:.6rem;display:flex;align-items:center;justify-content:center}
.profile-alert{position:fixed;top:80px;right:20px;z-index:1000;animation:slideIn .5s ease-out;box-shadow:0 4px 12px rgba(0,0,0,.15);border-left:4px solid #ff4757}
@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
@media (max-width:991px){.nav-link::after{display:none}.profile-alert{top:70px;right:10px;left:10px}}

/* ---------- Music Player ---------- */
.music-player-container{position:fixed;bottom:20px;right:20px;z-index:2000;max-width:92vw}
.music-player{background:rgba(18,18,18,.95);color:#fff;width:420px;max-width:92vw;border-radius:18px;overflow:hidden;box-shadow:0 12px 30px rgba(0,0,0,.35);transition:transform .35s,box-shadow .35s,border-radius .35s;backdrop-filter:blur(6px)}
.music-header{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;background:linear-gradient(135deg,#0d6efd,#42aec1)}
.title-pack{display:flex;align-items:center;gap:.6rem;min-width:0}
.mini-wave{display:flex;gap:2px;align-items:flex-end;height:16px}
.mini-wave span{width:3px;height:8px;background:#fff;opacity:.85;border-radius:2px;animation:bounce 1s infinite ease-in-out}
.mini-wave span:nth-child(2){animation-delay:.1s}.mini-wave span:nth-child(3){animation-delay:.2s}
@keyframes bounce{0%,100%{height:6px;opacity:.6}50%{height:14px;opacity:1}}
.header-text{display:flex;flex-direction:column;min-width:0}
.header-text strong{font-size:.95rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.header-text small{opacity:.9}
.music-player.collapsed .music-body{display:none}
.music-player .pill-mini{display:none;gap:.5rem;align-items:center;padding:.4rem .6rem;background:rgba(0,0,0,.15);border-radius:999px}
.music-player.collapsed .pill-mini{display:flex}
.pill-mini .pill-title{max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600}

/* body */
.music-body{padding:.85rem 1rem 1rem;display:block}
.track-title{font-weight:700;text-align:center;margin-bottom:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.controls-row{display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap}
.progress{height:8px;background:#333;border-radius:6px;cursor:pointer;flex:1;margin-right:.5rem}
.progress .progress-bar{background:#0d6efd}
.timeline{display:flex;justify-content:space-between;font-size:.8rem;opacity:.85;margin-top:.25rem;font-variant-numeric:tabular-nums}

.music-controls{display:flex;align-items:center;justify-content:center;gap:.5rem;margin-top:.6rem;flex-wrap:wrap}
.music-btn{background:#222;border:none;color:#fff;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:.18s}
.music-btn:hover{background:#0d6efd;transform:translateY(-1px)}
.music-btn.active{outline:2px solid rgba(13,110,253,.8)}

/* playlist & search */
.controls-extra{display:flex;gap:.5rem;align-items:center;margin-top:.6rem}
.search-input{flex:1}
.volume-group{display:flex;gap:.4rem;align-items:center}
.playlist{margin-top:.75rem;max-height:220px;overflow:auto;border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:6px}
.playlist-item{display:flex;align-items:center;gap:.6rem;padding:.45rem .6rem;border-bottom:1px dashed rgba(255,255,255,.06);cursor:pointer}
.playlist-item:last-child{border-bottom:none}
.play-index{opacity:.7;min-width:28px;text-align:right;font-variant-numeric:tabular-nums}
.play-title{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.playlist-item:hover{background:rgba(255,255,255,.04)}
.playlist-item.active{background:rgba(13,110,253,.14)}

@media (max-width:575px){
  .music-player-container{bottom:14px;right:14px;left:14px}
  .music-player{width:100%;border-radius:16px}
  .pill-mini .pill-title{max-width:46vw}
}
</style>
</head>
<body>
<?php if (isLoggedIn() && !$profile_complete): ?>
<div class="alert alert-warning alert-dismissible fade show profile-alert" role="alert">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <?= $profile_message ?>
  <a href="/sindo/pages/profil/index.php" class="alert-link">Lengkapi Sekarang</a>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/sindo/index.php">
      <img src="/sindo/assets/images/fontlogo.png" alt="SINDO Logo" style="height:55px;margin-right:10px;">
    </a>
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isLoggedIn()): ?>
        <li class="nav-item"><a class="nav-link <?= $current_page=='dashboard.php'?'active':'' ?>" href="/sindo/pages/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'tugas')!==false?'active':'' ?>" href="/sindo/pages/tugas/index.php">Tugas</a></li>
        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'jadwal')!==false?'active':'' ?>" href="/sindo/pages/jadwal/index.php">Jadwal</a></li>
        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'acara')!==false?'active':'' ?>" href="/sindo/pages/acara/index.php">Acara</a></li>
        <li class="nav-item"><a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'mata_kuliah')!==false?'active':'' ?>" href="/sindo/pages/mata_kuliah/index.php">Mata Kuliah</a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if (isLoggedIn()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
            <div class="profile-icon position-relative">
              <i class="bi bi-person"></i>
              <?php if (!$profile_complete): ?><span class="profile-notification">!</span><?php endif; ?>
            </div>
            <?= htmlspecialchars($_SESSION['username']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/sindo/pages/profil/index.php"><i class="bi bi-person me-2"></i>Profil <?php if (!$profile_complete): ?><span class="badge bg-danger float-end">!</span><?php endif; ?></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/sindo/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="/sindo/login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="/sindo/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container mt-5 pt-5"></main>

<!-- ===================== MUSIC PLAYER FLOATING (NO COVER) ===================== -->
<div class="music-player-container" aria-live="polite">
  <div class="music-player collapsed" id="musicPlayer" role="region" aria-label="Music player">
    <div class="music-header" id="togglePlayer" title="Tampilkan/Sembunyikan Pemutar">
      <div class="title-pack">
        <div class="mini-wave" aria-hidden="true"><span></span><span></span><span></span></div>
        <div class="header-text">
          <strong id="headerTitle">Now Playing</strong>
          <small id="headerSubtitle"></small>
        </div>
      </div>

      <div class="pill-mini">
        <button class="music-btn" id="miniPlayPause" aria-label="Play/Pause mini"><i class="bi bi-play-fill"></i></button>
        <span class="pill-title" id="miniTitle">—</span>
      </div>

      <i class="bi bi-chevron-up" id="toggleIcon"></i>
    </div>

    <div class="music-body" id="musicBody">
      <div class="track-title" id="trackTitle">Tidak ada lagu</div>

      <div class="controls-row">
        <div class="progress" id="seekBar" role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
          <div class="progress-bar" id="progressBar" style="width:0%"></div>
        </div>

        <div class="volume-group">
          <button class="music-btn" id="muteBtn" aria-label="Mute"><i class="bi bi-volume-up-fill" id="muteIcon"></i></button>
          <input id="volumeRange" type="range" min="0" max="1" step="0.01" value="0.85" style="width:90px" aria-label="Volume">
        </div>
      </div>

      <div class="timeline"><span id="currentTime">0:00</span><span id="totalTime">0:00</span></div>

      <div class="music-controls">
        <button class="music-btn" id="prevBtn" aria-label="Sebelumnya"><i class="bi bi-skip-backward-fill"></i></button>
        <button class="music-btn" id="playPauseBtn" aria-label="Play/Pause"><i class="bi bi-play-fill"></i></button>
        <button class="music-btn" id="nextBtn" aria-label="Berikutnya"><i class="bi bi-skip-forward-fill"></i></button>
        <button class="music-btn" id="shuffleBtn" aria-label="Shuffle"><i class="bi bi-shuffle"></i></button>
        <button class="music-btn" id="repeatBtn" aria-label="Repeat"><i class="bi bi-repeat"></i></button>
      </div>

      <div class="controls-extra">
        <input id="searchInput" class="form-control form-control-sm search-input" type="search" placeholder="Cari lagu..." aria-label="Search playlist">
        <div class="btn-group btn-group-sm" role="group" aria-label="Sort or actions">
          <button class="btn btn-outline-light" id="sortBtn" title="Urutkan A→Z"><i class="bi bi-sort-alpha-down"></i></button>
        </div>
      </div>

      <!-- Playlist -->
      <div class="playlist" id="playlist" aria-label="Playlist"></div>

      <audio id="audioPlayer" preload="metadata"></audio>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ===================== Init Data ===================== */
const tracks = <?= json_encode($tracks, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?: '[]' ?>;
const baseUrl = <?= json_encode($musicWebPath, JSON_UNESCAPED_SLASHES) ?>;

/* ===================== Elements ===================== */
const playerEl = document.getElementById('musicPlayer');
const audio = document.getElementById('audioPlayer');
const trackTitle = document.getElementById('trackTitle');
const headerTitle = document.getElementById('headerTitle');
const headerSubtitle = document.getElementById('headerSubtitle');
const miniTitle = document.getElementById('miniTitle');

const toggleBtn = document.getElementById('togglePlayer');
const toggleIcon = document.getElementById('toggleIcon');
const musicBody = document.getElementById('musicBody');

const playPauseBtn = document.getElementById('playPauseBtn');
const miniPlayPause = document.getElementById('miniPlayPause');
const nextBtn = document.getElementById('nextBtn');
const prevBtn = document.getElementById('prevBtn');
const shuffleBtn = document.getElementById('shuffleBtn');
const repeatBtn = document.getElementById('repeatBtn');

const progressBar = document.getElementById('progressBar');
const seekBar = document.getElementById('seekBar');
const currentTimeEl = document.getElementById('currentTime');
const totalTimeEl = document.getElementById('totalTime');

const playlistEl = document.getElementById('playlist');
const searchInput = document.getElementById('searchInput');
const sortBtn = document.getElementById('sortBtn');

const volumeRange = document.getElementById('volumeRange');
const muteBtn = document.getElementById('muteBtn');
const muteIcon = document.getElementById('muteIcon');

const LS_KEY = 'sindo_music_player_full_v1';

/* ===================== State ===================== */
let currentIndex = 0;
let isShuffled = false;
let order = [];
let repeatMode = 'all'; // all | one | off
let filteredOrder = null; // saat search aktif
let lastVolume = 0.85;

/* ===================== Helpers ===================== */
function fmtTime(s){
  s = Math.max(0, Math.floor(s||0));
  const m = Math.floor(s/60), r = s%60;
  return m + ':' + (r<10?('0'+r):r);
}
function setPlayIcon(isPlaying){
  const c = isPlaying ? 'bi-pause-fill' : 'bi-play-fill';
  playPauseBtn.innerHTML = `<i class="bi ${c}"></i>`;
  miniPlayPause.innerHTML = `<i class="bi ${c}"></i>`;
}
function updateHeaderTitles(title){
  headerTitle.textContent = title || 'Now Playing';
  miniTitle.textContent = title || '—';
  trackTitle.textContent = title || 'Tidak ada lagu';
  document.title = title ? `▶ ${title} — SI-NDO` : 'SI-NDO';
}
function saveState(){
  try {
    localStorage.setItem(LS_KEY, JSON.stringify({
      currentIndex, isShuffled, order, repeatMode,
      time: audio.currentTime || 0,
      collapsed: playerEl.classList.contains('collapsed'),
      volume: parseFloat(audio.volume || volumeRange.value || 0.85)
    }));
  } catch(e){}
}
function loadState(){
  try { return JSON.parse(localStorage.getItem(LS_KEY) || 'null'); } catch(e){ return null; }
}
function shuffleArray(arr){
  const a = arr.slice();
  for (let i=a.length-1;i>0;i--){ const j = Math.floor(Math.random()*(i+1)); [a[i], a[j]] = [a[j], a[i]]; }
  return a;
}

/* ===================== Playlist UI ===================== */
function buildOrder(keepCurrent=true){
  const allIdx = tracks.map((_,i)=>i);
  if (isShuffled){
    let s = shuffleArray(allIdx);
    if (keepCurrent && s.length){
      const pos = s.indexOf(currentIndex);
      if (pos > 0) [s[0], s[pos]] = [s[pos], s[0]];
    }
    order = s;
  } else {
    order = allIdx.slice();
    if (keepCurrent){
      const before = order.slice(0, order.indexOf(currentIndex));
      const after = order.slice(order.indexOf(currentIndex));
      order = after.concat(before);
    }
  }
  renderPlaylist();
  highlightActiveInPlaylist();
}

function renderPlaylist(){
  if (!tracks.length){ playlistEl.innerHTML = '<div class="p-3 text-center text-muted">Tidak ada lagu di /assets/music</div>'; return; }
  const useOrder = filteredOrder ?? order;
  let html = '';
  useOrder.forEach((idx, i)=>{
    const t = tracks[idx];
    html += `
      <div class="playlist-item ${idx===currentIndex?'active':''}" data-idx="${idx}" title="${t.title}">
        <div class="play-index">${i+1}</div>
        <div class="play-title">${t.title}</div>
        <div class="play-icons"><i class="bi bi-music-note-beamed"></i></div>
      </div>`;
  });
  playlistEl.innerHTML = html;
}

function highlightActiveInPlaylist(){
  playlistEl.querySelectorAll('.playlist-item').forEach(el=>{
    const idx = Number(el.dataset.idx);
    el.classList.toggle('active', idx===currentIndex);
  });
  const active = playlistEl.querySelector('.playlist-item.active');
  if (active) active.scrollIntoView({block:'nearest'});
}

/* ===================== Playback ===================== */
function loadTrackByIndex(idx, autoPlay=true, resumeTime=0){
  if (!tracks.length) { updateHeaderTitles('Tidak ada lagu di /assets/music'); setPlayIcon(false); return; }
  currentIndex = (idx + tracks.length) % tracks.length;
  const tr = tracks[currentIndex];
  audio.src = baseUrl + encodeURIComponent(tr.file);
  updateHeaderTitles(tr.title);
  totalTimeEl.textContent = '0:00';
  if (autoPlay){
    audio.play().then(()=> { setPlayIcon(true); if (resumeTime>0) audio.currentTime = resumeTime; }).catch(()=> setPlayIcon(false));
  } else setPlayIcon(false);
  highlightActiveInPlaylist();
  saveState();
}

function getNextIndex(direction){
  const useOrder = filteredOrder ?? order;
  const pos = useOrder.indexOf(currentIndex);
  if (pos === -1) return currentIndex;
  let newPos = pos + direction;
  if (newPos >= useOrder.length){
    if (repeatMode === 'all') newPos = 0; else return null;
  }
  if (newPos < 0){
    if (repeatMode === 'all') newPos = useOrder.length - 1; else return null;
  }
  return useOrder[newPos];
}

/* ===================== Events ===================== */
toggleBtn.addEventListener('click', (e)=>{
  if (e.target.closest('#miniPlayPause')) return;
  setCollapsed(!playerEl.classList.contains('collapsed'));
});

function setCollapsed(collapsed){
  if (collapsed){
    playerEl.classList.add('collapsed'); musicBody.style.display = 'none'; toggleIcon.classList.replace('bi-chevron-down','bi-chevron-up'); headerSubtitle.textContent = '';
  } else {
    playerEl.classList.remove('collapsed'); musicBody.style.display = 'block'; toggleIcon.classList.replace('bi-chevron-up','bi-chevron-down'); headerSubtitle.textContent = 'Playing controls';
  }
  saveState();
}

playPauseBtn.addEventListener('click', ()=> {
  if (!audio.src) { loadTrackByIndex(currentIndex, true); return; }
  if (audio.paused) { audio.play(); setPlayIcon(true); } else { audio.pause(); setPlayIcon(false); }
});
miniPlayPause.addEventListener('click', ()=> playPauseBtn.click());

nextBtn.addEventListener('click', ()=> {
  const next = getNextIndex(1);
  if (next === null){ audio.pause(); setPlayIcon(false); return; }
  loadTrackByIndex(next, true);
});
prevBtn.addEventListener('click', ()=> {
  const prev = getNextIndex(-1);
  if (prev === null){ audio.pause(); setPlayIcon(false); return; }
  loadTrackByIndex(prev, true);
});

shuffleBtn.addEventListener('click', ()=> {
  isShuffled = !isShuffled;
  shuffleBtn.classList.toggle('active', isShuffled);
  buildOrder(true);
  saveState();
});

repeatBtn.addEventListener('click', ()=> {
  repeatMode = (repeatMode === 'all') ? 'one' : (repeatMode === 'one' ? 'off' : 'all');
  if (repeatMode === 'all'){ repeatBtn.innerHTML = '<i class="bi bi-repeat"></i>'; repeatBtn.classList.add('active'); }
  else if (repeatMode === 'one'){ repeatBtn.innerHTML = '<i class="bi bi-repeat-1"></i>'; repeatBtn.classList.add('active'); }
  else { repeatBtn.innerHTML = '<i class="bi bi-repeat"></i>'; repeatBtn.classList.remove('active'); }
  saveState();
});

/* Playlist click */
playlistEl.addEventListener('click', (e)=> {
  const item = e.target.closest('.playlist-item');
  if (!item) return;
  const idx = Number(item.dataset.idx);
  currentIndex = idx;
  buildOrder(true);
  loadTrackByIndex(idx, true);
});

/* Search */
searchInput.addEventListener('input', (e)=> {
  const q = (e.target.value || '').trim().toLowerCase();
  if (!q){ filteredOrder = null; renderPlaylist(); highlightActiveInPlaylist(); return; }
  filteredOrder = [];
  order.forEach(idx => { if (tracks[idx].title.toLowerCase().includes(q)) filteredOrder.push(idx); });
  renderPlaylist();
  highlightActiveInPlaylist();
});

/* Sort button */
let sortedAZ = true;
sortBtn.addEventListener('click', ()=> {
  if (!tracks.length) return;
  if (sortedAZ){
    const allIdx = tracks.map((_,i)=>i);
    allIdx.sort((a,b)=> tracks[a].title.localeCompare(tracks[b].title, undefined, {sensitivity:'base'}));
    order = allIdx; sortedAZ = false; sortBtn.innerHTML = '<i class="bi bi-sort-alpha-up-alt"></i>';
  } else {
    order = tracks.map((_,i)=>i); sortedAZ = true; sortBtn.innerHTML = '<i class="bi bi-sort-alpha-down"></i>';
  }
  buildOrder(true);
});

/* Audio events */
audio.addEventListener('loadedmetadata', ()=> totalTimeEl.textContent = fmtTime(audio.duration || 0));
audio.addEventListener('timeupdate', ()=> {
  if (audio.duration){
    const pct = (audio.currentTime / audio.duration) * 100;
    progressBar.style.width = pct + '%';
    seekBar.setAttribute('aria-valuenow', Math.floor(pct));
    currentTimeEl.textContent = fmtTime(audio.currentTime);
    saveState();
  }
});
audio.addEventListener('ended', ()=> {
  if (repeatMode === 'one'){ audio.currentTime = 0; audio.play(); return; }
  const next = getNextIndex(1);
  if (next === null){ setPlayIcon(false); return; }
  loadTrackByIndex(next, true);
});

/* Seek */
seekBar.addEventListener('click', (e)=>{
  if (!audio.duration) return;
  const rect = seekBar.getBoundingClientRect();
  const ratio = (e.clientX - rect.left) / rect.width;
  audio.currentTime = Math.max(0, Math.min(audio.duration * ratio, audio.duration - 0.25));
});

/* Keyboard */
window.addEventListener('keydown', (e)=> {
  const tag = (document.activeElement && document.activeElement.tagName) || '';
  if (['INPUT','TEXTAREA','SELECT'].includes(tag)) return;
  if (e.code === 'Space'){ e.preventDefault(); playPauseBtn.click(); }
  if (e.code === 'ArrowRight'){ e.preventDefault(); nextBtn.click(); }
  if (e.code === 'ArrowLeft'){ e.preventDefault(); prevBtn.click(); }
});

/* Volume & Mute */
volumeRange.addEventListener('input', (e)=> {
  const v = parseFloat(e.target.value);
  audio.volume = v;
  lastVolume = v;
  muteIcon.className = (v > 0 ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill');
  saveState();
});
muteBtn.addEventListener('click', ()=> {
  if (audio.volume > 0){ lastVolume = audio.volume; audio.volume = 0; volumeRange.value = 0; muteIcon.className = 'bi bi-volume-mute-fill'; }
  else { audio.volume = lastVolume || 0.85; volumeRange.value = audio.volume; muteIcon.className = 'bi bi-volume-up-fill'; }
  saveState();
});

/* ===================== Init ===================== */
(function init(){
  if (!tracks.length){
    updateHeaderTitles('Tidak ada lagu di /assets/music');
    setCollapsed(true);
    setPlayIcon(false);
    renderPlaylist();
    return;
  }

  // default order = scan order
  order = tracks.map((_,i)=>i);

  // load state if ada
  const st = loadState();
  if (st){
    currentIndex = Math.min(Math.max(0, st.currentIndex || 0), tracks.length - 1);
    isShuffled   = !!st.isShuffled;
    repeatMode   = st.repeatMode || 'all';
    const vol = (typeof st.volume === 'number') ? st.volume : 0.85;
    audio.volume = vol; volumeRange.value = vol; lastVolume = vol;
    muteIcon.className = (vol>0 ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill');
    shuffleBtn.classList.toggle('active', isShuffled);
    if (repeatMode === 'all') { repeatBtn.innerHTML = '<i class="bi bi-repeat"></i>'; repeatBtn.classList.add('active'); }
    else if (repeatMode === 'one') { repeatBtn.innerHTML = '<i class="bi bi-repeat-1"></i>'; repeatBtn.classList.add('active'); }
    else { repeatBtn.innerHTML = '<i class="bi bi-repeat"></i>'; repeatBtn.classList.remove('active'); }

    buildOrder(true);
    setCollapsed(!!st.collapsed);
    loadTrackByIndex(currentIndex, true, st.time || 0);
  } else {
    setCollapsed(true);
    audio.volume = 0.85; volumeRange.value = 0.85; lastVolume = 0.85;
    buildOrder(true);
    loadTrackByIndex(order[0], false);
  }
  renderPlaylist();
})();
</script>
</body>
</html>
