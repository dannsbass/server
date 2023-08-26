<?php

namespace Dannsbass;

use Dannsbass\CariHadis\DaftarKitabHadis;

class OlahKitabHadis
{

    /**
     * Memecah kitab-kitab menjadi file-file kecil berisi satu hadis per file untuk memudahkan proses pengolahan.
     * Struktur isi file kitab adalah sebagai berikut:
     * nomor kitab | nomor hadis | nass hadis | terjemah hadis
     * Contoh:
     * 0 | 3 | قال رسول الله | Rasulullah bersabda
     * Artinya:
     * Shahih_Bukhari (kode: 0) hadis nomor 3
     */
    public static function pecahKitab()
    {
        foreach (DaftarKitabHadis::MATAN_TERJEMAH as $nomorkitab => $kitab) {
            $file = __DIR__ . "/../db/$kitab";
            if (file_exists($file)) {
                $array = file($file);
                $count = count($array);
                $namakitab = trim($kitab);
                foreach ($array as $no => $baris) {
                    $data = explode('|', $baris);
                    // $nomorkitab = $data[0];
                    $nomorhadis = trim($data[1]);
                    // $nass = $data[2];
                    $terjemah = trim($data[3]);
                    echo "$namakitab$nomorhadis diproses\n";
                    file_put_contents(__DIR__ . "/../db-per-hadis/$namakitab$nomorhadis", $baris);
                }
            }
        }
    }

    /**
     * Membuat indeks hadis berdasarkan kosakata
     * @param DaftarKitabHadis::MATAN_TERJEMAH|MATAN_ARAB|SYARAH_ARAB $kategori
     * @param ../db-per-hadis $dirkitab
     * @param ../kosakata $dirkosakata
     * @param ../tmp $tmpdir
     */
    public static function indexHadis(array $kategori, string $dirkitab, string $dirkosakata, string $tmpdir)
    {

        if (!self::adaDir($dirkitab)) return false;

        // mkdir
        self::bikindir($dirkosakata);
        self::bikindir($tmpdir);

        foreach ($kategori as $no => $kitab) {
            $totalkitab = glob("$dirkitab/$kitab*");
            for ($i = 1; $i <= count($totalkitab); $i++) {
                $data = explode('|', file_get_contents("$dirkitab/$kitab$i"));
                $nokitab = trim($data[0]);
                $nohadis = trim($data[1]);
                $nass = $data[2];
                //    $terjemah = trim($data[3]);
                //    $katas = explode(' ', $terjemah);
                $katas = explode(' ', $nass);
                foreach ($katas as $nokata => $kata) {
                    // bersihkan selain huruf dan spasi
                    //    $kata = preg_replace('/[^a-zA-Z\s]/', '', trim($kata));
                    $kata = preg_replace('/[^\p{Arabic}]/u', '', trim($kata));
                    if (empty($kata)) continue;
                    $item = "$nokitab:$nohadis";
                    $tmp = "$tmpdir/$kata";
                    if (file_exists($tmp)) {
                        if ($item == file_get_contents($tmp)) {
                            continue;
                        } else {
                            file_put_contents("$dirkosakata/$kata", "$item ", FILE_APPEND);
                            file_put_contents($tmp, $item);
                        }
                    } else {
                        file_put_contents("$dirkosakata/$kata", "$item ");
                        file_put_contents($tmp, $item);
                    }
                }
                echo "$kitab$i berhasil diproses\n";
                
            }
            echo "$kitab selesai diproses\n"; exit;
        }
    }

    /**
     * Mengupdate jumlah hadis dalam setiap kitab (jika ada perubahan)
     */
    public static function updateJumlahHadis()
    {
        foreach (DaftarKitabHadis::MATAN_TERJEMAH as $key => $kitab) {
            file_put_contents(__DIR__ . '/../jumlah_hadis/' . $kitab, count(file(__DIR__ . "/../db/$kitab")));
        }
    }

    /**
     * Cek ada direktori atau tidak
     * @param array|string $dir
     */
    private static function adaDir($dir)
    {
        if (is_array($dir)) {
            foreach ($dir as $d) {
                if (!is_dir($d)) return false;
            }
        }
        if (!is_dir($dir)) return false;
        return true;
    }
    /**
     * Membuat direktori jika belum ada
     * @param string @dirkosakata
     */
    public static function bikindir($dirkosakata)
    {
        if (!file_exists($dirkosakata) or !is_dir($dirkosakata)) mkdir($dirkosakata);
    }
}
