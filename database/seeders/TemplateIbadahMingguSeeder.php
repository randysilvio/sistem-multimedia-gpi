<?php

namespace Database\Seeders;

use App\Models\Liturgy;
use Illuminate\Database\Seeder;

/**
 * Template tata ibadah Minggu GPI Papua: disusun dari slide Jemaat Bethel Fakfak:
 *   Minggu I (lagu KJ), Minggu II (NKB), Minggu III (PKJ), Kunci Bulan, Minggu V (NR),
 *   ditambah dari tata ibadah November 2026: Minggu IV (DSL), Minggu Anak (Pola B), Adven I.
 *
 * Hanya STRUKTUR dan KALIMAT LITURGI. Lirik lagu tidak disimpan di sini: slot
 * "Nyanyian ..." diisi per jadwal lewat tombol Tarik dari Database Lagu
 * (contoh lagu yang dipakai tertulis di deskripsi tiap template).
 *
 * Konvensi:
 *  - Judul "Slide Bebas" = slide instruksi jemaat (teks besar di tengah).
 *  - Judul mengandung "Nyanyian" = slot lagu; "Bacaan Alkitab" = slot ayat.
 *  - [PF] / [PNT] / [JEMAAT] di awal bagian = label pembicara di layar.
 *  - ===SLIDE_BREAK=== = pindah ke slide berikutnya.
 *
 * Aman dijalankan berulang: template yang sudah ada tidak diubah. Template lama
 * bernama "Ibadah Minggu" (dari slide Minggu V) diganti nama menjadi "Ibadah Minggu V".
 */
class TemplateIbadahMingguSeeder extends Seeder
{
    public const NAMES = [
        'I' => 'Ibadah Minggu I',
        'II' => 'Ibadah Minggu II',
        'III' => 'Ibadah Minggu III',
        'KUNCI' => 'Ibadah Kunci Bulan',
        'V' => 'Ibadah Minggu V',
        'IV' => 'Ibadah Minggu IV',
        'ANAK' => 'Ibadah Minggu Anak (Pola B)',
        'ADVEN' => 'Ibadah Minggu Adven I',
    ];

    /** Nama lama template Minggu V */
    public const LEGACY_NAME = 'Ibadah Minggu';

    private const BR = "\n===SLIDE_BREAK===\n";

    public function run(): void
    {
        $legacy = Liturgy::where('name', self::LEGACY_NAME)->first();
        if ($legacy && !Liturgy::where('name', self::NAMES['V'])->exists()) {
            $legacy->update(['name' => self::NAMES['V'], 'description' => $this->descriptions()['V']]);
        }

        foreach (self::NAMES as $code => $name) {
            $liturgy = Liturgy::where('name', $name)->first();

            if (!$liturgy) {
                $liturgy = Liturgy::create(['name' => $name, 'description' => $this->descriptions()[$code]]);
                $this->fill($liturgy, $code);
                continue;
            }

            // Pembaruan isi template versi lama: hanya bila belum pernah dipakai jadwal,
            // supaya jadwal yang sudah dibuat tidak kehilangan isinya.
            $marker = self::UPGRADES[$code] ?? null;
            if ($marker && !$liturgy->items()->where('title', $marker)->exists() && !$liturgy->schedules()->exists()) {
                $liturgy->items()->delete();
                $this->fill($liturgy, $code);
            }
        }
    }

    /** Template versi lama yang diperbarui: kode => judul bagian yang menandai versi baru */
    private const UPGRADES = [
        'III' => 'Amanat Pengutusan', // dilengkapi dari tata ibadah 15 November 2026
    ];

    private function fill(Liturgy $liturgy, string $code): void
    {
        foreach ($this->items($code) as $order => [$title, $content]) {
            $liturgy->items()->create([
                'title' => $title,
                'is_dynamic' => true,
                'static_content' => $content,
                'order_number' => $order + 1,
            ]);
        }
    }

    public function descriptions(): array
    {
        return [
            'I' => 'Minggu pertama: lagu dari Kidung Jemaat (KJ). Pengakuan Iman Rasuli, Amanat Hidup Baru & Gloria. Contoh lagu: KJ 21, KJ 14, KJ 27, KJ 49, KJ 450, KJ 452, KJ 356, KJ 425.',
            'II' => 'Minggu kedua: lagu dari Nyanyikanlah Kidung Baru (NKB). Pengakuan Iman Rasuli. Contoh lagu: NKB 2, NKB 5, NKB 119, NKB 141, NKB 208, NKB 213, NKB 189.',
            'III' => 'Minggu ketiga: lagu dari Pelengkap Kidung Jemaat (PKJ). Pengakuan Iman Atanasius, Amanat Hidup Baru & Gloria. Contoh lagu: PKJ 13, PKJ 2, KJ 29, PKJ 15, PKJ 145, PKJ 290, PKJ 183, PKJ 274.',
            'KUNCI' => 'Ibadah kunci bulan (Minggu terakhir). Pengakuan Iman Rasuli, seruan Hosiana. Contoh lagu: DSL 118, PKJ 2, PKJ 43, KJ 50, KJ 450, DSL 186, NKB 210, NKB 189.',
            'V' => 'Minggu kelima: lagu dari Nyanyian Rohani (NR). Pengakuan Iman Nicea-Konstantinopel. Contoh lagu: NR 5, NR 3, PKJ 43, NR 79, NR 154, NR 132, NR 165, NR 189.',
            'IV' => 'Minggu keempat: lagu Dua Sahabat Lama (DSL). Pengakuan dosa berbalasan (Mazmur 51), Amanat Hidup Baru (1 Petrus 1:13-15), Gloria, Pengakuan Iman Rasuli. Contoh lagu: DSL 136, DSL 7, DSL 139, KJ 56, DSL 112, DSL 181, DSL 109, DSL 188.',
            'ANAK' => 'Ibadah Minggu khusus anak (Pola B): anak yang bertugas memimpin doa. Pengakuan Iman Rasuli, Gloria. Contoh lagu: KJ 21, KJ 3, KJ 28, KJ 184, KJ 302, KJ 26, KJ 424, KJ 346.',
            'ADVEN' => 'Minggu Adven I (nuansa etnis Indonesia Timur): penyalaan lilin Adven, Panggilan Beribadah, Doa Epiklese, respons "Maranatha", Pengakuan Iman dinyanyikan (KJ 280). Contoh lagu: GPM 1, KJ 81, PKJ 42, KJ 85, GPM 65, KJ 280, Papua Ini Negeriku, NKB 201, Seka-Seka Yo, Tuhan Yesus Batu Penjuru.',
        ];
    }

