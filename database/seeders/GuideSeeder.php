<?php

namespace Database\Seeders;

use App\Models\Guide;
use Illuminate\Database\Seeder;

class GuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            ['persiapan-kolam', 'Persiapan Kolam', 'persiapan', 'Keringkan dan bersihkan kolam, perbaiki pematang, lakukan pengapuran dan pemupukan dasar, lalu isi air bertahap sebelum tebar benih.'],
            ['pemilihan-penebaran-benih', 'Pemilihan & Penebaran Benih', 'benih', 'Pilih benih yang aktif, ukuran seragam, dan tidak cacat. Lakukan aklimatisasi suhu sebelum benih dilepas, idealnya pagi atau sore hari.'],
            ['manajemen-pakan', 'Manajemen Pakan', 'pakan', 'Berikan pakan secara teratur pagi dan sore sesuai ukuran ikan. Catat jumlah pakan harian dan hindari sisa pakan berlebih yang menurunkan kualitas air.'],
            ['kualitas-air', 'Pengelolaan Kualitas Air', 'air', 'Pantau suhu dan pH air setiap hari. Jika nilai di luar batas optimal, lakukan penggantian sebagian air dan periksa sumber masalah.'],
            ['hama-penyakit', 'Pencegahan Hama & Penyakit', 'kesehatan', 'Amati perilaku ikan setiap hari. Pisahkan ikan yang tampak sakit, jaga kebersihan kolam, dan cegah masuknya hama dari saluran air.'],
            ['panen-pascapanen', 'Teknik Panen & Pascapanen', 'panen', 'Panen saat target umur atau ukuran tercapai. Lakukan pada pagi hari, kurangi air bertahap, dan tangani ikan dengan hati-hati agar tetap segar.'],
        ];

        foreach ($guides as $i => [$slug, $title, $category, $body]) {
            Guide::updateOrCreate(['slug' => $slug], compact('title', 'category', 'body') + ['order' => $i + 1]);
        }
    }
}
