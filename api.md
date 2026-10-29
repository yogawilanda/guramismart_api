# GuramiSmart API v1

**Base URL:** `https://guramismartapi.yogawilanda.com/api/v1`

Semua request wajib header `Accept: application/json`.
Endpoint bertanda 🔒 wajib header `Authorization: Bearer {token}`.

Kode status umum: `200` OK · `201` dibuat · `204` dihapus · `401` belum login · `404` tidak ditemukan / bukan milik user · `422` validasi gagal.

---

## Auth
| Method | URL | Body |
|---|---|---|
| POST | `/auth/register` | `name, email, password, password_confirmation` |
| POST | `/auth/login` | `email, password` |
| POST 🔒 | `/auth/logout` | – |
| GET 🔒 | `/auth/me` | – |

Respons register/login: `{ "user": {...}, "token": "1|abc..." }`

## Siklus Budidaya 🔒
| Method | URL | Body / Query |
|---|---|---|
| GET | `/cycles` | `?status=active\|harvested` |
| POST | `/cycles` | `pond_area_m2*, seed_count*, stocking_date*, initial_capital*, name, seed_size, target_days` |
| GET | `/cycles/{id}` | – |
| PATCH | `/cycles/{id}` | field yang diubah (ditolak 422 jika sudah panen) |
| DELETE | `/cycles/{id}` | – |

Respons siklus menyertakan `duration_days`, `duration_months`, `days_to_harvest`, `status`.

## Panduan Budidaya 🔒
| Method | URL |
|---|---|
| GET | `/guides` |
| GET | `/guides/{slug}` |

Slug: `persiapan-kolam`, `pemilihan-penebaran-benih`, `manajemen-pakan`, `kualitas-air`, `hama-penyakit`, `panen-pascapanen`.

## Pakan Harian 🔒
| Method | URL | Body / Query |
|---|---|---|
| GET | `/cycles/{id}/feed-logs` | `?from=YYYY-MM-DD&to=YYYY-MM-DD` (paginated) |
| POST | `/cycles/{id}/feed-logs` | `fed_on*, amount_kg*, session (pagi\|sore), note` |
| PATCH | `/cycles/{id}/feed-logs/{logId}` | field yang diubah |
| DELETE | `/cycles/{id}/feed-logs/{logId}` | – |

## Kualitas Air 🔒
| Method | URL | Body / Query |
|---|---|---|
| GET | `/cycles/{id}/water-logs` | `?from=&to=` (paginated) |
| POST | `/cycles/{id}/water-logs` | `recorded_on*, temperature_c* (0–45), ph* (0–14), note` |
| PATCH | `/cycles/{id}/water-logs/{logId}` | field yang diubah |
| DELETE | `/cycles/{id}/water-logs/{logId}` | – |

Respons menyertakan `status: { temperature: "optimal|low|high", ph: "optimal|low|high" }` (optimal: suhu 25–30 °C, pH 6.5–8.0).

## Panen & Penjualan 🔒
| Method | URL | Body |
|---|---|---|
| GET | `/cycles/{id}/harvest` | – |
| POST | `/cycles/{id}/harvest` | `harvest_date*, total_weight_kg*, total_sales*` (sekali per siklus; status → harvested) |
| PUT | `/cycles/{id}/harvest` | field yang diubah |

## Dashboard 🔒
| Method | URL | Isi |
|---|---|---|
| GET | `/dashboard` | ringkasan semua siklus user |
| GET | `/cycles/{id}/dashboard` | `finance` (modal, penjualan, profit, ROI), `feed` (total, rata-rata/hari), `productivity` (kg/m²), `water_trend` (data grafik) |

Nilai `total_sales`, `profit`, `roi_percent`, `kg_per_m2` = `null` sebelum panen.

## Notifikasi 🔒
| Method | URL |
|---|---|
| GET | `/notifications` (`?unread=1`) |
| PATCH | `/notifications/{id}/read` |
| PATCH | `/notifications/read-all` |

Dikirim otomatis: pengingat pakan 07:00 & 16:00 WIB (user dengan siklus aktif), pengingat panen 06:00 WIB (siklus mencapai `target_days`).

---
`*` = wajib · tanggal format `YYYY-MM-DD` · uang dalam Rupiah utuh · berat dalam kg
