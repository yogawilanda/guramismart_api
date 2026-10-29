=== AUTH ===
PASS  register A -> 201
PASS  login password salah -> 422
PASS  me -> 200
PASS  tanpa token -> 401
=== SIKLUS ===
PASS  buat siklus -> 201
PASS  durasi otomatis = 30
PASS  target_days default 120
PASS  siklus invalid -> 422
=== ISOLASI DATA ===
PASS  B baca siklus A -> 404
PASS  B hapus siklus A -> 404
PASS  B input pakan ke siklus A -> 404
=== PAKAN & AIR ===
PASS  pakan pagi -> 201
PASS  pakan sore -> 201
PASS  pakan sebelum tebar -> 422
PASS  air -> 201
PASS  status pH 9 = high
PASS  status suhu 27 = optimal
PASS  pH 50 -> 422
=== DASHBOARD SEBELUM PANEN ===
PASS  dashboard -> 200
PASS  profit null sebelum panen
PASS  rata pakan = 5 kg/hari
=== PANEN ===
PASS  panen -> 201
PASS  panen ganda -> 422
PASS  status = harvested
PASS  ubah siklus selesai -> 422
=== DASHBOARD SETELAH PANEN ===
PASS  profit = 1.500.000
PASS  ROI = 50%
PASS  produktivitas = 5 kg/m2
PASS  ringkasan -> 200
=== LAINNYA ===
PASS  panduan = 6 bab
PASS  detail panduan -> 200
PASS  notifikasi -> 200
PASS  hapus siklus -> 204
PASS  logout -> 200
PASS  token lama ditolak -> 401

HASIL: 35 PASS, 0 FAIL