    /** @return array<int, array{0: string, 1: ?string}> */
    public function items(string $code): array
    {
        return match ($code) {
            'I' => $this->mingguI(),
            'II' => $this->mingguII(),
            'III' => $this->mingguIII(),
            'KUNCI' => $this->kunciBulan(),
            'V' => $this->mingguV(),
            'IV' => $this->mingguIV(),
            'ANAK' => $this->mingguAnak(),
            'ADVEN' => $this->adven(),
        };
    }

    // ------------------------------------------------------------------
    // Bagian yang sama di beberapa minggu
    // ------------------------------------------------------------------

    private function join(string ...$pieces): string
    {
        return implode(self::BR, $pieces);
    }

    private function say(string $speaker, string $text): string
    {
        return "[$speaker]\n$text";
    }

    private function rasuli(?string $intro = null): array
    {
        return ['Pengakuan Iman Rasuli', $this->join(
            ...array_filter([$intro ? $this->say('PF', $intro) : null]),
            ...[
            'Aku percaya kepada Allah, Bapa yang mahakuasa, Khalik langit dan bumi. Dan kepada Yesus Kristus, Anak-Nya yang tunggal, Tuhan kita,',
            'yang dikandung daripada Roh Kudus, lahir dari anak dara Maria,',
            'yang menderita di bawah pemerintahan Pontius Pilatus, disalibkan, mati dan dikuburkan, turun ke dalam kerajaan maut,',
            'pada hari yang ketiga bangkit pula dari antara orang mati, naik ke sorga, duduk di sebelah kanan Allah,',
            'Bapa yang mahakuasa, dan akan datang dari sana untuk menghakimi orang yang hidup dan yang mati.',
            'Aku percaya kepada Roh Kudus; gereja yang kudus dan am; persekutuan orang kudus;',
            'pengampunan dosa; kebangkitan daging; dan hidup yang kekal.',
            ],
        )];
    }

    /** Teks doa panjang dipotong di akhir kalimat, tiap potongan berlabel pembicara */
    private function prayer(string $speaker, string $text, int $max = 200): array
    {
        return array_map(fn ($p) => $this->say($speaker, $p), $this->chunks($text, $max));
    }

    private function ragamRefrein(): string
    {
        return $this->say('JEMAAT MENYANYIKAN', "Ragam Refrein KJ 393\nT'rima kasih pada Tuhan, hidupku diselamatkan.\nHatiku menjadi suci, kar'na dosaku diampuni.");
    }

