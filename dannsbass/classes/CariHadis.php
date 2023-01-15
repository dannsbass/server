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
            $this->q = $q;
        }

        $hasil_pencarian = '';

        foreach (explode(' ', preg_replace('/[^a-z\s]/', '', strtolower($this->q))) as $kata) {
            $file = "{$this->dir_kosakata}/" . $kata;
            if (self::fileExists($file, false)) {
                $hasil_pencarian .= file_get_contents($file);
            }
        }

        if (empty($hasil_pencarian)) {
            $status = false;
            $hasil = "Kosong";
        } else {
            $status = true;
            $index_daftar_hasil_pencarian = array_count_values(explode(' ', trim($hasil_pencarian)));
            array_multisort($index_daftar_hasil_pencarian, SORT_DESC);
            $hasil = $index_daftar_hasil_pencarian;
        }

        $respon = [
            'status'    => $status,
            'query'     => $this->q,
            'durasi'    => (microtime(true) - $this->awal),
            'hasil'     => $hasil,
        ];

        return json_encode(
            $respon,
            // JSON_PRETTY_PRINT
        );
    }

    /**
     * Mencari hadis berdasarkan nama kitab dan nomor hadis
     */
    public function cariKitab(string $kitab, int $id = null)
    {
        $status = false;
        if (empty($kitab)) {
            $hasil = 'Nama kitab tidak boleh kosong';
        } elseif (is_null($id)) {
            $this->q = $kitab;
            if (in_array($kitab, DaftarKitabHadis::MATAN_TERJEMAH)) {
                $status = true;
                $hasil = [
                    'nama_kitab'    => $kitab,
                    'jumlah_item'  => (int)trim(file_get_contents("$this->dir_jumlah_hadis/$kitab")),
                ];
            }else{
                $hasil = 'Nama kitab tidak dikenal';
            }
        } else {
            $this->q = $kitab . $id;
            if (in_array($kitab, DaftarKitabHadis::MATAN_TERJEMAH)) {
                $file = $this->dir_db_per_hadis . DIRECTORY_SEPARATOR . $kitab . $id;
                if (file_exists($file)) {
                    $status = true;
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
                'status'    => $status,
                'query'     => $this->q,
                // 'durasi'    => (microtime(true) - $this->awal), // dev mode
                'hasil'     => $hasil,
            ],
            // JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Mengupdate jumlah hadis dalam setiap kitab (jika ada perubahan)
     */
    public static function updateJumlahHadis()
    {
        foreach (DaftarKitabHadis::MATAN_TERJEMAH as $key => $kitab) {
            file_put_contents(__DIR__.'/../jumlah_hadis/'.$kitab, count(file(__DIR__ . "/../db/$kitab")));
        }
    }

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
        foreach(DaftarKitabHadis::MATAN_TERJEMAH as $nomorkitab => $kitab){
            $file = __DIR__."/../db/$kitab";
            if(file_exists($file)){
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
     * Mengecek keberadaan file secara case-insensitive
     */
    public static function fileExists($fileName, $caseSensitive = true) {

        if(file_exists($fileName)) {
            return $fileName;
        }
        if($caseSensitive) return false;
    
        // Handle case insensitive requests            
        $directoryName = dirname($fileName);
        $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
        $fileNameLowerCase = strtolower($fileName);
        foreach($fileArray as $file) {
            if(strtolower($file) == $fileNameLowerCase) {
                return $file;
            }
        }
        return false;
    }

    /**
     * Membuat direktori jika belum ada
     * @param string @dirkosakata
     */
    public function bikindir($dirkosakata)
    {
        if (!file_exists($dirkosakata) or !is_dir($dirkosakata)) mkdir($dirkosakata);
    }

    /**
     * Cek ada direktori atau tidak
     * @param array|string $dir
     */
    private function adaDir($dir)
    {
        if(is_array($dir)){
            foreach ($dir as $d) {
                if(!is_dir($d)) return false;
            }
        }
        if(!is_dir($dir)) return false;
        return true;
    }

    /**
     * Membuat indeks hadis berdasarkan kosakata
     * @param DaftarKitabHadis::MATAN_TERJEMAH|MATAN_ARAB|SYARAH_ARAB $kategori
     * @param ../db-per-hadis $dirkitab
     * @param ../kosakata $dirkosakata
     * @param ../tmp $tmpdir
     */
    public function indexHadis(array $kategori, string $dirkitab, string $dirkosakata, string $tmpdir){

        if(!$this->adaDir($dirkitab)) return false;
       
       // mkdir
       $this->bikindir($dirkosakata);
       $this->bikindir($tmpdir);
       
       foreach ($kategori as $no => $kitab) {
           $totalkitab = glob("$dirkitab/$kitab*");
           for ($i = 1; $i <= count($totalkitab); $i++) {
               $data = explode('|', file_get_contents("$dirkitab/$kitab$i"));
               $nokitab = trim($data[0]);
               $nohadis = trim($data[1]);
               // $nass = $data[2];
               $terjemah = trim($data[3]);
               $katas = explode(' ', $terjemah);
               foreach ($katas as $nokata => $kata) {
                   // bersihkan selain huruf dan spasi
                   $kata = preg_replace('/[^a-zA-Z\s]/', '', trim($kata));
                   if(empty($kata)) continue;
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
           echo "$kitab selesai diproses\n";
       }
   }

}
