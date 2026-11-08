<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Seeder;

/**
 * Semua lagu dari slide ibadah Minggu I, II, III, Kunci Bulan, dan Minggu V
 * (GPI Papua Jemaat Bethel Fakfak), LENGKAP semua baitnya.
 *
 * Sumber lirik lengkap:
 *  - KJ, NKB, PKJ : dataset github.com/tobiasMs/kidung-jemaat
 *  - NR           : alkitab.app (Mazmur & Nyanyian Rohani Buku 2)
 *  - DSL 118      : alkitab.app NKI 252 (lagu yang sama, "Let Him In")
 *  - DSL 186      : tidak ditemukan di internet: hanya bait 1 dari slide
 *  - DSL 7/109/112/136/139/181/188, GPM 1/65, lagu "Lainnya": tidak ditemukan di
 *                   internet: bait sesuai tata ibadah November 2026
 *
 * Aman dijalankan berulang:
 *  - lagu baru ditambahkan;
 *  - lagu yang sudah ada tetapi baitnya lebih sedikit (mis. hasil seeder lama
 *    yang hanya berisi bait di slide) DILENGKAPI; lagu yang sudah lengkap atau
 *    sudah diubah lebih panjang oleh pengguna tidak disentuh.
 */
class LaguIbadahMingguSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->songs() as [$book, $number, $title, $verses, $chorus]) {
            $data = Song::normalizeVerses(array_values($verses), array_keys($verses));
            $song = Song::where('book', $book)->where('number', $number)->first();

            if (!$song) {
                Song::create($data + compact('book', 'number', 'title', 'chorus'));
                continue;
            }

