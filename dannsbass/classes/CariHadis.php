<?php

namespace Dannsbass;

use Dannsbass\CariHadis\DaftarKitabHadis;

class CariHadis
{
    private $dir_kosakata = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'kosakata';
    private $dir_db_per_hadis = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'db-per-hadis';
    private $dir_jumlah_hadis = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "jumlah_hadis";
    private $q = '';
    private $awal;

    public function __construct(string $q = '')
    {
        $this->awal = microtime(true);
        $this->q = $q;
    }

    /**
     * Mencari hadis berdasarkan kata-kata tertentu
     */
    public function cariKata(string $q = '')
    {
        if (!empty($q)) {
          $find = array("َ","ِ","ُ","ً","ٍ","ٌ","ْ","ّ");
          $q = str_replace($find,"",$q);
            $this->q = $q;
        }

        $hasil_pencarian = '';
        $kata2 = array_filter(explode(' ', preg_replace('/[^\p{Arabic}|^a-z\s]/u', '', strtolower($this->q))));
        $jumlah_kata2 = count($kata2);

      $kata2_ditemukan = [];  
      $kata2_tak_ditemukan = [];  
      foreach ($kata2 as $kata) {
            $file = "{$this->dir_kosakata}/" . $kata;
            if (self::fileExists($file, false)) {
              $kata2_ditemukan[] = $kata;
                $hasil_pencarian .= file_get_contents($file);
            }else{
              $kata2_tak_ditemukan[] = $kata;
            }
        }

        if (empty($hasil_pencarian)) {
            $ok = false;
            $hasil = null;
        } else {
            $ok = true;
            $index_daftar_hasil_pencarian = array_count_values(explode(' ', trim($hasil_pencarian)));
            array_multisort($index_daftar_hasil_pencarian, SORT_DESC);
            foreach (array_values($index_daftar_hasil_pencarian) as $value) {
                if ($value == $jumlah_kata2) {
                    break;
                }
                $jumlah_kata2--;
            }
            $hasil = array_filter($index_daftar_hasil_pencarian, function($v)use($jumlah_kata2){return $v == $jumlah_kata2;});
        }

        $respon = [
            'ok'        => $ok,
            'query'     => $this->q,
          'kata_ditemukan' => array_unique($kata2_ditemukan),
          'kata_tak_ditemukan' => array_unique($kata2_tak_ditemukan),
            'durasi'    => (microtime(true) - $this->awal),
            'format'    => 'kode_kitab:nomor_hadis:jumlah_kata_yang_ditemukan',
            'kode_kitab' => array_combine(array_keys(DaftarKitabHadis::MATAN_TERJEMAH), array_values(DaftarKitabHadis::MATAN_TERJEMAH)),
            'hasil_pencarian'     => $hasil,
        ];

        return json_encode(
            $respon,
            // JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Mencari hadis berdasarkan nama kitab dan nomor hadis
     */
    public function cariKitab(string $kitab, int $id = null)
    {
        $ok = false;
        if (empty($kitab)) {
            $hasil = 'Nama kitab tidak boleh kosong';
        } elseif (is_null($id)) {
            $this->q = $kitab;
            if (in_array($kitab, DaftarKitabHadis::MATAN_TERJEMAH)) {
                $ok = true;
                $hasil = [
                    'nama_kitab'    => $kitab,
                    'jumlah_item'  => (int)trim(file_get_contents("$this->dir_jumlah_hadis/$kitab")),
                ];
            } else {
                $hasil = 'Nama kitab tidak dikenal';
            }
        } else {
            $this->q = $kitab . $id;
            if (in_array($kitab, DaftarKitabHadis::MATAN_TERJEMAH)) {
                $file = $this->dir_db_per_hadis . DIRECTORY_SEPARATOR . $kitab . $id;
                if (file_exists($file)) {
                    $ok = true;
                    $data = explode('|', trim(file_get_contents($file)));
                    $hasil = [
                        'nama_kitab'    => DaftarKitabHadis::MATAN_TERJEMAH[trim($data[0])],
                        'nomor_hadis'   => trim($data[1]),
                        'nass_hadis'    => trim($data[2]),
                        'terjemah_hadis' => trim($data[3]),
                    ];
                } else {
                    $hasil = "Data tidak ditemukan";
                }
            } else {
                $hasil = "Kitab tidak ditemukan";
            }
        }

        return json_encode(
            [
                'ok'    => $ok,
                'query'     => $this->q,
                // 'durasi'    => (microtime(true) - $this->awal), // dev mode
                'hasil'     => $hasil,
            ],
            // JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Mengecek keberadaan file secara case-insensitive
     */
    public static function fileExists($fileName, $caseSensitive = true)
    {

        if (file_exists($fileName)) {
            return $fileName;
        }
        if ($caseSensitive) return false;

        // Handle case insensitive requests            
        $directoryName = dirname($fileName);
        foreach (glob($fileName, GLOB_NOSORT) as $file) {
            if (strtolower($file) == strtolower($fileName)) {
                return $file;
            }
        }
        return false;
    }
}
