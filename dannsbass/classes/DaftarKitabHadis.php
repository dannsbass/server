<?php
namespace Dannsbass\CariHadis;

class DaftarKitabHadis
{
    /**
     * Daftar kitab-kitab matan yang telah diterjemahkan.
     * JANGAN UBAH URUTAN KITAB!
     */
    public const MATAN_TERJEMAH = ['Shahih_Bukhari', 'Shahih_Muslim', 'Sunan_Abu_Daud', 'Sunan_Tirmidzi', 'Sunan_Nasai', 'Sunan_Ibnu_Majah', 'Musnad_Darimi', 'Muwatho_Malik', 'Musnad_Ahmad', 'Sunan_Daraquthni', 'Musnad_Syafii', 'Mustadrak_Hakim', 'Shahih_Ibnu_Khuzaimah', 'Shahih_Ibnu_Hibban', 'Bulughul_Maram', 'Riyadhus_Shalihin'];

    public const MATAN_ARAB = ['Al_Adabul_Mufrad', 'Mushannaf_Ibnu_Abi_Syaibah', 'Mushannaf_Abdurrazzaq', 'Musnad_Abu_Yala', 'Musnad_Bazzar', 'Mujam_Thabarani_Shaghir', 'Mujam_Thabarani_Awsath', 'Mujam_Thabarani_Kabir', 'Hilyatul_Aulia', 'Doa_Thabarani', 'Arbain_Nawawi_I', 'Arbain_Nawawi_II', 'Akhlak_Rawi_Khatib', 'Mukhtashar_Qiyamullail_Marwazi', 'Syuabul_Iman_Baihaqi', 'Shahih_Ibnu_Khuzaimah_Arab', 'Shahih_Ibnu_Hibban_Arab', 'Riyadhus_Shalihin_Arab', 'Shahih_Adabul_Mufrad_Terjemah', 'Silsilah_Shahihah_Terjemah', 'Bulughul_Maram_Arab', 'Bulughul_Maram_Tahqiq_Fahl', 'Sunan_Baihaqi_Shaghir', 'Sunan_Baihaqi_Kabir', 'Targhib_wat_Tarhib_Mundziri', 'Majmauz_Zawaid'];
    
    public const SYARAH_ARAB = ['Fathul_Bari_Ibnu_Hajar', 'Syarh_Shahih_Muslim_Nawawi', 'Aunul_Mabud', 'Tuhfatul_Ahwadzi', 'Hasyiatus_Sindi_Nasai', 'Hasyiatus_Sindi_Ibnu_Majah', 'Tamhid_Ibnu_Abdil_Barr', 'Mirqatul_Mafatih_Ali_Al_Qari', 'Syarah_Arbain_Nawawi_Ibnu_Daqiq', 'Penjelasan_Hadis_Pilihan', 'Faidhul_Qadir', 'Mustadrak_Hakim_Arab', 'Silsilah_Shahihah_Albani'];

    /**
     * Mengambil nama-nama kitab MATAN yang TELAH diterjemahkan
     */
    public static function ambilMatanTerjemah()
    {
        return self::MATAN_TERJEMAH;
    }
    
    /**
     * Mengambil nama-nama kitab MATAN yang BELUM diterjemahkan
     */
    public static function ambilMatanArab()
    {
        return self::MATAN_ARAB;
    }

    /**
     * Mengambil nama-nama kitab SYARAH yang BELUM diterjemahkan
     */
    public static function ambilSyarahArab()
    {
        return self::SYARAH_ARAB;
    }
}