    /** Pembukaan yang sama di semua minggu */
    private function opening(array $votum): array
    {
        return [
            ['Slide Bebas', 'WARTA JEMAAT'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Persiapan', null],
            $votum,
            ['Slide Bebas', 'INTROITUS'],
            ['Nyanyian Jemaat', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
        ];
    }

    /** Penutup: pengutusan, berkat, saat teduh, jabat tangan */
    private function closing(array $afterBerkat = [], ?string $amanat = null, ?string $berkat = null): array
    {
        return array_merge(
            [['Slide Bebas', "JEMAAT BERDIRI\nPENGUTUSAN DAN BERKAT"]],
            $amanat ? [['Amanat Pengutusan', $this->say('PF', $amanat)]] : [],
            [
                ['Nyanyian Pengutusan', null],
                $berkat ? ['Berkat', $this->join(...$this->prayer('PF', $berkat))] : ['Slide Bebas', 'BERKAT'],
            ],
            $afterBerkat ?: [['Slide Bebas', "AMIN…\nAMIN…\nAMIN…"]],
            [
                ['Slide Bebas', "JEMAAT DUDUK\nSAAT TEDUH"],
            ],
        );
    }

    // ------------------------------------------------------------------
    // Minggu I (KJ)
    // ------------------------------------------------------------------

    private function mingguI(): array
    {
        return array_merge($this->opening(['Votum dan Salam', $this->join(
            $this->say('PF', 'Dalam nama Bapa dan Anak dan Roh Kudus. Kasih karunia dan damai sejahtera dari Allah Bapa kita, dan dari Tuhan Yesus Kristus yang telah menyerahkan diri-Nya karena dosa-dosa kita. Bagi-Nyalah kemuliaan selama-lamanya.'),
            $this->say('JEMAAT', 'AMIN'),
        )]), [
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Saudara-saudara jemaat, jika kita berkata bahwa kita tidak berdosa, maka kita menipu diri kita sendiri dan kebenaran tidak ada di dalam kita.'),
                $this->say('PNT', "Karena itu marilah kita merendahkan diri di hadapan Allah dan mengaku dosa-dosa kita kepada-Nya.\nKita berdoa:"),
                $this->say('PNT', 'Ya Bapa, kami datang di hadapan-Mu dan mengaku bahwa kami telah melakukan hal-hal yang bertentangan dengan kehendak-Mu.'),
                $this->say('PNT', 'Kami lebih cenderung melakukan keinginan kami sendiri daripada menyatakan kepatuhan kami terhadap firman-Mu.'),
                $this->say('PNT', 'Kami tidak setia melaksanakan panggilan kami sebagai saksi-saksi-Mu. Kami sering mengorbankan kepentingan diri sendiri.'),
                $this->say('PNT', 'Ampunilah dan sucikanlah kami, ya Bapa pengasih, demi kemuliaan-Mu dalam Kristus Juruselamat kami. Amin.'),
            )],
            ['Nyanyian Penyesalan', null],
            ['Berita Anugerah Pengampunan Dosa', $this->join(
                $this->say('PF', 'Demi nama Bapa, Anak dan Roh Kudus, diberitakan bahwa pengampunan dosa telah berlaku atas setiap orang yang telah mengaku dosanya dengan tulus ikhlas di hadapan Allah.'),
                $this->say('PF', 'Karena begitu besar kasih Allah akan dunia ini, sehingga Ia telah mengaruniakan Anak-Nya yang tunggal, supaya setiap orang yang percaya kepada-Nya tidak binasa, melainkan beroleh hidup yang kekal.'),
                $this->ragamRefrein(),
            )],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Amanat Hidup Baru', $this->join(
                $this->say('JEMAAT', 'Beritahukanlah jalan-jalan-Mu kepada kami ya Tuhan, tunjukkanlah itu kepada kami. Bawalah kami berjalan dalam kebenaran-Mu dan ajarlah kami, sebab Engkaulah Allah Penyelamat kami.'),
                $this->say('PF', "Dengarlah saudara-saudara, Amanat Hidup Baru:\n\"Kasihilah Tuhan, Allahmu, dengan segenap hatimu dan dengan segenap jiwamu dan dengan segenap akal budimu. Itulah hukum yang terutama dan yang pertama."),
                $this->say('PF', 'Dan hukum yang kedua, yang sama dengan itu, ialah: Kasihilah sesamamu manusia seperti dirimu sendiri. Pada kedua hukum inilah tergantung seluruh hukum Taurat dan kitab para nabi."'),
                $this->say('PF', 'Kiranya Roh Kudus menolong kita mewujudkan kemuliaan Allah dalam hidup kita.'),
            )],
            ['Gloria Besar', $this->say('JEMAAT MENYANYIKAN', "Kemuliaan bagi Allah di tempat yang Mahatinggi,\ndan damai sejahtera di bumi di antara manusia\nyang berkenan kepada-Nya.")],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Doa Pembacaan Alkitab', $this->join(
                $this->say('PF', 'Ya Tuhan, kami akan mendengarkan firman-Mu yang adalah pelita bagi kaki kami dan terang bagi jalan kami. Berilah Roh Kudus-Mu mendiami kami supaya kami menjadi bijaksana'),
                $this->say('PF', 'dan dengan hati yang teguh dapat mengamalkan firman-Mu dalam hidup kami sehari-hari. Berfirmanlah, ya Tuhan, sebab kami sudah sedia mendengarkan.'),
                $this->say('JEMAAT MENYANYIKAN', "Bersabdalah Tuhan, kami mendengarkan.\nBersabdalah Tuhan, kami mendengarkan."),
            )],
            ['Bacaan Alkitab', null],
            ['Slide Bebas', "HALELUYA\nHALELUYA\nHALELUYA"],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            $this->rasuli(),
            ['Nyanyian Pengakuan', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Nyanyian Jemaat', null],
        ], $this->closing(), [
            ['Nyanyian Jabat Tangan', null],
        ]);
    }

    // ------------------------------------------------------------------
    // Minggu II (NKB)
    // ------------------------------------------------------------------

    private function mingguII(): array
    {
        $kyrie = $this->say('JEMAAT', "Tuhan, kasihani.\nKristus, kasihani.\nTuhan, kasihani kami.");

        return array_merge($this->opening(['Votum dan Salam', $this->join(
            $this->say('PF', 'Pertolongan kepada kita adalah di dalam nama Tuhan yang menjadikan langit dan bumi, yang tetap setia di sepanjang sejarah umat manusia dan yang tidak pernah meninggalkan perbuatan tangan-Nya.'),
            $this->say('PF', 'Damai sejahtera Allah menyertai saudara.'),
            $this->say('JEMAAT', 'AMIN'),
        )]), [
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Bapa kami yang mahakuasa, dengan muka yang tertunduk karena dosa dan kesalahan kami, kami mengaku bahwa di sepanjang hidup ini, kami sering melakukan hal-hal yang tidak memuliakan nama-Mu.'),
                $this->say('PNT', 'Kami sering berbicara tentang mengasihi Tuhan dan sesama kami, tetapi kami lebih cenderung untuk mengasihi diri sendiri. Sebab itu kami mohon:'),
                $kyrie,
                $this->say('PNT', 'Kami sering berbicara tentang hukum-hukum-Mu yang adil, tetapi kami tidak setia untuk melakukannya di dalam kehidupan kami.'),
                $this->say('PNT', 'Kami berbicara tentang kehendak-Mu yang agung, tetapi kami cenderung untuk mengikuti kehendak pribadi kami. Sebab itu kami mohon:'),
                $kyrie,
                $this->say('PNT', 'Kasihanilah kami dan bebaskanlah kami dari segala hutang dosa yang telah kami lakukan.'),
                $this->say('PNT', 'Baharuilah langkah hidup kami dan berilah kami kesanggupan untuk mengabdi dengan setia kepada-Mu. Demi Kristus, Tuhan dan Juruselamat kami.'),
                $this->say('PNT & JEMAAT', 'AMIN'),
            )],
            ['Berita Anugerah Pengampunan Dosa', $this->join(
                $this->say('PF', 'Tuhan itu penyayang dan pengasih, panjang sabar dan berlimpah kasih setia. Bukan untuk seterusnya Ia menuntut dan bukan untuk selama-lamanya Ia mendendam.'),
                $this->say('PF', 'Tidak dilakukan-Nya kepada kita setimpal dengan dosa kita, dan tidak dibalas-Nya kepada kita setimpal dengan kesalahan kita.'),
                $this->say('PF', 'Tetapi setinggi langit di atas bumi, demikian besarnya kasih setia-Nya atas orang-orang yang takut akan Dia.'),
                $this->say('PF', 'Sejauh timur dari barat, dijauhkan-Nya dari pada kita pendurhakaan kita. Seperti bapa sayang kepada anak-anaknya, demikian Tuhan sayang kepada orang-orang yang takut akan Dia.'),
                $this->ragamRefrein(),
            )],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Slide Bebas', 'DOA PEMBACAAN ALKITAB'],
            ['Bacaan Alkitab', null],
            ['Slide Bebas', "HALELUYA\nHALELUYA\nHALELUYA"],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            $this->rasuli(),
            ['Nyanyian Pengakuan', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Nyanyian Jemaat', null],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
        ], $this->closing(), [
            ['Nyanyian Jabat Tangan', null],
        ]);
    }

    // ------------------------------------------------------------------
    // Minggu III (PKJ)
    // ------------------------------------------------------------------

    private function mingguIII(): array
    {
        return array_merge($this->opening(['Votum dan Salam', $this->join(
            $this->say('PF', 'Dalam nama Bapa dan Anak dan Roh Kudus. Kasih karunia dan damai sejahtera dari Allah Bapa kita, dan dari Tuhan Yesus Kristus menyertai saudara sekalian.'),
            $this->say('JEMAAT', 'AMIN'),
        )]), [
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Sungguh, dosa pemberontakan kami banyak di hadapan-Mu dan dosa kami bersaksi melawan kami. Sungguh, kami menyadari pemberontakan kami dan kami mengenal kejahatan kami.'),
                $this->say('PRIA', 'Kami telah memberontak dan mungkir terhadap Tuhan serta berbalik dari mengikuti Allah kami. Kami merencanakan pemerasan dan penyelewengan, mengandung dusta dalam kata-kata.'),
                $this->say('WANITA', 'Oleh karena itu kami mohon ya Tuhan, ampunilah segala dosa kami, dalam nama Tuhan Yesus Kristus.'),
                $this->say('JEMAAT', 'AMIN'),
            )],
            ['Nyanyian Penyesalan', null],
            ['Berita Anugerah Pengampunan Dosa', $this->join(
                $this->say('PF', 'Dengarlah saudara-saudara, Berita Anugerah Pengampunan Dosa:'),
                $this->say('PF', '"Sekalipun dosamu merah seperti kirmizi, akan menjadi putih seperti salju; sekalipun berwarna merah seperti kain kesumba, akan menjadi putih seperti bulu domba."'),
            )],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Amanat Hidup Baru', $this->join(
                $this->say('PF', 'Jemaat kekasih Kristus, marilah kita satukan kerinduan kita di dalam menyambut Amanat Hidup Baru.'),
                $this->say('JEMAAT', 'Beritahukanlah jalan-jalan-Mu kepada kami ya Tuhan, tunjukkanlah itu kepada kami.'),
                $this->say('JEMAAT', 'Bawalah kami berjalan dalam kebenaran-Mu dan ajarlah kami, sebab Engkaulah Allah Penyelamat kami.'),
                $this->say('PF', 'Karena itu, sebagai orang-orang pilihan Allah yang dikuduskan dan dikasihi-Nya, kenakanlah belas kasihan,'),
                $this->say('PF', 'kemurahan, kerendahan hati, kelemahlembutan dan kesabaran. Sabarlah kamu seorang terhadap yang lain,'),
                $this->say('PF', 'dan ampunilah seorang akan yang lain apabila yang seorang menaruh dendam terhadap yang lain,'),
                $this->say('PF', 'sama seperti Tuhan telah mengampuni kamu, kamu perbuat jugalah demikian. Dan di atas semuanya itu:'),
                $this->say('PF', 'kenakanlah kasih, sebagai pengikat yang mempersatukan dan menyempurnakan.'),
                $this->say('PF', 'Hendaklah damai sejahtera Kristus memerintah dalam hatimu, karena untuk itulah kamu telah dipanggil menjadi satu tubuh. Dan bersyukurlah.'),
                $this->say('PF', 'Kiranya Roh Kudus menolong kita mewujudkan kemuliaan Allah dalam hidup kita.'),
            )],
            ['Gloria Besar', $this->join(
                $this->say('JEMAAT MENYANYIKAN', "Tuhan Allah Raja sorga, Allah Bapa Mahakuasa,\nEngkaulah yang layak kami puji,\nmenyembah sambil bersyukur demi kemuliaan-Nya."),
                $this->say('JEMAAT MENYANYIKAN', "Hanya Kau yang Mahasuci, hanya Kau yang Mahatinggi,\nya Yesus Mesias Tuhan kami, beserta dengan Roh Kudus\ndi kemuliaan Bapa-Mu."),
            )],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Slide Bebas', 'DOA PEMBACAAN ALKITAB'],
            ['Nyanyian Persiapan Firman', null],
            ['Bacaan Alkitab', null],
            ['Respons Jemaat', $this->join(
                $this->say('PF', 'Alangkah berbahagianya orang-orang yang mendengarkan dan melakukan firman Tuhan dalam hidupnya. Terpujilah Kristus.'),
                $this->say('JEMAAT', 'HALELUYA… HALELUYA… HALELUYA…'),
            )],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Pengakuan Iman Atanasius', $this->join(...$this->chunks($this->atanasius()))],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Ajakan Memberi', $this->join(...$this->prayer('DKN', 'Saudara-saudara, sebelum kita memberikan tanda kasih lewat persembahan syukur ini, terlebih dahulu dengarkanlah ajakan untuk memberi: "Janganlah menghadap Tuhan dengan tangan hampa, tetapi masing-masing dengan sekedar persembahan, sesuai dengan berkat yang diberikan kepadamu oleh Tuhan, Allahmu." (Ulangan 16:16b-17)'))],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Nyanyian Jemaat', null],
        ], $this->closing([
            ['Jawaban Berkat (KJ 348)', $this->say('JEMAAT MENYANYIKAN', "Anug'rah Tuhan kita, Yesus Kristus,\npengasihan Allah, persekutuan dalam Roh Kudus\nkiranya menyertai kita. Amin.")],
        ], 'Marilah kita pulang dan menjadi saksi bagi-Nya di tempat kita berada.', 'Jemaat Tuhan, arahkanlah hati dan pikiranmu kepada Allah, dan terimalah berkat-Nya.'), [
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Jabat Tangan', null],
        ]);
    }

    /**
     * Pengakuan Iman Atanasius. Bagian "Tetapi untuk keselamatan yang kekal ... ialah Allah dan
     * manusia." hilang di slide (slide itu terduplikasi): dilengkapi dari rumusan bakunya.
     */
    private function atanasius(): string
    {
        return 'Barang siapa hendak menjadi selamat, pertama ia harus memegang iman yang am; jikalau seseorang tidak memeliharanya dengan sebulat semurninya, niscaya ia akan binasa. '
            . 'Adapun iman yang am adalah ini: bahwa kita menyembah satu Allah dalam ke-Tigaan dan ke-Tigaan dalam ke-Satuan; tanpa mengaduk oknum, tanpa menceraikan tabiat. '
            . 'Memang oknum Bapa adalah lain; oknum Anak adalah lain; oknum Roh Kudus adalah lain; akan tetapi Bapa, Anak dan Roh Kudus ke-Allahan-Nya satu; kehormatan-Nya sama, kemuliaan-Nya seabadi. '
            . 'Sedemikian Bapa, demikian juga Anak, dan demikian juga Roh Kudus. '
            . 'Bapa adalah tak tercipta, Anak adalah tak tercipta, dan Roh Kudus adalah tak tercipta; Bapa adalah tak terhingga, Anak adalah tak terhingga, dan Roh Kudus adalah tak terhingga; '
            . 'Bapa adalah abadi, Anak adalah abadi, dan Roh Kudus adalah abadi; meskipun demikian tiada tiga yang abadi, akan tetapi satu. '
            . 'Seperti juga tiada tiga yang tak tercipta dan yang tak terhingga, tetapi satu yang tak tercipta dan yang tak terhingga. '
            . 'Demikian juga Bapa adalah Mahakuasa, Anak adalah Mahakuasa, dan Roh Kudus adalah Mahakuasa; meskipun demikian tiada tiga yang Mahakuasa, tetapi satu yang Mahakuasa. '
            . 'Demikian juga Bapa adalah Allah, Anak adalah Allah, dan Roh Kudus adalah Allah; meskipun demikian tiada tiga Allah, tetapi satu Allah. '
            . 'Demikian juga Bapa adalah Tuhan, Anak adalah Tuhan, dan Roh Kudus adalah Tuhan; meskipun demikian tiada tiga Tuhan, tetapi satu Tuhan. '
            . 'Seperti kita diperintahkan oleh kebenaran Kristus untuk menyebut tiap oknum tersendiri Allah atau Tuhan, demikian juga kita dilarang oleh iman yang am untuk mengatakan ada tiga Allah atau Tuhan. '
            . 'Bapa tidak dibuat oleh siapa pun, dan tidak diciptakan dan tidak diperanakkan. Anak adalah hanya dari Bapa, tidak dibuat dan tidak diciptakan, akan tetapi diperanakkan. '
            . 'Roh Kudus adalah dari Bapa dan Anak, tidak dibuat dan tidak diciptakan, tetapi keluar. '
            . 'Dengan demikian adalah satu Bapa, bukannya tiga Bapa; satu Anak, bukannya tiga Anak; satu Roh Kudus, bukannya tiga Roh Kudus. '
            . 'Dan di dalam Tritunggal tiada yang lebih dahulu atau lebih kemudian, tiada yang lebih tinggi atau lebih rendah, akan tetapi ketiga oknum semua seabadi dan semua sama; '
            . 'hingga di mana-mana, seperti di atas telah dinyatakan, ke-Satuan dalam ke-Tigaan dan ke-Tigaan dalam ke-Satuan harus disembah. '
            . 'Oleh karena itu barang siapa hendak menjadi selamat, harus demikian keyakinannya mengenai Tritunggal. '
            . 'Tetapi untuk keselamatan yang kekal perlu juga ia percaya dengan setia akan penjelmaan Tuhan kita Yesus Kristus. '
            . 'Adapun iman yang benar ialah bahwa kita percaya dan mengaku, bahwa Tuhan kita Yesus Kristus, Anak Allah, ialah Allah dan manusia. '
            . 'Ialah Allah dari hakekat Bapa, diperanakkan sebelum segala zaman; dan Ialah manusia dari hakekat ibu-Nya, lahir di dalam zaman; '
            . 'Allah yang sempurna dan manusia yang sempurna, dengan jiwa akali dan daging insani; sama dengan Bapa dalam ke-Allahan-Nya, lebih rendah daripada Bapa dalam kemanusiaan-Nya. '
            . 'Meskipun Ialah Allah dan manusia, Ia bukan dua, melainkan satu Kristus; '
            . 'akan tetapi Ialah satu, bukan dengan mengubah ke-Allahan-Nya menjadi daging, melainkan dengan mengenakan kemanusiaan-Nya dalam Allah; '
            . 'satu sekali-kali, bukanlah dengan mengaduk hakekat, melainkan dengan kesatuan oknum. '
            . 'Oleh karena itu, seperti jiwa akali dan daging adalah manusia satu, demikian juga Allah dan manusia adalah satu Kristus, yang telah menderita untuk keselamatan kita, turun ke kerajaan maut; '
            . 'pada hari yang ketiga bangkit dari antara orang mati, naik ke sorga, duduk di sebelah kanan Allah, Bapa Yang Mahakuasa; dari sana Ia akan datang untuk mengadili orang yang hidup dan yang mati. '
            . 'Pada kedatangan-Nya segala orang akan bangkit dengan badannya, dan mereka akan mempertanggungjawabkan perbuatan-perbuatannya; '
            . 'dan yang telah berbuat baik akan pergi ke hidup kekal, sebaliknya yang telah berbuat jahat akan pergi ke api yang kekal. '
            . 'Inilah iman yang am; barang siapa tidak memeliharanya dengan setia dan kuat, ia tak menjadi selamat.';
    }

    /**
     * Potong teks panjang di akhir kalimat / titik koma menjadi potongan yang
     * panjangnya serata mungkin (±4 baris per slide, tanpa potongan yatim).
     */
    public function chunks(string $text, int $max = 200): array
    {
        $units = preg_split('/(?<=[.;])\s+/u', trim($text));
        $n = count($units);
        $len = array_map('mb_strlen', $units);
        $total = array_sum($len) + $n - 1;
        $target = $total / max(1, (int) ceil($total / ($max * 0.85)));

        // best[i] = biaya minimum untuk memotong unit 0..i-1; potongan > $max dihindari
        $best = [0 => 0.0];
        $from = [];
        for ($i = 1; $i <= $n; $i++) {
            $best[$i] = INF;
            $size = -1;
            for ($j = $i - 1; $j >= 0; $j--) {
                $size += $len[$j] + 1;
                $cost = $best[$j] + ($size - $target) ** 2 + ($size > $max ? 1e9 : 0);
                if ($cost < $best[$i]) {
                    $best[$i] = $cost;
                    $from[$i] = $j;
                }
                if ($size > $max * 2) {
                    break;
                }
            }
        }

        $out = [];
        for ($i = $n; $i > 0; $i = $from[$i]) {
            array_unshift($out, implode(' ', array_slice($units, $from[$i], $i - $from[$i])));
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // Kunci Bulan
    // ------------------------------------------------------------------

    private function kunciBulan(): array
    {
        return array_merge($this->opening(['Votum dan Salam', $this->join(
            $this->say('PF', 'Asal dan sumber hidup kita hanyalah Tuhan, Pencipta langit dan bumi. Kasih setia-Nya tidak pernah berakhir dan tuntunan tangan-Nya senantiasa menyertai kita.'),
            $this->say('PF', 'Damai sejahtera Allah menyertai saudara sekalian yang beribadah di saat ini.'),
            $this->say('JEMAAT', 'AMIN'),
        )]), [
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Kami datang di hadapan-Mu ya Allah, mengaku akan segala dosa kami. Kami hidup dalam kebencian, iri hati,'),
                $this->say('PNT', 'cemburu, kemunafikan dan masih banyak lagi dosa yang kami lakukan di sepanjang satu bulan ini.'),
                $this->say('PNT', 'Karena itu ampuni dan bersihkan kami dari dosa kami, agar kami layak menjadi anak-anak-Mu. Amin.'),
            )],
            ['Nyanyian Penyesalan', null],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Slide Bebas', 'DOA PEMBACAAN ALKITAB'],
            ['Bacaan Alkitab', null],
            ['Slide Bebas', "HOSIANA…\nHOSIANA…\nHOSIANA…"],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            $this->rasuli(),
            ['Nyanyian Pengakuan', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Nyanyian Jemaat', null],
        ], $this->closing(), [
            ['Nyanyian Jabat Tangan', null],
        ]);
    }

    // ------------------------------------------------------------------
    // Minggu IV (DSL): dari tata ibadah 22 November 2026
    // ------------------------------------------------------------------

    private function mingguIV(): array
    {
        return array_merge($this->opening(['Votum dan Salam', $this->join(
            $this->say('PF', 'Asal dan sumber hidup kita ialah Tuhan yang menjadikan langit dan bumi. Kasih setia-Nya tidak pernah berakhir dan tuntunan tangan-Nya senantiasa menyertai kita.'),
            $this->say('PF', 'Damai sejahtera Allah menyertai saudara-saudara.'),
            $this->say('JEMAAT', 'Dan menyertai saudara juga.'),
            $this->say('PF & JEMAAT', "1 – 7 – 1\nAMIN"),
        )]), [
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Saudara-saudara jemaat, selaku orang-orang berdosa marilah kita tunduk di hadapan Allah dan mengaku dosa-dosa kita kepada-Nya.'),
                $this->say('PNT', 'Kasihanilah kami, ya Allah, menurut kasih setia-Mu.'),
                $this->say('JEMAAT', 'Hapuskanlah pelanggaran kami, menurut rahmat-Mu yang besar.'),
                $this->say('PNT', 'Bersihkanlah kami seluruhnya dari kesalahan kami, tahirkanlah kami dari dosa-dosa kami.'),
                $this->say('JEMAAT', 'Sebab kami sadar akan pelanggaran kami, kami senantiasa bergumul dengan dosa-dosa kami.'),
                $this->say('PNT', 'Terhadap Engkau, terhadap Engkau sajalah kami telah berdosa.'),
                $this->say('JEMAAT', 'Dan melakukan apa yang Kau anggap jahat.'),
                $this->say('PNT', 'Ampunilah kami di dalam Putera-Mu, Tuhan dan Juruselamat kami.'),
            )],
            ['Nyanyian Penyesalan', null],
            ['Berita Anugerah Pengampunan Dosa', $this->join(
                $this->say('PF', 'Saudara-saudara jemaat, selaku orang-orang yang telah diampuni dosa-dosanya, dengarkanlah sekarang berita anugerah pengampunan dosa:'),
                $this->say('PF', '"Maka Ia yang tiada mengenal dosa telah dibuat-Nya menjadi dosa karena kita, supaya dalam Dia kita dibenarkan oleh Allah." (II Korintus 5:21)'),
                $this->ragamRefrein(),
            )],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Amanat Hidup Baru', $this->join(
                $this->say('PF', 'Siapkanlah akal budimu, waspadalah dan letakkanlah pengharapanmu seluruhnya atas kasih karunia yang dianugerahkan kepadamu pada waktu penyataan Yesus Kristus.'),
                $this->say('PF', 'Hiduplah sebagai anak-anak yang taat dan jangan turuti hawa nafsu yang menguasai kamu pada waktu kebodohanmu,'),
                $this->say('PF', 'tetapi hendaklah kamu menjadi kudus di dalam seluruh hidupmu, sama seperti Dia yang kudus, yang telah memanggil kamu. (I Petrus 1:13-15)'),
            )],
            ['Gloria Besar', $this->say('JEMAAT MENYANYIKAN', "Tuhan Allah Raja sorga, Allah Bapa Mahakuasa,\nEngkaulah yang layak kami puji,\nmenyembah sambil bersyukur demi kemuliaan-Nya.")],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Doa Pembacaan Alkitab', $this->say('PF', 'Jemaat yang Tuhan Yesus kasihi, mari kita berdoa mohon tuntunan Roh Kudus untuk mendengar firman Tuhan dengan menyanyikan KJ 56.')],
            ['Nyanyian Doa Pembacaan Alkitab', null],
            ['Bacaan Alkitab', null],
            ['Respons Jemaat', $this->join(
                $this->say('PF', 'Demikianlah firman Tuhan semesta alam. Hendaklah kamu menjadi pelaku firman dan bukan hanya pendengar saja.'),
                $this->say('JEMAAT', 'HALELUYA… HALELUYA… HALELUYA…'),
            )],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            $this->rasuli('Dalam persekutuan dengan semua orang percaya di segala abad dan tempat, marilah kita mengaku iman kita dengan mengucapkan Pengakuan Iman Rasuli.'),
            ['Nyanyian Pengakuan', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Ajakan Memberi', $this->say('DKN', 'Kristus telah mati untuk semua orang, supaya mereka yang hidup tidak lagi hidup untuk dirinya sendiri, tetapi hidup untuk Dia, yang telah mati dan telah dibangkitkan untuk mereka.')],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
            ['Slide Bebas', "DOA SYAFAAT\nDOA BAPA KAMI"],
        ], $this->closing(
            [],
            'Kembalilah ke dalam hidupmu sehari-hari dan lakukanlah firman Tuhan.',
            'Allah sumber kasih karunia, yang telah memanggil kamu dalam Kristus kepada kemuliaan-Nya yang kekal, akan melengkapi, meneguhkan dan mengokohkan kamu. Ialah yang empunya kuasa sampai selama-lamanya.',
        ), [
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Jabat Tangan', null],
        ]);
    }

    // ------------------------------------------------------------------
    // Minggu khusus Anak, Pola B: dari tata ibadah 8 November 2026
    // ------------------------------------------------------------------

    private function mingguAnak(): array
    {
        return [
            ['Slide Bebas', 'WARTA ANAK'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Persiapan', null],
            ['Votum dan Salam', $this->join(
                $this->say('PF', 'Dalam nama Bapa, dan Anak, dan Roh Kudus. Kasih karunia dan damai sejahtera dari Allah Bapa kita dan dari Tuhan Yesus Kristus menyertai kita semua.'),
                $this->say('JEMAAT', 'AMIN'),
            )],
            ['Slide Bebas', 'INTROITUS'],
            ['Nyanyian Anak', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Pengakuan Dosa', $this->join(
                $this->say('ANAK', "Anak-anak, marilah kita merendahkan diri di hadapan Allah dan mengaku dosa-dosa kita kepada-Nya.\nKita berdoa:"),
                ...$this->prayer('ANAK', 'Ya Yesus, kami memohon di hadapan-Mu, sudah banyak dosa yang telah kami perbuat, melawan orang tua, tidak menjadi anak yang baik. Selama ini ada banyak perbuatan dan perkataan yang tidak mencerminkan kasih-Mu. Dengan penuh kerendahan hati dan memohon pengampunan, berikanlah kami kesempatan untuk memperbaiki, kembalikan kami dalam rencana-Mu yang indah, berikanlah pengampunan-Mu agar kami bertumbuh menjadi pribadi yang semakin taat kepada-Mu. Kami berjanji tidak akan mengulangi kesalahan yang sama. Pengampunan-Mu telah mengubah kami menjadi lebih baik. Dengarlah doa kami. Amin.'),
            )],
            ['Nyanyian Penyesalan Anak', null],
            ['Berita Anugerah Pengampunan Dosa', $this->join(
                $this->say('PF', 'Dengarlah anak-anak, Berita Anugerah Pengampunan Dosa:'),
                $this->say('PF', '"Tetapi hendaklah kamu ramah terhadap seorang yang lain, penuh kasih mesra dan saling mengampuni, sebagaimana Allah di dalam Kristus telah mengampuni kamu." (Efesus 4:32)'),
                $this->say('JEMAAT MENYANYIKAN', "T'rima kasih pada Tuhan, hidupku diselamatkan.\nHatiku menjadi suci, kar'na dosaku diampuni."),
            )],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Amanat Hidup Baru', $this->join(
                $this->say('PF', 'Anak-anak, dengarlah Amanat Hidup Baru:'),
                $this->say('PF', '"Kamu akan Kuberikan hati yang baru, dan roh yang baru di dalam batinmu dan Aku akan menjauhkan dari tubuhmu hati yang keras dan Kuberikan kepadamu hati yang taat." (Yehezkiel 36:26)'),
            )],
            ['Gloria Besar', $this->say('JEMAAT MENYANYIKAN', "Kemuliaan bagi Allah di tempat yang Mahatinggi,\ndan damai sejahtera di bumi di antara manusia\nyang berkenan kepada-Nya.")],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Doa Pembacaan Alkitab', $this->join(...$this->prayer('ANAK', 'Tuhan Yesus sahabat kami anak-anak, pada saat ini kami menyediakan hati, pikiran dan diri kami seutuhnya untuk firman-Mu yang mengubah kehidupan kami menjadi lebih baik. Berilah kemampuan untuk melakukan firman-Mu dalam kehidupan kami sehari-hari. Berfirmanlah ya Tuhan, sebab kami sudah sedia mendengar. Amin.'))],
            ['Bacaan Alkitab', null],
            ['Respons Jemaat', $this->join(
                $this->say('PF', 'Berbahagialah orang yang mendengar firman Tuhan dan melaksanakannya dalam hidupnya. Haleluya!'),
                $this->say('JEMAAT', 'HALELUYA… HALELUYA… HALELUYA…'),
            )],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            $this->rasuli('Anak-anak, marilah kita berdiri dan mengikrarkan Pengakuan Iman Rasuli secara bersama-sama, dan berkata demikian:'),
            ['Nyanyian Pengakuan Anak', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'PENGUCAPAN SYUKUR'],
            ['Ajakan Memberi', $this->say('DKN', 'Dan Allah sanggup melimpahkan segala kasih karunia kepada kamu, supaya kamu senantiasa berkecukupan di dalam segala sesuatu dan malah berkelebihan di dalam pelbagai kebajikan. (II Korintus 9:8)')],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Nyanyian Anak', null],
            ...$this->closing(
                [],
                'Anak-anak, pulanglah dengan sukacita dan tetaplah berkarya demi kemuliaan nama Tuhan.',
                'Kini angkatlah hati dan pikiranmu kepada Tuhan, terimalah berkat dari pada-Nya: "Anugerah Tuhan Yesus Kristus, pengasihan Allah Bapa, dan persekutuan dalam Roh Kudus kiranya menyertaimu senantiasa, kini dan selamanya."',
            ),
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Jabat Tangan', null],
        ];
    }

    // ------------------------------------------------------------------
    // Minggu Adven I (etnis Indonesia Timur): dari tata ibadah 29 November 2026
    // ------------------------------------------------------------------

    private function adven(): array
    {
        return [
            ['Slide Bebas', 'WARTA JEMAAT'],
            ['Slide Bebas', "PENYALAAN LILIN IBADAH\nLILIN ADVEN I"],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Persiapan', null],
            ['Panggilan Beribadah', $this->join(
                $this->say('PF', 'Pujilah Tuhan, hai jiwaku. Pujilah nama-Nya, hai segenap batinku.'),
                $this->say('JEMAAT', 'Pujilah Dia, hai matahari dan bulan; pujilah Dia, hai bintang-bintang yang gemerlapan.'),
                $this->say('PF', 'Dia, yang menutupi langit dengan awan-awan, yang menyediakan hujan bagi bumi, yang membuat gunung-gunung menumbuhkan rumput.'),
                $this->say('JEMAAT', 'Betapa terangnya langit menyatakan kemuliaan Allah! Betapa jelasnya cakrawala menunjukkan perbuatan-Nya.'),
                $this->say('PF', 'Langit menceritakan kemuliaan Allah, dan cakrawala memberitakan pekerjaan tangan-Nya; hari meneruskan berita itu kepada hari, dan malam menyampaikan pengetahuan itu kepada malam.'),
                $this->say('JEMAAT', 'Tidak ada berita dan tidak ada kata, suara mereka tidak terdengar; tetapi gema mereka terpencar ke seluruh dunia, dan perkataan mereka sampai ke ujung bumi.'),
                $this->say('PF', 'Ya Tuhan, Tuhan kami, betapa mulia nama-Mu di seluruh bumi! Kami hendak mengagungkan dan memasyhurkan nama-Mu di seluruh bumi.'),
                $this->say('PF & JEMAAT', 'Marilah kita memuji dan memuliakan nama-Nya, sujud dan sembah kepada-Nya dalam ibadah Minggu Adven ke-1 saat ini.'),
            )],
            ['Votum dan Salam', $this->join(
                $this->say('PF', 'Ibadah Minggu Adven ke-1 saat ini ditahbiskan di dalam nama Allah Bapa, Anak dan Roh Kudus. Kasih karunia Tuhan Yesus Kristus menyertai saudara-saudara yang beribadah saat ini.'),
                $this->say('JEMAAT', 'Kasih yang sama juga menyertai saudara. Amin.'),
            )],
            ['Slide Bebas', 'INTROITUS'],
            ['Nyanyian Jemaat', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Dalam ibadah Adven ke-1 ini, marilah dengan rendah hati kita mengaku dosa-dosa kita kepada-Nya:'),
                ...$this->prayer('PNT', 'Ya Allah sumber damai, kami mengaku bahwa kami sering hidup dalam kekhawatiran dan ketakutan. Kami lebih mengandalkan kekuatan kami sendiri daripada berharap kepada-Mu. Kami gagal menjadi pembawa damai di tengah keluarga, gereja dan masyarakat. Bahkan sering kali kami menyimpan dendam dan amarah terhadap sesama kami. Ampunilah kami, ya Tuhan. Sucikanlah hati kami dalam masa Adven ini, agar kami sungguh siap menyambut kedatangan Kristus, Sang Raja Damai. Di dalam nama Yesus Kristus kami berdoa. Amin.'),
            )],
            ['Nyanyian Penyesalan', null],
            ['Anugerah Pengampunan Tuhan', $this->join(...$this->prayer('PF', 'Jemaat, dengarlah kata firman Tuhan: "Baiklah orang fasik meninggalkan jalannya, dan orang jahat meninggalkan rancangannya; baiklah ia kembali kepada Tuhan, maka Dia akan mengasihaninya, sebab Ia memberi pengampunan dengan limpahnya."'))],
            ['Nyanyian Jemaat', null],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Slide Bebas', 'DOA EPIKLESE'],
            ['Nyanyian Doa Epiklese', null],
            ['Bacaan Alkitab', null],
            ['Respons Jemaat', $this->join(
                $this->say('PF', 'Berbahagialah orang yang mendengar firman Tuhan dan melakukannya dalam hidupnya.'),
                $this->say('JEMAAT', 'MARANATHA… MARANATHA… MARANATHA…'),
            )],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Pengakuan Iman', $this->say('PF', 'Bersama semua orang percaya di segala abad dan tempat, marilah kita berdiri mengucapkan pengakuan iman dengan menyanyikan KJ 280 "Aku Percaya".')],
            ['Nyanyian Pengakuan Iman', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', "PUJIAN\nPS / VG / SOLO"],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Ajakan Memberi', $this->say('DKN', 'Marilah kita membawa syukur kita ke pelataran-Nya, sambil mengingat kata firman Tuhan: "Sebab segala sesuatu adalah dari Dia, dan oleh Dia, dan kepada Dia: Bagi Dialah kemuliaan sampai selama-lamanya!" (Roma 11:36)')],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
            ...$this->closing(
                [['Jawaban Berkat (PKJ 292)', $this->say('JEMAAT MENYANYIKAN', "AMIN… AMIN… AMIN…")]],
                'Pulanglah dengan damai dan sukacita, maknailah Masa Raya Adven sebagai masa pengharapan, penantian dan persiapan untuk menyambut kedatangan Tuhan Yesus Kristus.',
                '"Diberkatilah orang yang mengandalkan TUHAN, yang menaruh harapannya pada TUHAN! Ia akan seperti pohon yang ditanam di tepi air, yang merambatkan akar-akarnya ke tepi batang air, dan yang tidak mengalami datangnya panas terik, yang daunnya tetap hijau, yang tidak kuatir dalam tahun kering, dan yang tidak berhenti menghasilkan buah." (Yeremia 17:7-8)',
            ),
            ['Slide Bebas', 'LILIN DIPADAMKAN'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Nyanyian Jabat Tangan', null],
            ['Nyanyian Penutup', null],
            ['Slide Bebas', "SELAMAT MERAYAKAN\nMINGGU ADVEN I"],
        ];
    }

    // ------------------------------------------------------------------
    // Minggu V (NR): dari slide 30 Agustus
    // ------------------------------------------------------------------

    private function mingguV(): array
    {
        return array_merge($this->opening(['Votum dan Salam', $this->join(
            $this->say('PNT', 'Dalam nama Bapa dan Anak dan Roh Kudus. Kasih karunia dan damai sejahtera dari Allah Bapa kita dan dari Tuhan Yesus Kristus menyertai saudara-saudara.'),
            $this->say('JEMAAT', "1 – 1\nAMIN"),
        )]), [
            ['Pengakuan Dosa', $this->join(
                $this->say('PNT', 'Sebagai persekutuan keluarga kami tidak menghadirkan kasih-Mu di tengah-tengah keluarga kami. Kami menyakiti hati saudara-saudara kami. Perselisihan, iri hati, dengki dan masih banyak lagi kegagalan kami yang lain, yang tidak sempat kami ucapkan. Oleh karena itu ya Bapa, kami mohon kepada-Mu.'),
                $this->say('JEMAAT', "Tuhan\nkasihanilah kami"),
                $this->say('PNT', "Berilah kami sejahtera-Mu\nberlaku atas kami"),
                $this->say('JEMAAT', 'Bebaskanlah kami dari dosa kami dan perbaharuilah hidup kami, di dalam Yesus Kristus Juruselamat kami. Amin'),
            )],
            ['Berita Anugerah Pengampunan Dosa', $this->say('JEMAAT', 'Nyanyikan mazmur bagi Tuhan hai orang-orang yang dikasihi-Nya dan persembahkanlah syukur bagi nama-Nya yang kudus. Sebab sesaat saja Ia murka, tetapi seumur hidup Ia murah hati. Sepanjang malam ada tangisan, menjelang pagi terdengar sorak-sorai.')],
            ['Nyanyian Respons Jemaat', null],
            ['Slide Bebas', 'PELAYANAN FIRMAN'],
            ['Slide Bebas', 'DOA PEMBACAAN ALKITAB'],
            ['Bacaan Alkitab', null],
            ['Slide Bebas', "HALELUYA\nHALELUYA\nHALELUYA"],
            ['Slide Bebas', 'KHOTBAH'],
            ['Slide Bebas', 'JEMAAT BERDIRI'],
            ['Pengakuan Iman Nicea-Konstantinopel', $this->join(
                'Aku percaya kepada satu Allah, Bapa yang Maha Kuasa, pencipta langit dan bumi. Segala yang kelihatan dan tidak kelihatan.',
                'Dan kepada satu Tuhan Yesus Kristus Anak Allah yang Tunggal, yang lahir dari Sang Bapa, sebelum ada segala zaman.',
                'Allah dari Allah, Terang dari Terang Allah yang sejati dari Allah yang sejati diperanakkan bukan dibuat.',
                'Sehakekat dengan Sang Bapa, yang dengan perantaraan-Nya segala sesuatu dibuat, yang telah turun dari sorga, untuk kita manusia dan untuk keselamatan kita.',
                'Dan yang menjadi daging oleh Roh Kudus dari Anak Dara Maria, dan menjadi manusia yang disalibkan bagi kita di bawah pemerintahan Pontius Pilatus,',
                'menderita, mati dan dikuburkan. Yang bangkit pada hari ketiga sesuai dengan isi kitab-kitab dan naik ke sorga. Yang duduk di sebelah kanan Sang Bapa',
                'dan akan datang kembali dengan kemuliaan untuk menghakimi orang yang hidup dan yang mati, dan Kerajaan-Nya tak akan berakhir.',
                'Aku percaya kepada Roh Kudus yang menjadi Tuhan dan yang menghidupkan, yang keluar dari Sang Bapa dan Sang Anak, disembah dan dimuliakan,',
                'yang berfirman dengan perantaraan para nabi. Aku percaya satu gereja yang Kudus, Am dan Rasuli. Aku mengaku satu baptisan untuk pengampunan dosa.',
                'Aku menantikan kebangkitan orang mati dan kehidupan di zaman yang akan datang.',
            )],
            ['Nyanyian Pengakuan', null],
            ['Slide Bebas', 'JEMAAT DUDUK'],
            ['Slide Bebas', 'DOA SYAFAAT'],
            ['Nyanyian Jemaat', null],
            ['Slide Bebas', 'PERSEMBAHAN SYUKUR'],
            ['Nyanyian Persembahan', null],
            ['Slide Bebas', 'DOA PERSEMBAHAN'],
        ], $this->closing(), [
            ['Nyanyian Jabat Tangan', null],
        ]);
    }
}
