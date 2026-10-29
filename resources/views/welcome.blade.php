@php
$base = rtrim(url('/api/v1'), '/');
$t = config('guramismart.water.temperature');
$ph = config('guramismart.water.ph');
try { $guides = \App\Models\Guide::orderBy('order')->get(['slug', 'title']); } catch (\Throwable $e) { $guides =
collect(); }

$groups = [
['Autentikasi', 'UC-01', [
['POST', '/auth/register', 'Daftar akun baru', true],
['POST', '/auth/login', 'Masuk, mendapat token', true],
['POST', '/auth/logout', 'Cabut token aktif'],
['GET', '/auth/me', 'Profil akun'],
]],
['Siklus budidaya', 'UC-02', [
['GET', '/cycles', 'Daftar siklus · ?status=active|harvested'],
['POST', '/cycles', 'Mulai siklus: luas kolam, benih, modal'],
['GET', '/cycles/{id}', 'Detail + durasi otomatis'],
['PATCH', '/cycles/{id}', 'Ubah data (terkunci setelah panen)'],
['DELETE', '/cycles/{id}', 'Hapus siklus'],
]],
['Panduan', 'UC-03', [
['GET', '/guides', 'Daftar bab panduan'],
['GET', '/guides/{slug}', 'Isi satu bab'],
]],
['Pakan harian', 'UC-04', [
['GET', '/cycles/{id}/feed-logs', 'Riwayat · ?from=&to='],
['POST', '/cycles/{id}/feed-logs', 'Catat pakan (kg, pagi/sore)'],
['PATCH', '/cycles/{id}/feed-logs/{logId}', 'Koreksi catatan'],
['DELETE', '/cycles/{id}/feed-logs/{logId}', 'Hapus catatan'],
]],
['Kualitas air', 'UC-05', [
['GET', '/cycles/{id}/water-logs', 'Riwayat suhu & pH'],
['POST', '/cycles/{id}/water-logs', 'Catat suhu (°C) & pH'],
['PATCH', '/cycles/{id}/water-logs/{logId}', 'Koreksi catatan'],
['DELETE', '/cycles/{id}/water-logs/{logId}', 'Hapus catatan'],
]],
['Notifikasi', 'UC-06', [
['GET', '/notifications', 'Pengingat pakan & panen · ?unread=1'],
['PATCH', '/notifications/{id}/read', 'Tandai dibaca'],
['PATCH', '/notifications/read-all', 'Tandai semua dibaca'],
]],
['Panen & penjualan', 'UC-07', [
['GET', '/cycles/{id}/harvest', 'Data panen'],
['POST', '/cycles/{id}/harvest', 'Catat berat (kg) & penjualan (Rp)'],
['PUT', '/cycles/{id}/harvest', 'Koreksi data panen'],
]],
['Dashboard', 'UC-08', [
['GET', '/dashboard', 'Ringkasan semua siklus'],
['GET', '/cycles/{id}/dashboard', 'Profit, ROI, pakan, kg/m², tren air'],
]],
];
$total = collect($groups)->sum(fn ($g) => count($g[2]));
$pct = fn ($v, $max) => round($v / $max * 100, 2);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GuramiSmart API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --paper: #f3f0e8;
            --sheet: #faf8f3;
            --ink: #1c2621;
            --soft: #56625b;
            --rule: #d9d3c5;
            --pond: #2d5a45;
            --pond-2: #9fbfa9;
            --band: #cfe0d3;
            --get: #2d5a45;
            --post: #2c4a78;
            --edit: #9a6514;
            --del: #983a2c;
        }

        @media (prefers-color-scheme:dark) {
            :root {
                --paper: #111714;
                --sheet: #161e1a;
                --ink: #e4e8e2;
                --soft: #98a69d;
                --rule: #2a3530;
                --pond: #8cc4a4;
                --pond-2: #3f6b55;
                --band: #22392e;
                --get: #8cc4a4;
                --post: #8fb0e3;
                --edit: #e0b16a;
                --del: #e88f7f;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            background: var(--paper);
            color: var(--ink);
            font: 15px/1.6 "IBM Plex Sans", system-ui, sans-serif;
            -webkit-font-smoothing: antialiased
        }

        .wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 0 24px
        }

        a {
            color: inherit
        }

        code,
        .mono {
            font-family: "IBM Plex Mono", ui-monospace, monospace;
            font-size: .88em
        }

        /* top strip */
        .strip {
            border-bottom: 1px solid var(--rule);
            font-size: 13px;
            color: var(--soft)
        }

        .strip .wrap {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding-top: 12px;
            padding-bottom: 12px;
            flex-wrap: wrap
        }

        .live {
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .live i {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--pond);
            box-shadow: 0 0 0 3px var(--band)
        }

        /* hero */
        .hero {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 56px;
            padding: 72px 0 56px;
            border-bottom: 1px solid var(--rule)
        }

        .kicker {
            font-family: "IBM Plex Mono", monospace;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--soft);
            margin-bottom: 20px
        }

        h1 {
            font-family: "IBM Plex Serif", Georgia, serif;
            font-weight: 500;
            font-size: clamp(36px, 5.4vw, 58px);
            line-height: 1.04;
            letter-spacing: -.02em
        }

        h1 em {
            font-style: italic;
            font-weight: 400;
            color: var(--pond)
        }

        .lede {
            margin-top: 20px;
            max-width: 46ch;
            color: var(--soft);
            font-size: 16.5px
        }

        .base {
            margin-top: 32px;
            display: flex;
            align-items: stretch;
            border: 1px solid var(--ink);
            max-width: 560px
        }

        .base span {
            padding: 12px 14px;
            font-family: "IBM Plex Mono", monospace;
            font-size: 13.5px;
            overflow-x: auto;
            white-space: nowrap;
            flex: 1
        }

        .base button {
            border: 0;
            border-left: 1px solid var(--ink);
            background: var(--ink);
            color: var(--paper);
            font: 500 13px "IBM Plex Sans";
            padding: 0 18px;
            cursor: pointer
        }

        .base button:hover {
            opacity: .85
        }

        .facts {
            margin-top: 28px;
            display: flex;
            gap: 32px;
            flex-wrap: wrap
        }

        .facts div {
            font-size: 13px;
            color: var(--soft)
        }

        .facts b {
            display: block;
            font: 500 26px "IBM Plex Serif", serif;
            color: var(--ink)
        }

        /* gauge panel */
        .panel {
            background: var(--sheet);
            border: 1px solid var(--rule);
            padding: 24px;
            align-self: start
        }

        .panel h3 {
            font: 600 13px "IBM Plex Sans";
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--soft);
            margin-bottom: 4px
        }

        .panel p {
            font-size: 13px;
            color: var(--soft);
            margin-bottom: 20px
        }

        .gauge {
            margin-bottom: 22px
        }

        .gauge .lbl {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 8px
        }

        .gauge .lbl b {
            font-weight: 500
        }

        .track {
            position: relative;
            height: 30px;
            background: repeating-linear-gradient(90deg, var(--rule) 0 1px, transparent 1px 10%);
            border-bottom: 1px solid var(--ink)
        }

        .track .ok {
            position: absolute;
            top: 6px;
            bottom: 0;
            background: var(--band);
            border-left: 2px solid var(--pond);
            border-right: 2px solid var(--pond)
        }

        .ticks {
            display: flex;
            justify-content: space-between;
            font-family: "IBM Plex Mono", monospace;
            font-size: 11px;
            color: var(--soft);
            margin-top: 4px
        }

        .legend {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: var(--soft);
            border-top: 1px dashed var(--rule);
            padding-top: 14px
        }

        .legend span::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 6px;
            vertical-align: -1px
        }

        .legend .l1::before {
            background: var(--band);
            border: 1px solid var(--pond)
        }

        .legend .l2::before {
            border: 1px solid var(--soft)
        }

        /* sections */
        section {
            padding: 64px 0;
            border-bottom: 1px solid var(--rule)
        }

        .sh {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 40px
        }

        .sh h2 {
            font: 500 26px/1.2 "IBM Plex Serif", serif;
            letter-spacing: -.01em
        }

        .sh .note {
            font-size: 13.5px;
            color: var(--soft);
            margin-top: 10px
        }

        /* flow */
        .flow {
            list-style: none;
            counter-reset: s;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-top: 2px solid var(--ink)
        }

        .flow li {
            counter-increment: s;
            padding: 14px 12px 0 0;
            font-size: 13.5px;
            position: relative
        }

        .flow li::before {
            content: counter(s, decimal-leading-zero);
            display: block;
            font: 500 12px "IBM Plex Mono";
            color: var(--soft);
            margin-bottom: 6px
        }

        .flow li::after {
            content: "";
            position: absolute;
            top: -6px;
            left: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--paper);
            border: 2px solid var(--ink)
        }

        .flow b {
            display: block;
            font-weight: 600;
            margin-bottom: 4px
        }

        .flow code {
            color: var(--soft);
            font-size: 11.5px;
            word-break: break-all
        }

        /* endpoints */
        .group {
            margin-bottom: 28px
        }

        .gh {
            display: flex;
            align-items: baseline;
            gap: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--ink)
        }

        .gh h4 {
            font-size: 15px;
            font-weight: 600
        }

        .gh small {
            font: 12px "IBM Plex Mono";
            color: var(--soft)
        }

        .row {
            display: grid;
            grid-template-columns: 72px minmax(0, 1.1fr) 1fr;
            gap: 16px;
            padding: 9px 0;
            border-bottom: 1px solid var(--rule);
            align-items: baseline;
            font-size: 14px
        }

        .m {
            font: 500 11.5px "IBM Plex Mono";
            letter-spacing: .04em
        }

        .GET {
            color: var(--get)
        }

        .POST {
            color: var(--post)
        }

        .PATCH,
        .PUT {
            color: var(--edit)
        }

        .DELETE {
            color: var(--del)
        }

        .row code {
            font-size: 13px;
            word-break: break-all
        }

        .row .d {
            color: var(--soft)
        }

        .pub {
            font: 500 10.5px "IBM Plex Mono";
            border: 1px solid var(--pond);
            color: var(--pond);
            padding: 1px 6px;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: .06em
        }

        /* code */
        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid var(--ink)
        }

        .tabs button {
            background: none;
            border: 0;
            padding: 8px 16px;
            font: 500 13px "IBM Plex Sans";
            color: var(--soft);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px
        }

        .tabs button.on {
            color: var(--ink);
            border-color: var(--ink)
        }

        pre {
            background: var(--sheet);
            border: 1px solid var(--rule);
            border-top: 0;
            padding: 20px;
            overflow-x: auto;
            font: 13px/1.65 "IBM Plex Mono", monospace
        }

        pre .c {
            color: var(--soft)
        }

        .pane {
            display: none
        }

        .pane.on {
            display: block
        }

        .errs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: var(--rule);
            border: 1px solid var(--rule);
            margin-top: 28px
        }

        .errs div {
            background: var(--paper);
            padding: 14px;
            font-size: 13px;
            color: var(--soft)
        }

        .errs b {
            display: block;
            font: 500 20px "IBM Plex Mono";
            color: var(--ink);
            margin-bottom: 2px
        }

        /* guides */
        .gl {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid var(--ink)
        }

        .gl li {
            padding: 14px 16px 14px 0;
            border-bottom: 1px solid var(--rule);
            font-size: 14px
        }

        .gl code {
            display: block;
            color: var(--soft);
            font-size: 12px;
            margin-top: 2px
        }

        footer {
            padding: 32px 0 48px;
            font-size: 13px;
            color: var(--soft)
        }

        footer .wrap {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap
        }

        @media (max-width:860px) {

            .hero,
            .sh {
                grid-template-columns: 1fr;
                gap: 32px
            }

            .hero {
                padding: 48px 0 40px
            }

            .flow {
                grid-template-columns: 1fr;
                border-top: 0;
                border-left: 2px solid var(--ink);
                padding-left: 20px
            }

            .flow li {
                padding: 0 0 18px
            }

            .flow li::after {
                top: 4px;
                left: -27px
            }

            .row {
                grid-template-columns: 62px 1fr
            }

            .row .d {
                grid-column: 2;
                margin-top: -6px;
                font-size: 13px
            }

            .errs {
                grid-template-columns: repeat(2, 1fr)
            }

            .gl {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>

    <div class="strip">
        <div class="wrap">
            <span class="live"><i></i> Server aktif · {{ now()->translatedFormat('d M Y, H:i') }} WIB</span>
            <span class="mono">v1 · Laravel {{ app()->version() }}</span>
        </div>
    </div>

    <main class="wrap">

        <div class="hero">
            <div>
                <div class="kicker">Sistem Informasi Budidaya Ikan Gurami</div>
                <h1>Catatan kolam,<br><em>jadi angka usaha.</em></h1>
                <p class="lede">REST API GuramiSmart: siklus budidaya, pakan harian, suhu &amp; pH air, panen, hingga
                    laporan profit per siklus. Semua respons dalam JSON.</p>

                <div class="base">
                    <span id="base">{{ $base }}</span>
                    <button type="button" onclick="copyBase(this)">Salin</button>
                </div>

                <div class="facts">
                    <div><b>{{ $total }}</b>endpoint</div>
                    <div><b>8</b>use case</div>
                    <div><b>{{ $guides->count() ?: 6 }}</b>bab panduan</div>
                    <div><b>2</b>pengingat otomatis</div>
                </div>
            </div>

            <aside class="panel">
                <h3>Rentang optimal air</h3>
                <p>Setiap input kualitas air dinilai otomatis: <span class="mono">optimal</span>, <span
                        class="mono">low</span>, atau <span class="mono">high</span>.</p>

                <div class="gauge">
                    <div class="lbl"><span>Suhu</span><b class="mono">{{ $t['min'] }}–{{ $t['max'] }} °C</b></div>
                    <div class="track">
                        <div class="ok"
                            style="left:{{ $pct($t['min'], 45) }}%;width:{{ $pct($t['max'] - $t['min'], 45) }}%"></div>
                    </div>
                    <div class="ticks"><span>0</span><span>15</span><span>30</span><span>45 °C</span></div>
                </div>

                <div class="gauge">
                    <div class="lbl"><span>pH</span><b class="mono">{{ $ph['min'] }}–{{ $ph['max'] }}</b></div>
                    <div class="track">
                        <div class="ok"
                            style="left:{{ $pct($ph['min'], 14) }}%;width:{{ $pct($ph['max'] - $ph['min'], 14) }}%">
                        </div>
                    </div>
                    <div class="ticks"><span>0</span><span>7</span><span>14</span></div>
                </div>

                <div class="legend"><span class="l1">optimal</span><span class="l2">di luar batas</span></div>
            </aside>
        </div>

        <section>
            <div class="sh">
                <div>
                    <h2>Alur pakai</h2>
                    <p class="note">Satu siklus budidaya, dari tebar benih sampai laporan.</p>
                </div>
                <ol class="flow">
                    <li><b>Login</b><code>POST /auth/login</code></li>
                    <li><b>Mulai siklus</b><code>POST /cycles</code></li>
                    <li><b>Baca panduan</b><code>GET /guides</code></li>
                    <li><b>Catat harian</b><code>…/feed-logs<br>…/water-logs</code></li>
                    <li><b>Pengingat</b><code>GET /notifications</code></li>
                    <li><b>Panen</b><code>POST …/harvest</code></li>
                    <li><b>Laporan</b><code>GET …/dashboard</code></li>
                </ol>
            </div>
        </section>

        <section>
            <div class="sh">
                <div>
                    <h2>Endpoint</h2>
                    <p class="note">Semua butuh header <code>Authorization: Bearer {token}</code>, kecuali yang bertanda
                        publik.</p>
                </div>
                <div>
                    @foreach ($groups as [$name, $uc, $rows])
                    <div class="group">
                        <div class="gh">
                            <h4>{{ $name }}</h4><small>{{ $uc }}</small>
                        </div>
                        @foreach ($rows as $r)
                        <div class="row">
                            <span class="m {{ $r[0] }}">{{ $r[0] }}</span>
                            <span><code>{{ $r[1] }}</code>@if (!empty($r[3]))<span
                                    class="pub">publik</span>@endif</span>
                            <span class="d">{{ $r[2] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section>
            <div class="sh">
                <div>
                    <h2>Mulai cepat</h2>
                    <p class="note">Login sekali, simpan token, kirim di setiap request.</p>
                </div>
                <div>
                    <div class="tabs">
                        <button type="button" class="on" data-p="py">Python</button>
                        <button type="button" data-p="curl">cURL</button>
                    </div>
                    @verbatim
                    <pre class="pane on" id="py">import requests, pandas as pd

BASE = "<span class="b"></span>"

token = requests.post(f"{BASE}/auth/login",
    json={"email": "EMAIL", "password": "PASSWORD"},
    headers={"Accept": "application/json"}).json()["token"]

H = {"Accept": "application/json", "Authorization": f"Bearer {token}"}

cycles = requests.get(f"{BASE}/cycles", headers=H).json()
dash = requests.get(f"{BASE}/cycles/{cycles[0]['id']}/dashboard", headers=H).json()["data"]

<span class="c"># tren suhu & pH langsung jadi grafik</span>
pd.DataFrame(dash["water_trend"]).plot(x="date", y=["temperature_c", "ph"])</pre>
                    <pre class="pane" id="curl"><span class="c"># 1. login</span>
curl -X POST <span class="b"></span>/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"EMAIL","password":"PASSWORD"}'

<span class="c"># 2. pakai token dari respons</span>
curl <span class="b"></span>/cycles \
  -H "Accept: application/json" -H "Authorization: Bearer TOKEN"</pre>
                    @endverbatim
                    <div class="errs">
                        <div><b>401</b>Token tidak ada / kedaluwarsa</div>
                        <div><b>404</b>Data tidak ada atau bukan milikmu</div>
                        <div><b>422</b>Input ditolak, cek <code>errors</code></div>
                        <div><b>null</b>Profit &amp; kg/m² sebelum panen</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($guides->isNotEmpty())
        <section>
            <div class="sh">
                <div>
                    <h2>Panduan</h2>
                    <p class="note">Isi lengkap via <code>GET /guides/{slug}</code>.</p>
                </div>
                <ol class="gl">
                    @foreach ($guides as $g)
                    <li>{{ $g->title }}<code>{{ $g->slug }}</code></li>
                    @endforeach
                </ol>
            </div>
        </section>
        @endif

    </main>

    <footer>
        <div class="wrap">
            <span>GuramiSmart · Proyek Manajemen Proyek</span>
            <span>Tanggal <span class="mono">YYYY-MM-DD</span> · uang dalam Rupiah · berat dalam kg</span>
        </div>
    </footer>

    <script>
        const BASE = document.getElementById('base').textContent.trim();
document.querySelectorAll('pre .b').forEach(el => el.textContent = BASE);
function copyBase(btn){
  navigator.clipboard.writeText(BASE).then(() => { btn.textContent = 'Tersalin'; setTimeout(() => btn.textContent = 'Salin', 1500); });
}
document.querySelectorAll('.tabs button').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.tabs button, .pane').forEach(x => x.classList.remove('on'));
  b.classList.add('on'); document.getElementById(b.dataset.p).classList.add('on');
}));
    </script>
</body>

</html>