            if (count($song->verses ?? []) < count($data['verses'])) {
                $song->update($data + ['chorus' => $song->chorus ?: $chorus]);
            }
        }

        // Lagu yang liriknya hanya dari buletin (tidak ditemukan di internet) ditandai
        // "belum lengkap": selama baitnya belum ditambah pengguna.
        if (\Illuminate\Support\Facades\Schema::hasColumn('songs', 'is_incomplete')) {
            $seeded = collect($this->songs())->keyBy(fn ($s) => $s[0] . ' ' . $s[1]);
            foreach (self::INCOMPLETE as [$book, $number]) {
                $song = Song::where('book', $book)->where('number', $number)->first();
                $seededCount = count($seeded["$book $number"][3] ?? []);
                if ($song && count($song->verses ?? []) <= $seededCount) {
                    $song->forceFill(['is_incomplete' => true])->save();
                }
            }
        }
    }

    public const INCOMPLETE = [
        ['DSL', '186'], ['DSL', '7'], ['DSL', '109'], ['DSL', '112'], ['DSL', '136'],
        ['DSL', '139'], ['DSL', '181'], ['DSL', '188'], ['GPM', '1'],
    ];

    /** @return array<int, array{0: string, 1: string, 2: string, 3: array<int, string>, 4: ?string}> */
    public function songs(): array
    {
        return [
            ['KJ', '14', 'Muliakan Tuhan Allah', [
                1 => "Muliakan Tuhan Allah, muliakan Tuhan Allah,\nmuliakan pimpinanNya dalam kasih sayangNya.",
                2 => "Kami datang kepadaMu, kami datang kepadaMu,\nbersyukur sebulat hati, kar'na kasihMu besar.",
                3 => "Kau dekat dengan firmanMu, kau dekat dengan firmanMu,\nYa, berfirmanlah, ya Tuhan, kami siap mendengar.",
            ], null],
            ['KJ', '21', 'Hari Minggu, Hari Yang Mulia', [
                1 => "Hari Minggu, hari yang mulia, itu hari Tuhanku.\nIa bawa rasa bahagia masuk dalam hatiku.",
                2 => "Hari Minggu hari istirahat bagi badan yang letih.\nFirman Tuhan turun bawa nikmat untuk hati yang sedih.",
            ], "Hari Minggu, hari Tuhan, hari suci dan teduh.\nHari Minggu, hari Tuhan, hari suci dan teduh."],
            ['KJ', '27', 'Meski Tak Layak Diriku', [
                1 => "Meski tak layak diriku, tetapi kar'na darahMu\ndan kar'na Kau memanggilku, 'ku datang, Yesus, padaMu.",
                2 => "Sebagaimana adanya jiwaku sungguh bercela,\ndarahMulah pembasuhnya; 'ku datang, Tuhan, padaMu.",
                3 => "Terombang-ambing, berkeluh, gentar di kancah kemelut,\nya Anakdomba Allahku, ku datang kini padaMu.",
                4 => "Sebagaimana adaku celaka, buta dan kelu;\nsegala apa yang perlu 'ku dapat dalam diriMu.",
                5 => "Sebagaimana janjiMu menyambut dan membasuhku,\nya Anakdomba yang kudus, 'ku datang kini padaMu.",
            ], null],
            ['KJ', '29', 'Di Muka Tuhan Yesus', [
                1 => "Di muka Tuhan Yesus betapa hina diriku.\nKubawa dosa-dosaku di muka Tuhan Yesus.",
                2 => "Di muka Tuhan Yesus tersungkur kar'na dosaku,\nkubuka kerinduanku di muka Tuhan Yesus.",
                3 => "Di muka Tuhan Yesus 'ku insaf akan salahku;\nbertobat kini hatiku di muka Tuhan Yesus.",
                4 => "Di muka Tuhan Yesus kudapat kasih sayang-Nya;\nhatiku pasrah berserah di muka Tuhan Yesus.",
            ], null],
            ['KJ', '49', 'Firman Allah Jayalah', [
                1 => "Firman Allah jayalah sampai ujung dunia:\nkita pun dipanggilNya untuk hidup yang baka.",
                2 => "Firman Khalik semesta yang mengasuh makhlukNya,\nbimbinganNya pun tepat bagi orang tersesat.",
                3 => "Firman Put'ra mulia menyampaikan kurnia:\noleh darah yang kudus dosa kita ditebus.",
                4 => "Firman kesaksian Roh pandu s'lamat yang teguh:\nkita mengikutiNya dalam karsa dan kerja.",
                5 => "Firman Hidup yang kudus, berkuasalah terus\nhingga dunia yang gelap lihat fajar gemerlap.",
                6 => "Umat Tuhan, bangunlah, masuk ladang dunia!\nBanyaklah tuaiannya, tapi kurang pekerja.",
                7 => "Tuhan, untuk panenMu semangatkan hambaMu;\nbiar isi dunia sambut sinarMu seg'ra.",
            ], null],
            ['KJ', '348', 'Anug\'rah Tuhan kita, Yesus Kristus', [
                1 => "Anug'rah Tuhan kita, Yesus Kristus, pengasihan Allah,\npersekutuan dalam Roh Kudus kiranya menyertai kita. Amin.",
            ], null],
            ['KJ', '356', 'Tinggallah dalam Yesus', [
                1 => "Tinggallah dalam Yesus, jadilah muridNya,\nb'lajarlah Firman Tuhan, taat kepadaNya.\nTinggallah dalam Yesus, Andalkan kuasaNya.\nDialah Pokok yang benar, kitalah rantingNya.",
                2 => "Kita sebagai ranting pasti berbuahlah,\nasal dengan setia tinggal di dalamNya.\nTinggallah dalam Yesus, muliakan namaNya:\nhidup berlimpah kurnia hanya di dalamNya!",
            ], null],
            ['KJ', '393', 'Tuhan, Betapa Banyaknya', [
                1 => "Tuhan, betapa banyaknya berkat yang Kauberi,\nteristimewa rahmatMu dan hidup abadi.",
                2 => "Sanak saudara dan teman Kaub'ri kepadaku;\nberkat terindah ialah 'ku jadi anakMu.",
                3 => "Setiap hari rahmatMu tiada putusnya:\nhendak kupuji namaMu tetap selamanya.",
            ], "T'rima kasih, ya Tuhanku atas keselamatanku!\nPadaku telah Kauberi hidup bahagia abadi."],
            ['KJ', '425', 'Berkumandang Suara dari Seberang', [
                1 => "Berkumandang suara dari seberang,\n\"Kirimlah cahyamu!\"\nBanyak jiwa dalam dosa mengerang,\n\"Kirimlah cahyamu!\"",
                2 => "Kita t'lah dengar jeritan dari jauh,\n\"Kirimlah cahyamu!\"\nBantuanmu b'rikan, janganlah jemu,\n\"Kirimlah cahyamu!\"",
                3 => "Jangan kita tinggal diam mendengar:\n\"Kirimlah cahyamu!\"\nInjil Tuhan haruslah kita sebar,\n\"Kirimlah cahyamu!\"",
            ], "Kirimlah pelita Injili menyentak yang terlelap.\nKirimlah pelita Injili menyentak yang terlelap."],
            ['KJ', '450', 'Hidup Kita yang Benar', [
                1 => "Hidup kita yang benar haruslah mengucap syukur.\nDalam Kristus bergemar; janganlah tekebur.",
                2 => "Biar badai menyerang, biar ombak menerjang,\naku akan bersyukur kepada Tuhanku.",
                3 => "Apa arti hidupmu? Bukankah ungkapan syukur,\nkar'na Kristus, Penebus, berkurban bagimu!",
                4 => "Bertekun bersyukurlah hingga suaraNya kaudengar:\n\"Sungguh indah anakKu, ungkapan syukurmu.\"",
                5 => "Tuhan Yesus, tolonglah, sempurnakan syukurku.\nRoh Kudus berkuasalah di dalam hidupku!",
            ], "Dalam susah pun senang; dalam segala hal\nAku bermazmur dan ucap syukur; itu kehendakNya!"],
            ['KJ', '452', 'Naikkan Doa Tak Enggan', [
                1 => "Naikkan doa tak enggan; Yesus pasti berkenan.\nDoa itu p'rintahNya: Ia tak menolaknya.",
                2 => "Maharaja Dialah, tak terbatas kuasaNya:\nminta saja apapun; pasti sanggup Tuhanmu!",
                3 => "Dosa sarat menekan; Tuhan, angkatlah beban\ndan sucikan diriku oleh curah darahMu!",
                4 => "B'rikanlah sentosaMu dan kuasai diriku.\nPenebusku, Kau berhak jadi Rajaku tetap.",
                5 => "Biar oleh kasihMu bersemangat langkahku:\nKau Pembimbing dan Teman hingga akhir yang terang.",
                6 => "JalanMu tunjukkanlah, jiwaku kuatkanlah,\nhingga hidup matiku memenuhi maksudMu.",
            ], null],
            ['NKB', '2', 'Hai Mari Sembah', [
                1 => "Hai mari sembah Yang Maha besar,\nnyanyikan syukur dengan bergemar.\nPerisai umatNya Yang Maha Esa,\nmulia namaNya, takhtaNya megah!",
                2 => "Hai masyhurkanlah keagunganNya;\ncahaya terang itu jubahNya\nGemuruh suaraNya di awan kelam;\nberjalanlah Dia di badai kencang.",
                3 => "Buana penuh mujizat ajaib,\nya Khalik, Engkau membuatnya baik.\nEngkau memisahkan daratan dan laut\ndengan kuasa firman: besarlah Engkau!",
                4 => "PengasuhanMu betapa megah;\nudara dan t'rang menyatakannya,\nembun bertetesan dan hujan sejuk,\nlembah maupun bukit cermin kasihMu!",
                5 => "UmatMu lemah dan dari debu,\ntetap memegang janjiMu teguh.\nKasih setiaMu berlimpah terus,\nya Khalik, Pembela dan Kawan kudus!",
                6 => "Ya Mahabesar, kekal kasihMu;\nmalaikat memb'ri pujian merdu,\npun kami makhlukMu kecil dan lemah,\nmengangkat pujian serta menyembah.",
            ], null],
            ['NKB', '5', 'Seluruh Dunia, Hai, Nyanyikanlah', [
                1 => "Seluruh dunia, hai nyanyikanlah: 'Kau Allahku!\nSetinggi langitlah pujian bergema;\npun bumi tak lelah menaikkan sembah.\nSeluruh dunia, hai nyanyikanlah: 'Kau Allahku!",
                2 => "Seluruh dunia, hai nyanyikanlah: 'Kau Rajaku!\nGereja bermazmur penuh dengan syukur,\nkhususnya hatimu memuji tak jemu.\nSeluruh dunia, hai nyanyikanlah: 'Kau Rajaku!",
            ], null],
            ['NKB', '119', 'Nyanyikan Lagi Bagiku', [
                1 => "Nyanyikan lagi bagiku, Firman Kehidupan.\nSungguh mulia dan merdu, Firman Kehidupan.\nFirman yang terindah, ajarku setia.",
                2 => "Kristus memberi dunia, Firman Kehidupan.\nHai pendosa dengarkanlah, Firman Kehidupan.\nBagimu anug'rah, diberi berlimpah.",
                3 => "Manislah gema InjilNya, Firman Kehidupan.\nDamai, ampunan dib'riNya, Firman Kehidupan.\nYesus Jurus'lamat, sumber s'gala rahmat.",
            ], "Indah benar, ajaib benar, Firman Kehidupan.\nIndah benar, ajaib benar, Firman Kehidupan."],
            ['NKB', '141', 'Kasihku Padamu Tambahkanlah', [
                1 => "Kasihku padaMu tambahkanlah!\nYa Kristus Tuhanku, o, dengarlah!\n'Ku mohon tak henti: Tambahkan kasihku,\nmakin besar kepadaMu!",
                2 => "Dahulu dunia andalanku,\nkini Engkau, Tuhan, harapanku.\nInilah doaku: Tambahkan kasihku,\nmakin besar kepadaMu!",
                3 => "Walaupun badai k'ras memukulku,\nnamun penghiburku malak kudus.\nInilah laguku: Tambahkan kasihku,\nmakin besar kepadaMu!",
                4 => "Sungguh pun ajalku t'lah menjelang\npujian bagiMu tak berselang.\nTak lain doaku: Tambahkan kasihku,\nmakin besar kepadaMu!",
            ], null],
            ['NKB', '189', 'Pegang Tanganku', [
                1 => "'Ku ingin selalu dekat padaMu,\nikut 'Kau Tuhan tiada jemu;\nBila 'Kau pimpin kehidupanku,\ntak 'kan 'ku ragu, tetap langkahku.",
                2 => "Gelap perjalanan yang aku tempuh,\nnamun cerah berseri jiwaku;\nsusah kecewa di dunia fana,\ndamai menanti di sorga baka.",
            ], "O Jurus'lamat, pegang tanganku,\nBimbinganMu yang aku perlu;\nB'ri pertolongan dan kuasaMu,\no Tuhan Yesus, pegang tanganku."],
            ['NKB', '208', 'Tabur Waktu Pagi', [
                1 => "Tabur waktu pagi, tabur benih kasih,\ntabur waktu siang t'rus sampai senja.\nNantikan tuaian pada musim panen,\nkita 'kan bersuka bawa berkasNya.",
                2 => "Di terik sang surya, di g'lap bayang awan\nkita pun menabur, riang bekerja.\nNanti panen tiba, tugas akan usai,\nkita 'kan bersuka bawa berkasNya.",
                3 => "Maju walau sukar, tabur bagi Tuhan,\nbiar jiwa raga susah dan lelah.\nSampai akhir nanti kita disambutNya,\nkita 'kan bersuka bawa berkasNya.",
            ], "Bawa berkasNya masuk lumbungNya,\nkita 'kan bersuka bawa berkasNya.\nBawa berkasNya masuk lumbungNya,\nkita 'kan bersuka bawa berkasNya."],
            ['NKB', '210', '\'Ku Utus \'Kau', [
                1 => "'Ku utus 'kau mengabdi tanpa pamrih,\nberkarya t'rus dengan hati teguh,\nmeski dihina dan menanggung duka;\n'Ku utus 'kau mengabdi bagiKu.",
                2 => "'Ku utus 'kau membalut yang terluka,\nmenolong jiwa sarat berkeluh,\nmenanggung susah dan derita dunia.\n'Ku utus 'kau berkurban bagiKu.",
                3 => "'Ku utus 'kau kepada yang tersisih,\nyang hatinya diliputi sendu,\nsebatang kara, tanpa handai taulan.\n'Ku utus 'kau membagi kasihKu.",
                4 => "'Ku utus 'kau, tinggalkan ambisimu,\npadamkanlah segala nafsumu,\nnamun berkaryalah dengan sesama.\n'Ku utus 'kau bersatulah teguh.",
                5 => "'Ku utus 'kau mencari sesamamu\nyang hatinya tegar terbelenggu,\n'tuk menyelami karya di Kalvari.\n'Ku utus 'kau mengiring langkahKu.\nKar'na Bapa mengutusku, 'Ku utus 'kau",
            ], null],
            ['NKB', '213', 'Kita Sudah Ditebus Olehnya', [
                1 => "Kita sudah ditebus olehNya,\nkini layanilah Mukhalismu.\nMaju t'rus dan kibarkan panjiNya,\nsanjung Rajamu!",
                2 => "Waktu suka atau waktu duka,\nwalau badai datang melandamu;\nJanganlah jemu melayaniNya,\nsanjung Rajamu!",
                3 => "Dan layanilah dengan setia,\njangan dosa sampai menghalangmu.\nJunjunglah terus kebenaranNya,\nsanjung Rajamu!",
            ], "Mari bawa padaNya segenap talentamu\nserta hidup mengikuti firmanNya!\nTaat dan setialah walau sukar jalanmu,\nhidup kudus agar kasihNya pun nyatalah!"],
            ['NR', '3', 'Hormat Bagi Allah Bapa, Hormat Bagi Anak-Nya', [
                1 => "Hormat bagi Allah Bapa,\nhormat bagi Anak-Nya,\nhormat bagi Roh Penghibur,\nketiganya Yang Esa.\nHaleluya, haleluya,\nketiganya yang Esa.",
                2 => "Hormat bagi Raja sorga,\nTuhan kaum manusia.\nHormat bagi Raja G'reja\ndi seluruh dunia.\nHaleluya, haleluya,\ndi seluruh dunia.",
                3 => "Haleluya, persembahan\nbala kerajaan-Mu\ndiletakkan siang-malam\ndi hadapan takhta-Mu.\nHaleluya, haleluya.\ndi hadapan takhta-Mu.",
            ], null],
            ['NR', '5', 'Hai Makhluk Allah-hu Besar', [
                1 => "Hai makhluk Allah Hu Besar,\nbernyanyilah dan bergemar,\nhaleluya, haleluya!\nNyanyian baru angkatlah,\npujaan dari dunia,\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
                2 => "Hai surya terang benderang,\nyang mas cahayamu cerlang,\nhaleluya, haleluya.\ndan bulan yang mencurahkan\nt'rang perak berpenghiburan,\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
                3 => "Hai angin yang serbu serang,\ndan hujan seraman bendang,\nhaleluya, haleluya,\nkau dinihari yang teduh,\npetang yang ungu tejamu,\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
                4 => "hai sungai hablur yang jernih\ndan tanah yang b'ri rezeki,\nhaleluya, haleluya,\nsamud'ra, pulau dan selat\ndan hutan-rimba yang lebat,\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
                5 => "Segala hewan yang terbang,\nberjalan kaki dan ber'nang,\nhaleluya, haleluya,\nkembang dan buah-buah pun\ndi taman-taman dan kebun,\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
                6 => "Manusia, hai turutlah\nmemuji pengasihan-Nya\nhaleluya, haleluya,\nsegala kaum di dunia\nsebagai anak-anak-Nya\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
                7 => "Hai mahluk-Nya sekalian\nberhimpun dan bernyanyian,\nhaleluya, haleluya,\ndan puji Allah yang kudus\nsekarang dan selalu t'rus,\npuji Allah sedekala,\nhaleluya, haleluya, haleluya!",
            ], null],
            ['NR', '79', 'Tuhanlah kekal Raja Hati Kami', [
                1 => "Tuhanlah kekal Raja hati kami; kami tak sesal\nikut firman-Mu beriman teguh pada jalan damai.",
                2 => "Bimbing kami pun, agar di gangguan,\nkami bertekun, sampai akhirnya\ndi terang baka, kami pandang Tuhan.",
            ], null],
            ['NR', '132', 'Ya Tuhan, Murah-Mu Baka', [
                1 => "Ya Tuhan, murah-Mu baka.\nDi sorga dan di dunia\nberlimpahlah anugerah\nyang Kauberi.",
                2 => "Ya Tuhan, kami tidak laik.\nTerimalah syukur yang naik,\nkar'na karunia yang baik,\nyang kauberi.",
                3 => "Kar'na segala tumbuhan,\ndan berkat buah-buahan,\ndan makanan dan minuman,\nyang Kauberi.",
                4 => "Kar'na kesukaan kerja,\ndan rumah dengan berkatnya,\nbahagia sejahtera,\nyang Kauberi.",
                5 => "Tidak terbalas kasihan,\nyang memberi keampunan,\nAnugerah kes'lamatan,\nyang Kauberi.",
                6 => "Dan Anak-Mu sendirilah.\nyang menebus manusia\ndengan salib dan mati-Nya\nyang Kauberi.",
                7 => "Terima hormat dan sembah,\nterima hidup dan kerja\ndengan sekalian benda,\nyang Kauberi.",
            ], null],
            ['NR', '154', 'Jikalau Jiwaku Berdoa', [
                1 => "Jika jiwaku berdoa\nkepada-Mu, Tuhanku,\najarl'aku t'rima saja\npemberian tangan-Mu.\nDan mengaku s'perti Yesus\ndi depan sengsara-Nya:\n\"Jangan kehendakku Bapa,\nkehendak-Mu jadilah.\"",
                2 => "Apa juga yang kautimbang\nbaik untuk hidupku,\nb'rilah aku pun setuju\ndengan maksud hikmat-Mu.\nDan mengerti dan percaya,\nbiar hatiku lemah:\n\"Jangan kehendakku, Bapa,\nkehendak-Mu jadilah.\"",
                3 => "Aku cari penghiburan\nhanya dalam kasih-Mu\nDalam susah Engkau saja\nperlindungan hidupku.\n'Ku mengaku, s'perti Yesus\ndi depan sengsara-Nya:\n\"Jangan kehendakku, Bapa,\nkehendak-Mu jadilah.\"",
            ], null],
            ['NR', '165', 'Yesus Memesan: Dalam Malam G\'lap', [
                1 => "Yesus memesan: dalam malam g'lap\nhendak kita jadi lilin yang gerlap.\nAkan hormat Tuhan bercahayalah\nanak masing-masing di sudutnya.",
                2 => "Yesus menegur: Aku tahu tetap,\nlilin mana t'rang dan mana yang gelap;\nkalau sudah suram atau t'rang cerah\nanak masing-masing di sudutnya.",
                3 => "Yesus memesan: lihat g'lap benar\nbumi yang berdosa, susahnya besar,\nJadi lilin bagus, bercahayalah\nanak masing-masing di sudutnya.",
            ], null],
            ['NR', '189', 'Serikat Persaudaraan', [
                1 => "Serikat persaudaraan, berdirilah teguh!\nSempurnakan persatuan di dalam Tuhanmu.\nBersama-sama majulah, dikuatkan iman,\nBerdamai bersejahtera, dengan pengasihan.",
                2 => "Serikatmu tetap teguh di atas alasan,\nyaitu satu Tuhanmu dan satulah iman,\ndan satu juga baptisan dan Bapa satulah,\nyang olehmu sekalian dipuji, disembah.",
                3 => "Dan masing-masing kamu pun dib'ri anugerah,\nsupaya kamu bertekun dan rajin bekerja.\nHendaklah hatimu rendah, tahu: Tuhan memesan.\nJemaat menurut firman-Nya berkasih-kasihan.",
            ], null],
            ['PKJ', '2', 'Mulia, Mulia NamaNya', [
                1 => "Mulia, mulia namaNya.\nBagi Yesus kemuliaan, puji, sembah!\nMulia, kekuasaanNya\nMemb'ri berkat bagi jemaat,\nBersyukurlah!",
            ], "Pujilah, tinggikanlah Rajamu Yesus.\nDialah selamanya Sang Raja benar!\nMulia, mulia namaNya!\nSang Penebus, Mahakudus, Mahabesar."],
            ['PKJ', '13', 'Kita Masuk Rumahnya', [
                1 => "Kita masuk rumahNya, berkumpul menyembah kepadaNya.\nKita masuk rumahNya, berkumpul menyembah kepadaNya.\nKita masuk rumahNya, berkumpul menyembah kepada Kristus,\nmenyembah Kristus Tuhan.",
                2 => "Lupakanlah dirimu, arahkan hatimu kepadaNya.\nLupakanlah dirimu, arahkan hatimu kepadaNya.\nLupakanlah dirimu, arahkan hatimu kepada Kristus,\nmenyembah Kristus Tuhan.",
                3 => "Muliakan namaNya dan angkat tanganmu kepadaNya.\nMuliakan namaNya dan angkat tanganmu kepadaNya.\nMuliakan namaNya dan angkat tanganmu kepada Kristus,\nmenyembah Kristus Tuhan.",
            ], null],
            ['PKJ', '15', 'Kusiapkan Hatiku, Tuhan', [
                1 => "Kusiapkan hatiku, Tuhan, menyambut firmanMu, saat ini.\nAku sujud menyembah Engkau dalam hadiratMu, saat ini.\nCurahkanlah pengurapanMu kepada umatMu saat ini.\nKusiapkan hatiku, Tuhan, mendengar firmanMu.\nFirmanMu, Tuhan. tiada berubah,\nSejak semulanya dan s'lama-lamanya tiada berubah.\nFirmanMu, Tuhan, penolong hidupku,\nKusiapkan hatiku, Tuhan, menyambut firmanMu.",
            ], null],
            ['PKJ', '43', 'Tuhan, Kami Berlumuran Dosa', [
                1 => "Tuhan, kami berlumuran dosa.\nTuhan, sudilah ampuni kami.",
                2 => "Tuhan, harta kami musnah sudah.\nTuhan, hati masih milik kami.",
                3 => "Tuhan, sudi ampuni mereka.\nTuhan, Kau yang tahu perbuatannya.",
                4 => "Tuhan, kami berlumuran dosa.\nTuhan, sudilah ampuni kami.",
            ], null],
            ['PKJ', '145', 'Aku Melangkah Ke Rumah Tuhan', [
                1 => "Aku melangkah ke rumah Tuhan\ndalam iringan umat Kristus,\n'Ku mensyukuri berkat, rahmatMu,\nyang sungguh banyak kepadaku.",
                2 => "Aku bersujud di hadiratMu;\nkubawakan persembahanku.\nKuhaturkan kepadaMu, Tuhan,\nkiranya Kau menerimanya.",
                3 => "Aku serahkan hasil karyaku,\nharta, tenaga, serta waktu.\nDari tanganMu jua asalnya,\nbagi kemuliaanMu, Bapa.",
                4 => "Aku berlutut di hadapanMu,\naku serahkan jiwa raga.\nKarna Engkau menebus hidupku:\nutuslah aku jadi saksi.",
            ], "Aku bersyukur kepadaMu, Tuhan,\natas berkatMu yang Kaulimpahkan.\nAku bersyukur kepadaMu, Tuhan,\natas kasihMu yang Kaucurahkan."],
            ['PKJ', '183', 'Mari Sebarkan Injil', [
                1 => "Mari sebarkan Injil ke seluruh dunia;\nmari kabarkan nama Yesus Mahamulia.\nBesar kasihNya bagiku dan bagi kita\nsemua, Dia mati bagi umat manusia.",
                2 => "Bukalah hatimu, mari terima Dia.\nBuanglah congkakmu dan tetaplah percaya.\nDekaplah Yesus Tuhanmu agar hidupmu\nberseri; s'gala puji bagi Tuhan diberi.",
            ], "Mari sebarkan, hai mari wartakan;\nkeselamatan oleh Tuhan tiada terperi,\ndan teruskan serta beritakan\nrahmat Ilahi dalam Yesus diberi."],
            ['PKJ', '274', 'Pakailah Waktu Anug\'rah Tuhanmu', [
                1 => "Pakailah waktu anug'rah Tuhanmu, hidupmu singkat bagaikan kembang.\nMana benda yang kekal dihidupmu? Hanyalah kasih tak akan lekang.",
                2 => "Jangan menyia-nyiakan waktumu, hibur dan tolonglah yang berkeluh.\nBiarlah lampumu t'rus bercahaya, muliakanlah Tuhan dihidupmu.",
                3 => "Karya jerihmu demi Tuhan Yesus 'kan dihargai benar olehNya.\nKasih yang sudah kau tabur di dunia nanti kau tuai di sorga mulia.",
            ], "Tiada yang baka didalam dunia, s'gala yang indah pun akan lenyap.\nNamun kasihmu demi Tuhan Yesus sungguh bernilai dan tinggal tetap."],
            ['PKJ', '290', 'Kurangkai Nada Menjadi Lagu', [
                1 => "Kurangkai nada menjadi lagu.\nKusenandungkan di hadapanMu.\nDengarkan, Tuhan, seruan hatiku,\ndengarkan, Tuhan, seruan hatiku,\ndengar seruan hatiku.",
                2 => "Kususun kata dalam kalbuku.\nKuucap doa puji syukurku.\nTerima, Tuhan, jeritan jiwaku,\nterima Tuhan, jeritan jiwaku,\nterima jeritan jiwaku.",
                3 => "Kusebarkan firman di ladangMu.\nKuserukan pujian bagimu.\nBerkati, Tuhan, semua karyaku,\nberkati Tuhan, semua karyaku,\nberkati semua karyaku.",
            ], null],
            ['KJ', '50', 'SabdaMu Abadi', [
                1 => "SabdaMu abadi, suluh langkah kami.\nYang mengikutinya hidup sukacita.",
                2 => "Di tengah ancaman sabdaMu harapan,\nsumber penghiburan, kabar kes'lamatan.",
                3 => "Dalam badai topan sabdaMu pedoman;\ndalam kekelaman jalan kami aman.",
                4 => "SabdaMu semua harta tak terduga,\nsungguh memberkati yang membuka hati.",
                5 => "Sabda pengasihan dalam hidup insan,\ncah'ya pengharapan bila ajal datang.",
                6 => "Tolong, agar kami rajin mendalami\nlalu melakukan sabdaMu, ya Tuhan!",
            ], null],
            ['DSL', '118', 'Sambutlah', [
                1 => "Tuhan datang padamu, sambutlah!\nJangan buang Tuhanmu, sambutlah!",
                2 => "Buka pintu hatimu, sambutlah!\nJangan tangguh waktumu, sambutlah!",
                3 => "Ia hendak b'ri khalas, sambutlah!\nDia s'laku pelepas, sambutlah!",
                4 => "Kalau suka dapat s'nang, sambutlah!\nK'lak kau masuk dalam t'rang, sambutlah!",
            ], "Ia pun berdirilah\ndan ketuk tak b'rentinya,\nharap sampai maksud-Nya.\nSambutlah!"],
            ['DSL', '186', 'Gembala dan Pemimpin', [
                1 => "Tuhan, tilik kami, dengarlah doa;\npimpin dengan damai serta hantarlah.\nBiar salut kami dengan ni'matMu,\nagar ramai-ramai puji namaMu.",
            ], null],

            // ---------------------------------------------------------------
            // Tata ibadah November 2026: Minggu Anak (8/11), Minggu III (15/11),
            // Minggu IV (22/11), Adven I (29/11)
            // ---------------------------------------------------------------

            ['KJ', '3', 'Kami Puji dengan Riang', [
                1 => "Kami puji dengan riang Dikau, Allah yang besar;\nbagai bunga t'rima siang, hati kami pun mekar.\nKabut dosa dan derita, kebimbangan, t'lah lenyap.\nSumber suka yang abadi, b'ri sinarMu menyerap.",
                2 => "Kau memb'ri, Kau mengampuni, Kau limpahkan rahmatMu,\nsumber air hidup ria, lautan kasih dan restu.\nYang mau hidup dalam kasih Kau jadikan milikMu\nagar kami menyayangi, meneladan kasihMu.",
                3 => "Semuanya yang Kaucipta memantulkan sinarMu.\nPara malak, tata surya naikkan puji bagiMu.\nPadang, hutan dan samud'ra, bukit, gunung dan lembah,\nmargasatwa bergembira 'ngajak kami pun serta.",
                4 => "Mari kita pun memuji dengan suara menggegap,\nmenyanyikan kuasa kasih yang teguh serta tetap.\nKita maju dan bernyanyi, jaya walau diserang,\nikut mengagungkan kasih dalam lagu pemenang.",
            ], null],
            ['KJ', '26', 'Mampirlah, Dengar Doaku', [
                1 => "Mampirlah, dengar doaku, Yesus Penebus;\norang lain Kauhampiri, jangan jalan t'rus.",
                2 => "Di hadapan takhta rahmat aku menyembah,\ntunduk dalam penyesalan. Tuhan, tolonglah!",
                3 => "Ini saja andalanku: jasa kurbanMu.\nHatiku yang hancur luluh buatlah sembuh.",
                4 => "Kaulah Sumber penghiburan, Raja hidupku.\nBaik di bumi baik di sorga, siapa bandingMu?",
            ], "Yesus, Tuhan, dengar doaku;\norang lain Kauhampiri, jangan jalan t'rus."],
            ['KJ', '28', 'Ya Yesus, Tolonglah', [
                1 => "Ya Yesus, tolonglah, hapuskan dosaku\ndan dari nafsu dunia lepaskan hambaMu.",
                2 => "Ya Yesus, dengarlah seruan hatiku,\nlengkapi aku yang lemah, sebagai laskarMu.",
                3 => "Ya Yesus, yang menang, sertai hambaMu,\nb'ri di sengsara dan perang percaya yang teguh.",
                4 => "Ya Yesus, pimpinlah, tetaplah Kau dekat,\nsupaya ke neg'ri baka jalanku tak sesat.",
                5 => "Ya Yesus, lihatlah serangan seteru,\nlumpuhkanlah senjatanya dengan kuasaMu.",
                6 => "Ya Yesus Penebus, berilah akhirnya\nkesukaanMu yang kudus di negeri baka.",
            ], null],
            ['KJ', '56', 'Datanglah Kepadaku, ya Roh Kudus', [
                1 => "Datanglah kepadaku, ya Roh Kudus;\ndatanglah kepadaku, ya Roh Kudus.\nB'rilah api dalam hati;\nhidupku penuhilah, ya Roh Kudus.",
            ], null],
            ['KJ', '81', 'O, Datanglah, Imanuel', [
                1 => "O, datanglah, Imanuel, tebus umatMu Israel\nyang dalam berkeluh kesah menantikan Penolongnya.\nBersoraklah, hai Israel, menyambut Sang Imanuel!",
                2 => "O, datang, Tunas Isai, patahkan belenggu pedih\ndan umatMu lepaskanlah dari lembah sengsaranya.\nBersoraklah, hai Israel, menyambut Sang Imanuel!",
                3 => "O Surya Pagi, datanglah dan jiwa kami hiburlah;\nhalaukanlah gelap seram bayangan maut yang kejam.\nBersoraklah, hai Israel, menyambut Sang Imanuel!",
                4 => "O Kunci Daud, datanglah, gapura sorga bukalah;\ntutuplah jalan seteru, supaya s'lamat umatMu.\nBersoraklah, hai Israel, menyambut Sang Imanuel!",
                5 => "O Tuhan Allah, datanglah, firmanMu berkuasalah,\nseperti waktu Kauberi di atas puncak Sinai.\nBersoraklah, hai Israel, menyambut Sang Imanuel!",
            ], null],
            ['KJ', '85', 'Kusongsong Bagaimana', [
                1 => "Kusongsong bagaimana, ya Yesus, datangMu?\nEngkau Terang buana, Kau Surya hidupku!\nKiranya Kau sendiri Penyuluh jalanku,\nsupaya kuyakini tujuan janjiMu.",
                2 => "Kaum Sion menaburkan kembang di jalanMu;\n'ku ikut mengelukan Dikau di hatiku.\nKunyanyi Hosiana, ya Raja, tolonglah!\nPadaMulah kiranya hambaMu berserah.",
                3 => "Betapa Kau berkorban hendak menghiburku\ndi kala 'ku di jurang sengsara kemelut.\nKau datang, Jurus'lamat, dengan sejahtera:\nkeluh-kesahku tamat dan hatiku cerah.",
                4 => "Di saat 'ku terpasung, Kau membebaskanku;\nsegala aib dan malu terhapus olehMu.\nPadaku Kautambahi mahkota mulia,\nbahagia abadi, pusaka yang baka!",
                5 => "Sebabnya Kautinggalkan takhtaMu yang megah,\nkasihMulah belaka terhadap dunia.\nKau rela menderita sengsara dan cela,\nsegala dukacita dengan manusia.",
                6 => "Hai insan yang berduka, tabahkan hatimu,\ndan pandanglah ke muka. Hai kamu yang lesu:\ntelah di ambang pintu Penolong mulia;\ndengan harapan itu jiwamu pun lega.",
                7 => "Tak usah cari jalan, tak usah berlelah,\nbersusah siang-malam mengatur datangNya.\nSengaja Ia datang melipur laramu,\nmenaruh kasih sayang, membuka belenggu.",
                8 => "Kendati hutang dosa membuatmu gentar,\npadaNya kau sentosa, anug'rahNya besar!\nBagimu Ia datang menjadi Penebus;\nsejaht'ra kerajaan warisanmu terus!",
                9 => "Tak lagi menakutkan kuasa yang gelap:\nsemua lawan Tuhan menghilang serempak.\nSeg'ra Rajamu datang penuh karunia\nmembawa kemenangan selama-lamanya!",
                10 => "Yang datang menghakimi seisi dunia,\nrahmani dan rahimi membela umatNya.\nYa datang, Matahari, sinari umatMu;\npadaMu kami cari bahagia penuh.",
            ], null],
            ['KJ', '184', 'Yesus Sayang Padaku', [
                1 => "Yesus sayang padaku; Alkitab mengajarku.\nWalau 'ku kecil, lemah, aku ini milikNya.",
                2 => "Yesus sayang padaku, Ia mati bagiku;\ndosaku dihapusNya, sorga pun terbukalah.",
                3 => "Yesus sayang padaku, waktu sakit badanku\naku ditungguiNya dari sorga mulia.",
                4 => "Yesus sayang padaku, dan tetap bersamaku;\nnanti 'ku bersamaNya tinggal dalam rumahNya.",
            ], "Yesus Tuhanku sayang padaku;\nitu firmanNya di dalam Alkitab."],
            ['KJ', '280', 'Aku Percaya', [
                1 => "Aku percaya Allah yang kekal,\nyang oleh Sabda kita kenal:\nBapa Pencipta alam semesta,\nyang mengasihi manusia.",
                2 => "Aku percaya Put'ra TunggalNya\nyang disalibkan di Golgota,\nyang dari kubur bangkit dan menang,\nnaik ke sorga dalam terang.",
                3 => "Aku percaya pada Roh Kudus\nyang mendiami kita terus.\nAku percaya G'reja yang esa;\n'ku jadi suci di dalamnya.",
            ], null],
            ['KJ', '302', "Kub'ri Persembahan", [
                1 => "Kub'ri persembahan pada Tuhanku\nsambil puji Yesus, Jurus'lamatku.",
                2 => "Dengan sukaria kub'ri padaMu\ndan merasa kaya dalam Tuhanku.",
                3 => "Mari kawan-kawan, rela hatilah\nbawa persembahan; datanglah seg'ra.",
            ], null],
            ['KJ', '346', 'Tuhan Allah Beserta Engkau', [
                1 => "Tuhan Allah beserta engkau sampai bertemu kembali;\nkasih Kristus mengawali, Tuhan Allah beserta engkau!",
                2 => "Tuhan Allah beserta engkau, sayapNya pernaunganmu,\nsabda Kristus santapanmu, Tuhan Allah beserta engkau!",
                3 => "Tuhan Allah beserta engkau dalam susah dan keluhmu;\nrangkulanNya menghiburmu, Tuhan Allah beserta engkau!",
                4 => "Tuhan Allah beserta engkau! Panji kasih peganganmu,\nmaut pun kalah di depanmu, Tuhan Allah beserta engkau!",
            ], "Sampai bertemu, bertemu, sampai lagi kita bertemu;\nsampai bertemu, bertemu, Tuhan Allah beserta engkau!"],
            ['KJ', '424', 'Yesus Menginginkan Daku', [
                1 => "Yesus menginginkan daku bersinar bagiNya,\ndi mana pun 'ku berada, 'ku menyenangkanNya.",
                2 => "Yesus menginginkan daku menolong orang lain,\nmanis dan sopan selalu, ketika 'ku bermain.",
                3 => "'Ku mohon Yesus menolong menjaga hatiku,\nagar bersih dan bersinar meniru Tuhanku.",
                4 => "Aku pun ingin bersinar dan melayaniNya,\nhingga di sorga 'ku hidup senang bersamaNya.",
            ], "Bersinar, bersinar; itulah kehendak Yesus;\nbersinar, bersinar, aku bersinar terus."],
            ['PKJ', '7', 'Bersyukurlah pada Tuhan', [
                1 => "Bersyukurlah pada Tuhan, serukanlah namaNya!\nBernyanyilah bagi Tuhan, mari bermazmurlah!",
                2 => "Hatiku siap, ya Tuhan, bernyanyi dan bermazmur,\nkar'na Engkau Mahabaik, setia dan benar.",
                3 => "Aku hendak mengagungkan Allahku dan Rajaku,\ndan memuliakan namaNya untuk selamanya.",
            ], "Pujilah Tuhan, hai jiwaku, pujilah namaNya.\nAku hendak bernyanyi seumur hidupku."],
            ['PKJ', '11', 'Inilah Hari Minggu', [
                1 => "Inilah hari Minggu, hari Tuhan yang kudus.\nSemua orang percaya bergegas datang ke rumahNya.",
                2 => "Mari datang padaNya, menyembah serta sujud.\nMari datang bersyukur atas kasih karuniaNya.",
                3 => "Biarlah firman Tuhan membentuk hidup kita,\nmenyatakan kasihNya ke seluruh penjuru dunia.",
            ], "Dengarkanlah panggilanNya, Yesus mengundang engkau.\nMarilah, orang percaya, marilah datang seg'ra."],
            ['PKJ', '42', 'Kumohon Pengampunan', [
                1 => "Kumohon pengampunan, Tuhan,\natas segala dosa\nyang banyak kulakukan, Tuhan,\nnyata di hadapanMu.",
                2 => "Teguhkanlah imanku, Tuhan,\nbimbing tiap langkahku;\njangan biarkan aku, Tuhan,\nsesat dari jalanMu.",
                3 => "Kau gunung harapanku, Tuhan,\nsumber kehidupanku,\ntempat perlindunganku, Tuhan,\ndari jerat penggoda.",
            ], null],
            ['PKJ', '46', 'Dari Kungkungan Duka Kelam', [
                1 => "Dari kungkungan duka kelam, ya Tuhanku, 'ku datanglah,\nmasuk terangMu bebas senang, 'ku datang padaMu.\nDari beban kesakitanku masuk ke dalam kekuatanMu;\ndalam derita aku datang, ya Yesus, Tuhanku.",
                2 => "Dari dera kepapaanku, ya Tuhanku, 'ku datanglah,\ndan mengecap kekayaanMu, 'ku datang padaMu.\nDari cela keaibanku pada salibMu 'ku berteduh;\ndalam dosaku aku datang, ya Yesus, Tuhanku.",
                3 => "Dari hempasan badai deras, ya Tuhanku, 'ku datanglah,\nmasuk ke dalam bandar tenang, 'ku datang padaMu.\nDari keputusasaanku masuk ke dalam anug'rahMu;\ndalam susahku aku datang, ya Yesus, Tuhanku.",
            ], null],
            ['PKJ', '146', 'Bawa Persembahanmu', [
                1 => "Bawa persembahanmu dalam rumah Tuhan\ndengan rela hatimu, janganlah jemu.\nBawa persembahanmu, bawa dengan suka.",
                2 => "Rahmat Tuhan padamu tidak tertandingi\noleh apa saja pun dalam dunia.\nKasih dan karunia sudah kau terima.",
                3 => "Persembahkan dirimu untuk Tuhan pakai\nagar kerajaanNya makin nyatalah.\nDamai dan sejahtera diberikan Tuhan.",
            ], "Bawa persembahanmu, tanda sukacitamu.\nBawa persembahanmu, ucaplah syukur."],
            ['PKJ', '177', 'Aku Tuhan Semesta', [
                1 => "Aku Tuhan semesta, jeritanmu Kudengar.\nKau di dunia yang gelap 'Ku s'lamatkan.\nAkulah Pencipta t'rang; malam jadi benderang.\nSiapakah utusanKu membawa t'rang?",
                2 => "Aku Tuhan semesta. 'Ku menanggung sakitmu\ndan menangis kar'na kau tak mau dengar.\n'Kan Kuubah hatimu yang keras jadi lembut.\nSiapa bawa firmanKu? UtusanKu?",
                3 => "Aku Tuhan semesta. 'Ku melihat yang resah.\nOrang miskin dan lesu Aku jenguk.\nAku ingin memberi perjamuan sorgawi.\nSiapa mewartakannya? Siapakah?",
            ], "Ini aku, utus aku!\nKudengar Engkau memanggilku.\nUtus aku; tuntun aku;\n'Ku prihatin akan umatMu."],
            ['PKJ', '255', 'FirmanMu Kupegang Selalu', [
                1 => "FirmanMu kupegang selalu, saat duka saat senang.\nJalan hidup yang akan datang tangan Tuhan yang memegang.\nPencobaan menghimpit aku dan menjadi keluhanku,\nfirmanMu kupegang selalu, sayapMu tempat berteduh.\nFirmanMu, Tuhan, kupegang s'lalu. Hilanglah keraguanku!\nBila hatiku rasa susah, padaMu aku berserah,\nfirmanMu kupegang selalu, maka amanlah jiwaku.",
            ], null],
            ['PKJ', '292', 'Amin', [
                1 => "Amin, amin, a…min.",
            ], null],
            ['NKB', '201', 'Di Jalan Hidupku', [
                1 => "Di hidupku 'ku ada sobat yang setia,\nyang s'nantiasa berjalan sertaku;\nmasa gelap dibuatNya terang ceria,\nitulah Yesus, Jurus'lamatku.",
                2 => "O kasihNya besarlah tiada taranya,\ndengan rela Dia mati bagiku;\nkepadaNya 'ku s'rahkan jiwa dan raga,\nsejak itu Dia bimbingku s'lalu.",
            ], "'Ku tak cemas 'kan jalan yang naik turun,\nlewat lembah dan gunung yang terjal;\nsebab Tuhan berjalanlah di sampingku,\nmemimpinku ke neg'ri yang kekal."],

            // DSL & GPM: tidak ditemukan di internet: bait sesuai tata ibadah jemaat
            ['DSL', '7', 'Haleluya', [
                1 => "Gemarlah kan Hu, hai jemaat segnap,\nkarna ni'mat Ilahmu padamu tetap.",
            ], "Haleluya, puji Tuhan, haleluya, gemar!\nHaleluya, puji Tuhan, kasihNya besar!"],
            ['DSL', '109', 'Api Maha Tuhan', [
                1 => "Ya Yesus, t'rang dan kuatku, brikanlah, brikanlah apiMu.\nBaik masuk dalam hatiku, brikanlah, brikanlah apiMu.\nBersihkanlah daku, Almasih, supaya hati yang keji\nmenjadi suci dan jernih; brikanlah, brikanlah apiMu.",
                2 => "Hai, jika hilang harapmu, mintalah, mintalah apinya.\nYang menggerakkan hatimu, itulah, itulah apinya.\nSaudara, biar bangunlah, sebagaimana darahnya\nmenyucikan, begitulah ia bri, ia bri apinya.",
            ], null],
            ['DSL', '112', 'Waktu Senang', [
                1 => "Hari terang, waktu senang, tatkala aku bertemu\nMuchalitsku dan Tuhanku, yaitu Juruslamatku.",
                2 => "Aku yakin, sungguh amin, aku k'lak jadi milikNya;\ndalam t'rang dengan senang aku k'lak hidup sertaNya.",
            ], "Hari t'rang, waktu s'nang, tatkala aku bertemu\nMuchalitsku dan Tuhanku, yaitu Juruslamatku,\nPenebus yang kudus, yaitu Juruslamatku."],
            ['DSL', '136', 'Jam Sembahyang', [
                1 => "Jam sembahyang yang kudus, bila engkau keluh\npada Tuhan yang tahu kesusahanmu,\ndengan hati beriman biar pergi seg'ra,\nk'lak kau rasa sentosa duduk di situlah.",
                2 => "Jam sembahyang yang kudus, Tuhan t'lah berpesan:\nmana dua tau tiga berhimpun senang,\nmaka Aku hadirlah di pertengahannya.\nSungguh hati terhibur duduk di situlah.",
            ], "Bersembahyanglah pada Bapanya\ndengan nama Yesus turut kehendakNya."],
            ['DSL', '139', 'Kerinduanku', [
                1 => "Hai Anak Domba Allah yang t'lah datang\nmenghapuskanlah dosa dunia g'nap;\nseperti rusa cari air batang,\nbegitu jiwaku pun rindu Kau tetap.",
                2 => "Kutangis kar'na dosaku ceraikan Kau, Tuhan,\nhingga jiwaku kelam; kutinggalkan semua yang tegahkan,\nkudatang padaMu, baik b'ri kesucian.",
            ], "Biar hidupku kutaruh, Tuhanku,\ndi bawah kakiMu, hai Juruslamatku."],
            ['DSL', '181', 'Taburlah', [
                1 => "Yang menabur dengan tangis sambil harap kuat Hu,\nmengetam k'lak dengan sorak, s'lamat yang kekal penuh.",
                2 => "Tegal itu jangan lalai, biar taburlah seg'ra;\npada musim pengetaman engkau k'lak bersoraklah.",
                3 => "Padang gandum yang di ladang, hai, betapa suburnya!\nMasing-masing b'ri seratus, meski iblis menegah.",
            ], "Embun surga pun mendiris, matahari membenas,\nhingga sarat tangkai pulur jadi masaklah lekas."],
            ['DSL', '188', 'Bersiap Nanti Kedatangan Tuhan', [
                1 => "Talama Tuhan datanglah, bersiap dirimu,\nsedia nanti datangNya dan ni'matNya penuh.\nKudus dan adil adanya, setia, tulus dan teguh,\ndan Ia akan datanglah selaku Sultanmu.",
                3 => "Berjagalah, berdoalah, bernanti Tuhanmu;\nbaik tilik tanda waktunya, bernanti pri teguh.\nKetikanya tak orang tahu melainkan Bapa sajalah,\ndan memang tidak lagi jauh; berjaga-jagalah.",
            ], "Hendaklah engkau jaga-jagalah,\nsedia lampu dengan minyaknya,\nkarna dengan sekejap kedengaranlah gegap:\nmempelai laki-laki datanglah!"],
            ['GPM', '1', 'Akang Manis Lawang', [
                1 => "Akang manis lawang kalo katong samua su bakumpul,\nkatong manyanyi sama-sama puji Tete Manis e.\nToki tifa dendang la rame-rame angka suara,\nkatong barsuka somba Tete Manis, katong barsuka somba Tete Manis.",
                2 => "Ayo basudara katong samua baku gandeng tangan,\nkatong badendang muliakan kasih Ama yang kekal.\nToki toleng-toleng tiop tahuri angka pujian,\nkatong barsuka somba Tete Manis, katong barsuka somba Tete Manis.",
            ], null],
            ['GPM', '65', 'Bapa, Siapkanlah Hatiku', [
                1 => "Bapa, siapkanlah hatiku, aku mau mendengar sabda-Mu.\nBapa, sucikanlah hatiku, aku mau mendengar sabda-Mu.\nSabda-Mu kekal, penerang langkahku dalam susah dan senang.\nBerilah Roh Kudus-Mu menuntun hidupku dalam susah dan senang.\nSiapkanlah hatiku, Bapa, mau menyambut sabda-Mu.\nSiapkanlah hatiku, Bapa, mau menyambut sabda-Mu. Amin.",
            ], null],

            // Lagu tanpa nomor buku (kategori "Lainnya"): cari dengan nomor urut atau judul
            ['BEBAS', '1', 'Tuhan Yesus Batu Penjuru', [
                1 => "Tuhan Yesus batu penjuru,\nbintang timur gilang gemilang.\nTuhan Yesus batu penjuru,\nbintang timur gilang gemilang.\nOrang Papua… heh!\nApa yang kau pilih?\nInjil kekuatan Allah\nyang memberi kemenangan.",
            ], null],
            ['BEBAS', '2', 'Seka-Seka Yo', [
                1 => "Seka-seka yo,\nkiri kanan putar badan puji Tuhan.\nSeka-seka yo,\nkiri kanan putar badan puji Tuhan.",
            ], "Yesus sayang ko, Yesus sayang sa,\nYesus sayang torang semua.\nYesus sayang ko, Yesus sayang sa,\nYesus sayang torang semua."],
            ['BEBAS', '3', 'Papua Ini Negeriku', [
                1 => "Papua ini negeriku, ini negeriku.\nPapua ini negeriku, ini negeriku.\nDi sini mengalir mata air yang jernih,\ndi sini terdengar pujian bagi Allah.",
            ], "Kemuliaan Tuhan sudah dinyatakan di tanah Papua.\nKemuliaan Tuhan sudah dinyatakan di tanah Papua.\nMari puji Dia, mari sembah Dia, Raja segala raja.\nMari puji Dia, mari sembah Dia, Raja segala raja."],
        ];
    }
}
