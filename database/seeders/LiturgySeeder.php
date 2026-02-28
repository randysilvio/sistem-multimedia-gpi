<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Liturgy;
use App\Models\LiturgyItem;
use Illuminate\Support\Facades\Schema;

class LiturgySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // KUMPULAN TEMPLATE TATA IBADAH GPI PAPUA
        // Semua item dibuat dinamis ('is_dynamic' => true) agar bisa diedit di Control Panel.
        $templates = [
            // ==========================================
            // 1. TATA IBADAH MINGGU I
            // ==========================================
            'Tata Ibadah Minggu I' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Dalam nama Bapa dan Anak dan Roh Kudus, kasih karunia dan damai sejahtera dari Allah Bapa kita dan dari Tuhan Yesus Kristus yang telah menyerahkan diriNya karena dosa-dosa kita. BagiNyalah kemuliaan selama-lamanya.\n\nJemaat : Amin."],
                ['title' => 'Introitus', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'static' => "Pnt : Saudara-saudara jemaat, jika kita berkata bahwa kita tidak berdosa, maka kita menipu diri kita sendiri dan kebenaran tidak ada didalam kita. Karena itu marilah kita merendahkan diri dihadapan Allah dan mengaku dosa-dosa kita kepadaNya. Kita berdoa :\n\n\"Ya Bapa, kami datang di hadapanMu dan mengaku bahwa kami telah melakukan hal-hal yang bertentangan dengan kehendakMu. Kami lebih cenderung melakukan keinginan kami sendiri daripada menyatakan kepatuhan kami terhadap FirmanMu. Kami tidak setia melaksanakan panggilan kami sebagai saksi-saksiMu. Kami sering mengorbankan kepentingan sesama kami demi kepentingan diri sendiri.\n\nAmpunilah dan sucikanlah kami ya Bapa, Pengasih, demi kemuliaan-Mu dalam Kristus Juruselamat kami. Amin.\""],
                ['title' => 'Nyanyian Penyesalan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berita Anugerah Pengampunan Dosa', 'is_dynamic' => true, 'static' => "PF : Demi nama Bapa, Anak dan Roh Kudus, diberitakan bahwa pengampunan dosa telah berlaku atas setiap orang yang telah mengaku dosanya dengan tulus iklas dihadapan Allah.\n\nKarena begitu besar kasih Allah akan dunia ini, sehingga Ia telah mengaruniakan AnakNya yang tunggal, supaya setiap orang yang percaya kepadaNya tidak binasa melainkan beroleh hidup yang kekal."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Hidup Baru', 'is_dynamic' => true, 'static' => "PF : Jemaat dimohon berdiri selaku tanda kesiapan.\n\nJemaat : Beritahukanlah jalan-jalanMu kepada kami ya Tuhan, tunjukkanlah itu kepada kami. Bawalah kami berjalan dalam kebenaranMu dan ajarlah kami. Sebab Engkaulah Allah Penyelamat kami.\n\nPF : Dengarlah saudara-saudara, Amanat Hidup Baru : \"Kasihilah Tuhan Allahmu dengan segenap hatimu dan dengan segenap jiwamu dan dengan segenap akal budimu. Itulah hukum yang terutama dan yang pertama. Dan hukum yang kedua yang sama dengan itu ialah : Kasihilah sesamamu manusia seperti dirimu sendiri. Pada kedua hukum ini tergantung Seluruh Hukum Taurat dan Kitab para nabi\" (Matius 22 : 37 - 40).\n\nKiranya Roh Kudus menolong kita mewujudkan kemuliaan Allah dalam hidup kita."],
                ['title' => 'Nyanyian Kemuliaan (Gloria)', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Ya Tuhan, kami akan mendengarkan FirmanMu yang adalah pelita bagi kaki kami dan terang bagi jalan kami. Berilah Roh KudusMu mendiami kami supaya kami menjadi bijaksana dan dengan hati yang teguh dapat mengamalkan FirmanMu dalam hidup kami sesehari.\n\nBerfirmanlah, ya Tuhan, sebab kami sudah sedia mendengar. Amin."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Rasuli', 'is_dynamic' => true, 'static' => "Aku percaya kepada Allah Bapa Yang Maha Kuasa, Khalik langit dan bumi.\n\nDan kepada Yesus Kristus AnakNya yang tunggal, Tuhan kita, yang dikandung dari Roh Kudus, lahir dari anak dara Maria, yang menderita dibawah pemerintahan Pontius Pilatus, disalibkan, mati dan dikuburkan, turun kedalam kerajaan maut.\n\nPada hari yang ketiga bangkit pula dari antara orang mati, naik ke sorga, duduk disebelah kanan Allah, Bapa Yang Maha Kuasa, dan akan datang dari sana untuk menghakimi orang yang hidup dan yang mati.\n\nAku percaya kepada Roh Kudus, Gereja yang kudus dan Am, Persekutuan Orang Kudus, Pengampunan Dosa, Kebangkitan Daging dan Hidup yang kekal."],
                ['title' => 'Nyanyian Pengakuan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Ajakan Untuk Memberi', 'is_dynamic' => true, 'static' => 'Dkn : Tuhan mengasihi orang yang memberi dengan suka cita.'],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Sekarang ya Allah kami, kami bersyukur kepadaMu dan memuji namaMu yang agung itu. Sebab siapakah kami, sehingga kami mampu memberikan persembahan sukarela seperti ini ? Sebab dari padaMulah segala-galanya dan dari tanganMu sendirilah persembahan yang kami berikan ini. Amin."],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Marilah kita kembali ke dalam kehidupan kita sehari-hari dan melakukan Firman Tuhan yang telah kita dengar."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Tuhan memberkati engkau dan melindungi engkau; Tuhan menyinari engkau dengan wajahNya dan memberi engkau kasih karunia ; Tuhan menghadapkan wajahNya kepadamu dan memberi engkau damai sejahtera.\n\nJemaat : Amin, Amin, Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 2. TATA IBADAH MINGGU II
            // ==========================================
            'Tata Ibadah Minggu II' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Pertolongan Kepada kita adalah di dalam nama Tuhan yang menjadikan langit dan bumi, yang tetap setia di sepanjang sejarah umat manusia dan yang tidak pernah meninggalkan perbuatan tanganNya. Damai Sejahtera Allah menyertai saudara.\n\nJemaat : Amin."],
                ['title' => 'Introitus', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'static' => "Pnt : Marilah kita merendahkan diri di hadapan Allah dan dengan rasa sesal dan malu kita mengaku dosa-dosa kita kepadaNya. Kita berdoa :\n\n\"Bapa kami yang maha kuasa, dengan muka yang tertunduk karena dosa dan kesalahan kami, kami mengaku bahwa di sepanjang hidup ini, kami sering melakukan hal-hal yang tidak memuliakan namaMu.\n\nKami sering berbicara tentang mengasihi Tuhan dan sesama kami, tetapi kami lebih cenderung untuk mengasihi diri sendiri... Kami sering berbicara tentang hukum-hukumMu yang adil, tetapi kami tidak setia untuk melakukannya di dalam kehidupan kami.\n\nKasihanilah kami dan bebaskanlah kami dari segala hutang dosa yang telah kami lakukan. Baharuilah langkah hidup kami dan berilah kami kesanggupan untuk mengabdi dengan setia kepadaMu. Demi Kristus, Tuhan dan Juruselamat kami. Amin.\""],
                ['title' => 'Berita Anugerah Pengampunan Dosa', 'is_dynamic' => true, 'static' => "PF : Tuhan itu penyayang dan pengasih, panjang sabar dan berlimpah kasih setia. Bukan untuk seterusnya Ia menuntut dan bukan untuk selama-lamanya Ia mendendam.\n\nTidak dilakukannya kepada kita setimpal dengan dosa kita, dan tidak dibalasnya kepada kita setimpal dengan kesalahan kita. Tetapi setinggi langit di atas bumi, demikian besarnya kasih setiaNya atas orang-orang yang takut akan Dia. Sejauh Timur dari Barat, dijauhkanNya dari pada kita pendurhakaan kita. Seperti bapa sayang kepada anak-anaknya, demikian Tuhan sayang akan orang-orang yang takut kepadaNya (Maz.103:8-13)."],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Ya Tuhan, bukalah pikiran kami dan terangilah dengan Roh Kudus sehingga kami mengerti kehendak Tuhan di dalam firman yang akan kami baca. Amin."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Rasuli', 'is_dynamic' => true, 'static' => "Aku percaya kepada Allah Bapa Yang Maha Kuasa, Khalik langit dan bumi...\n\n(Lanjutkan Pengakuan Iman)"],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Ajakan Untuk Memberi', 'is_dynamic' => true, 'static' => 'Dkn : Tuhan mengasihi orang yang memberi dengan suka cita.'],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Ya Tuhan, terimalah persembahan syukur kami ini, yaitu umatMu yang telah Tuhan selamatkan. Jadikanlah persembahan syukur ini, suatu berkat bagi pelayanan kasih dan keadilan. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Kembalilah ke dalam kehidupanmu sehari-hari dan lakukan Firman Tuhan."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Kasih Karunia Tuhan Yesus Kristus dan Kasih Allah dan persekutuan Roh Kudus, menyertai saudara-saudara.\n\nJemaat : Amin, Amin, Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 3. TATA IBADAH MINGGU III
            // ==========================================
            'Tata Ibadah Minggu III' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Dalam nama Bapa dan Anak dan Roh Kudus. Kasih karunia dan damai sejahtera dari Allah Bapa kita dan dari Tuhan Yesus Kristus menyertai saudara sekalian.\n\nJemaat : Amin."],
                ['title' => 'Introitus', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'static' => "Pnt : Sungguh, dosa pemberontakan kami banyak dihadapanMu & dosa kami bersaksi melawan kami. Sungguh, kami menyadari pemberontakan kami & kami mengenal kejahatan kami.\n\nKami telah memberontak dan mungkir terhadap Tuhan serta berbalik dari mengikut Allah kami. Kami merencanakan pemerasan & penyelewengan, mengandung dusta dalam kata-kata (Yesaya 59 : 12 – 13).\n\nOleh karena itu kami mohon ya Tuhan, ampunilah segala dosa kami, dalam nama Tuhan Yesus Kristus. Amin."],
                ['title' => 'Berita Anugerah Pengampunan Dosa', 'is_dynamic' => true, 'static' => "PF : Dengarlah saudara-saudara Berita Anugerah Pengampunan Dosa, \"Sekalipun dosamu merah seperti kermisi, akan menjadi putih seperti salju, sekalipun berwarna merah seperti kain kesumba, akan menjadi putih seperti bulu domba\"."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Hidup Baru', 'is_dynamic' => true, 'static' => "PF : Dengarlah Amanat Hidup Baru : Karena itu sebagai orang-orang pilihan Allah yang dikuduskan dan dikasihiNya, kenakanlah belas kasihan, kemurahan, kerendahan hati, kelemahlembutan dan kesabaran.\n\nSabarlah kamu seorang terhadap yang lain, dan ampunilah seorang akan yang lain apabila yang seorang menaruh dendam terhadap yang lain, sama seperti Tuhan telah mengampuni kamu, kamu perbuat jugalah demikian. Dan diatas semuanya itu: kenakanlah kasih, sebagai pengikat yang mempersatukan dan menyempurnakan.\n\nHendaklah damai sejahtera Kristus memerintah dalam hatimu, karena untuk itulah kamu telah dipanggil menjadi satu tubuh. Dan bersyukurlah.\n\n\"Kiranya Roh Kudus menolong kita mewujudkan kemuliaan Allah dalam hidup kita\""],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "Menyanyikan PKJ 15. ”Kusiapkan Hatiku Tuhan”\n\nKusiapkan hatiku Tuhan \nMenyambut FirmanMu, saat ini\nAku sujud menyembah Engkau\nDalam hadiratMu, saat ini..."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Atanasius', 'is_dynamic' => true, 'static' => "Barang siapa hendak menjadi selamat, pertama ia harus memegang iman yang am ; jikalau seseorang tidak memeliharanya dengan sebulat semurninya, niscaya ia akan binasa.\n\nAdapun iman yang am adalah ini : bahwa kita menyembah suatu Allah dalam ke-Tigaan dan ke-Tigaan dalam ke-Satuan; tanpa mengaduk oknum, tanpa menceraikan tabiat.\n\nMemang oknum Bapa adalah lain ; oknum anak adalah lain ; oknum Roh Kudus adalah lain ; akan tetapi Bapa, Anak, dan Roh Kudus ke-AllahNya satu; kehormatanNya sama, kemuliaanNya seabadi..."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Ajakan Untuk Memberi', 'is_dynamic' => true, 'static' => "Dkn : Saudara-saudara sebelum kita memberikan tanda kasih lewat persembahan syukur ini, terlebih dahulu dengarkanlah ajakan untuk memberi : \"Janganlah menghadap Tuhan dengan tangan hampa, tetapi masing-masing dengan sekedar persembahan, sesuai dengan berkat yang diberikan kepadamu oleh Tuhan, Allahmu\" (Ulangan 16 : 16b - 17)"],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Ya Kristus, Allah kami, sebagai tanda syukur kepadaMu kami memberikan dari apa yang kami miliki. Berkati persembahan ini, kiranya berkenan bagi perluasan pekerjaanMu di muka bumi ini. Amin."],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Marilah kita pulang dan menjadi saksi bagiNya ditempat kita berada."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Jemaat Tuhan, arahkanlah hati dan pikiranmu kepada Allah, dan terimalah berkatNya:\nAnug'rah Tuhan kita, Yesus Kristus, pengasihan Allah, persekutuan dalam Roh Kudus kiranya menyertai kita. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 4. TATA IBADAH MINGGU IV / V
            // ==========================================
            'Tata Ibadah Minggu IV / V' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Asal dan sumber hidup kita ialah Tuhan yang menjadikan langit dan bumi. Kasih setia-Nya tidak pernah berakhir dan tuntunan tangan-Nya senantiasa menyertai kita. Damai sejahtera Allah menyertai saudara-saudara.\n\nJemaat : Dan menyertai saudara juga. Amin."],
                ['title' => 'Introitus', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'static' => "Pnt : Saudara-saudara jemaat, selaku orang-orang berdosa marilah kita tunduk di hadapan Allah dan mengaku dosa-dosa kita kepada-Nya.\nPnt : Kasihanilah kami, ya Allah, menurut kasih setia-Mu\nJemaat : Hapuskanlah pelanggaran kami, menurut rahmat-Mu yang besar.\nPnt : Bersihkanlah kami seluruhnya dari kesalahan kami, tahirkanlah kami dari dosa-dosa kami.\nJemaat : Sebab kami sadar akan pelanggaran kami, kami senantiasa bergumul dengan dosa-dosa kami.\nPnt : Terhadap Engkau, terhadap Engkau sajalah kami telah berdosa.\nJemaat : Dan melakukan apa yang Kau anggap jahat\nPnt : Ampunilah kami di dalam Putera-Mu, Tuhan dan Juruselamat kami."],
                ['title' => 'Berita Anugerah Pengampunan Dosa', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara jemaat, selaku orang-orang yang telah diampuni dosa-dosanya, dengarkanlah sekarang berita anugerah pengampunan dosa :\n\n\"Maka Ia yang tiada mengenal dosa telah dibuat-Nya menjadi dosa karena kita, supaya dalam Dia kita dibenarkan oleh Allah\" (II Korintus 5 : 21)"],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Hidup Baru', 'is_dynamic' => true, 'static' => "PF : Siapkanlah akal budimu, waspadalah dan letakanlah pengharapanmu seluruhnya atas kasih karunia yang dianugerahkan kepadamu pada waktu pernyataan Yesus Kristus.\n\nHiduplah sebagai anak-anak yang taat dan jangan turuti hawa nafsu yang menguasai kamu pada waktu kebodohanmu, tetapi hendaklah kamu menjadi kudus di dalam seluruh hidupmu, sama seperti Dia yang kudus, yang telah memanggil kamu."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Jemaat yang Tuhan Yesus kasihi, mari kita berdoa mohon tuntunan Roh Kudus untuk mendengar firman Tuhan dengan menyanyikan KJ.No.56 \"Datanglah Kepadaku, Ya Roh Kudus\""],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Rasuli', 'is_dynamic' => true, 'static' => "Aku percaya kepada Allah Bapa Yang Maha Kuasa, Khalik langit dan bumi..."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Ajakan Untuk Memberi', 'is_dynamic' => true, 'static' => "Dkn : Kristus telah mati untuk semua orang, supaya mereka yang hidup tidak lagi hidup untuk dirinya sendiri, tetapi hidup untuk Dia, yang telah mati dan telah dibangkitkan untuk mereka."],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Tuhan Allah, sumber berkat kami bersyukur karena kami dapat memberikan sebagian dari berkat-Mu bagi kami, untuk kelanjutan kesaksian dan pelayanan Gereja Tuhan. Berkatilah hidup kami selanjutnya agar itu menjadi persembahan yang hidup dan kudus di hadapan-Mu."],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Kembalilah ke dalam hidupmu sehari-hari dan lakukanlah firman Tuhan."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Allah sumber kasih karunia, yang telah memanggil kamu dalam Kristus kepada kemuliaanNya yang kekal, akan melengkapi, meneguhkan dan mengokohkan kamu. Ialah yang empunya kuasa sampai selama-lamanya.\n\nJemaat : Amin. Amin. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 5. TATA IBADAH PENEGUHAN SIDI (LENGKAP)
            // ==========================================
            'Tata Ibadah Peneguhan Sidi' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Prosesi / Nyanyian Persiapan', 'is_dynamic' => true, 'static' => "Prosesi Calon Sidi Baru memasuki ruang ibadah."],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Dalam nama BAPA dan ANAK dan ROH KUDUS, kasih karunia dan damai sejahtera dari Allah Bapa Kita dan dari Tuhan Yesus Kristus, menyertai saudara sekalian.\n\nJemaat : Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'static' => "Pnt : Saudara-saudara jemaat, jika kita berkata bahwa kita tidak berdosa, maka kita menipu diri kita sendiri dan kebenaran tidak ada di dalam kita. Karena itu marilah kita merendahkan diri dihadapan Allah dan mengaku dosa-dosa kita kepadaNya.\n\n\"Ya Bapa, kami datang dihadapan-Mu dan mengaku bahwa kami telah melakukan hal-hal yang bertentangan dengan kehendak-Mu. Kami lebih cenderung melakukan keinginan kami sendiri daripada menyatakan kepatuhan kami terhadap FirmanMu.\n\nAmpunilah dan sucikanlah kami ya Bapa, Pengasih, demi kemuliaaanMu dalam Kristus Juruselamat kami. Amin."],
                ['title' => 'Nyanyian Penyesalan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berita Anugerah Pengampunan Dosa', 'is_dynamic' => true, 'static' => "PF : Sekalipun dosamu merah seperti kermisi, akan menjadi putih seperti salju; sekalipun berwarna merah seperti kain kesumba, akan menjadi putih seperti bulu domba, (Yesaya 1 :8). Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Hidup Baru', 'is_dynamic' => true, 'static' => "PF : Marilah kita satukan kerinduan kita dalam menyambut Amanat Hidup Baru. Sabda Yesus : \"Kasihilah Tuhan Allahmu dengan segenap hatimu dan dengan segenap jiwamu dan dengan segenap akal budimu. Itulah hukum yang terutama dan yang pertama.\n\nDan hukum yang kedua yang sama dengan itu ialah : Kasihilah sesamamu manusia seperti dirimu sendiri. Pada kedua hukum ini tergantung seluruh Hukum Taurat dan Kitab Para nabi\" (Matius 22 : 37 – 40).\n\nKiranya Roh Kudus menolong kita mewujudkan kemuliaan Allah dalam hidup kita."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Ya Roh Kudus, terangilah akal-budi kami dan tuntunlah pengertian kami, agar FirmanMu dapat kami mengerti, dan dengan hati teguh kami laksanakan dalam kehidupan kami. Amin."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Pengajaran Sidi', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara jemaat, telah kita ketahui bahwa hari ini akan diteguhkan saudara/i menjadi anggota sidi Gereja. Dengarkanlah pengajaran dan nasehat pokok dari para Rasul:\n\n\"Kamu telah menerima Kristus Yesus Tuhan kita, karena itu hendaklah hidupmu tetap di dalam Dia. Hendaklah kamu berakar di dalam Dia dan dibangun di atas Dia. Hendaklah kamu bertambah teguh didalam iman yang telah diajarkan kepadamu, dan hendaklah hatimu melimpah dengan syukur\" (Kolose 2:6,7).\n\nDan biarlah kamu juga dipergunakan sebagai batu hidup untuk pembangunan rumah rohani, bagi suatu imamat kudus..."],
                ['title' => 'Pengakuan Calon Sidi', 'is_dynamic' => true, 'static' => "PF : Para calon sidi gereja saya persilahkan berdiri. Jawablah pertanyaan-pertanyaan berikut ini dengan tulus ikhlas:\n\nPertama : Apakah saudara-saudara percaya kepada Allah, pencipta langit dan bumi, dan kepada Yesus Kristus Anak Allah yang Tunggal, Tuhan kita dan kepada Roh Kudus?\n\nKedua : Apakah saudara-saudara dengan sepenuh hati bersedia menyatakan iman saudara-saudara secara nyata dalam kehidupan setiap hari?\n\nKetiga : Apakah saudara-saudara bersedia dan berjanji menjadi anggota Sidi GPI Papua dan setia mengambil bagian dalam pembangunan Jemaat Tuhan?\n\nCalon Sidi : YA, DENGAN SEGENAP HATIKU."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Rasuli', 'is_dynamic' => true, 'static' => "PF : Jemaat diundang berdiri dan marilah kita satukan pengakuan kita dengan bersama-sama mengikrarkan pengakuan Iman Rasuli.\n\nAku percaya kepada Allah Bapa Yang Maha Kuasa, Khalik langit dan bumi..."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk - Calon Sidi Berlutut)'],
                ['title' => 'Peneguhan Sidi', 'is_dynamic' => true, 'static' => "PF : Berdasarkan pengakuan dan kesediaan saudara-saudara, sebagai pelayan Yesus Kristus saya meneguhkan saudara-saudara menjadi Anggota Sidi Gereja Protestan Indonesia di Papua.\n\n(Penumpangan Tangan) : \"Allah sumber segala kasih-karunia yang memanggil kamu dalam Kristus Yesus kepada kemuliaan yang kekal, melengkapi, meneguhkan dan mengokohkan kamu. Dia yang empunya kuasa selama-lamanya\". Amin."],
                ['title' => 'Nyanyian Peneguhan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nasehat dan Penyerahan', 'is_dynamic' => true, 'static' => "PF : Ingatlah kata-kata Yesus, Tuhan kita : \"Aku memberi perintah baru kepada kamu, yaitu supaya kamu saling mengasihi, sama seperti Aku telah mengasihi kamu demikian pula kamu harus saling mengasihi.\"\n\nSaudara-saudara jemaat, kita telah menyaksikan dan mendengar pengakuan iman saudara kita ini. Karena itu terimalah mereka dalam kasih dan pelayanan bersama, karena kita adalah satu di dalam Tuhan."],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Ajakan Memberi', 'is_dynamic' => true, 'static' => "Dkn : Bawalah seluruh persembahan perpuluhan itu ke dalam rumah perbendaharaan, supaya ada persediaan makanan di rumahKu dan ujilah Aku, Firman Tuhan semesta alam, apakah Aku tidak membukakan bagimu tingkap-tingkap langit dan mencurahkan berkat kepadamu sampai berkelimpahan ?"],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Ya Tuhan, terimalah persembahan syukur kami ini, yaitu umatMu yang telah Tuhan selamatkan. Jadikanlah persembahan syukur ini, suatu berkat bagi pelayanan kasih dan keadilan, Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Marilah kita pulang dan menjadi saksi bagiNya di tempat kita berada."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Tuhan memberkati engkau dan melindungi engkau, Tuhan menyinari engkau dengan wajah-Nya dan memberi engkau kasih karunia; Tuhan menghadapkan wajah-Nya kepadamu dan memberi engkau Damai Sejahtera.\n\nJemaat : Amin. Amin. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 6. TATA IBADAH PERJAMUAN KUDUS
            // ==========================================
            'Tata Ibadah Perjamuan Kudus' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Dalam nama Bapa dan Anak dan Roh Kudus. Kasih karunia dan damai sejahtera dari Allah Bapa kita, dan dari Tuhan Yesus Kristus yang telah menyerahkan diriNya karena dosa-dosa kita. BagiNyalah kemuliaan selama-lamaNya.\n\nJemaat : Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Titah Perjamuan & Arti', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara, dengarlah Titah Perjamuan Kudus, sebagaimana dituliskan oleh Rasul Paulus dalam I Korintus 11 : 23-26.\n\nSetiap kali kita merayakan perjamuan kudus, maka kita bersama-sama mengingat kembali karya Tuhan yang telah diwujudkan dikayu salib. Roti dan cawan ini adalah suatu peringatan akan persekutuan antara Allah dengan kita."],
                ['title' => 'Pengarahan Hati & Pengakuan Iman', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara, Supaya kita dipelihara oleh roti sorgawi, yakni Yesus Kristus, janganlah kita melihat pada roti dan anggur yang kelihatan ini, tetapi hendaklah dengan iman kita mengarahkan hati kita kepada Yesus Kristus, Tuhan dan Juruselamat kita.\n\n(Dilanjutkan dengan Pengakuan Iman)"],
                ['title' => 'Pelayanan Roti dan Anggur', 'is_dynamic' => true, 'static' => "PF : Marilah karena segala sesuatu sudah tersedia. Ya Tuhan, kasihanilah kami orang berdosa ini.\n\n(Sambil memecah-mecahkan roti) : Roti yang kami pecah-pecahkan ini adalah tanda persekutuan dengan Tubuh Kristus. Ambillah, makanlah, ingat dan percayalah...\n\n(Sambil mengangkat cawan/sloki) : Cawan minuman ini adalah tanda persekutuan dengan darah Kristus. Ambillah, minumlah, ingat dan percayalah..."],
                ['title' => 'Ungkapan Syukur', 'is_dynamic' => true, 'static' => "PF : Pujilah Tuhan hai jiwaku\nJ : PUJILAH NAMANYA YANG KUDUS, HAI SEGENAP BATINKU\nPF : Pujilah Tuhan, hai jiwaku\nJ : DAN JANGANLAH LUPAKAN SEGALA KEBAIKANNYA\nPF : Dia yang mengampuni segala kesalahanmu\nJ : YANG MENYEMBUHKAN SEGALA PENYAKITMU..."],
                ['title' => 'Ajakan Memberi & Nyanyian Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Jemaat, marilah kita memberikan persembahan syukur kepada Allah, seraya mengingat kata Alkitab : \"Berilah kepada Tuhan kemuliaan namaNya, bawalah persembahan dan masuklah ke pelataranNya\" (Mzm 96 : 8)."],
                ['title' => 'Doa Persembahan & Doa Syafaat', 'is_dynamic' => true, 'static' => "Dkn : Ya Tuhan, terimalah persembahan syukur kami ini, yaitu umatMu yang telah Tuhan selamatkan. Jadikanlah persembahan syukur ini suatu berkat bagi pelayanan, kasih dan keadilan. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara, jika kita harus hidup di dunia ini, maka bagi kita itu berarti bahwa kita harus bekerja menghasilkan buah bagi kemuliaan Allah, Bapa Tuhan kita Yesus Kristus."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Tuhan memberkati engkau dan melindungi engkau; Tuhan menyinari engkau dengan wajahNya dan memberi engkau kasih karunia. Tuhan menghadapkan wajahNya kepadamu dan memberi engkau damai sejahtera.\n\nJemaat : Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 7. TATA IBADAH PEMBERKATAN NIKAH / PERTUNANGAN
            // ==========================================
            'Tata Ibadah Pemberkatan Nikah / Pertunangan' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Prosesi / Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Pertolongan kepada kita adalah di dalam nama Tuhan yang menjadikan langit dan bumi yang tetap setia disepanjang sejarah umat manusia dan yang tidak pernah meninggalkan perbuatan tanganNya. Damai Sejahtera Allah menyertai saudara-saudara. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Ya Bapa yang Maha Kuasa, Ya Kristus Maha Pengasih, karuniakan kami Roh KudusMu yang memimpin kami mendalami segala kekayaan FirmanMU dan melakukannya dalam hidup beriman. Amin."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Dasar & Janji Pernikahan / Pertunangan', 'is_dynamic' => true, 'static' => "PF : Pernikahan memang dikehendaki Allah bahkan dibentuk oleh Allah sendiri. Karena itu apa yang telah dipersatukan Allah, hendaknya tidak diceraikan oleh manusia (Mrk 10 : 6 – 9).\n\n(Dilanjutkan dengan pengucapan janji: \"Saya menyambut engkau sebagai suami/isteriku, dan berjanji bahwa saya akan tetap setia kepadamu...\")"],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Rasuli', 'is_dynamic' => true, 'static' => "Aku percaya kepada Allah Bapa Yang Maha Kuasa, Khalik langit dan bumi..."],
                ['title' => 'Pemasangan Cincin & Akta Pemberkatan', 'is_dynamic' => true, 'static' => "Mempelai : Saya mengambil engkau selaku suami/isteriku yang sah dan memberikan cincin ini sebagai lambang cinta kasih dan kesetiaanku kepadamu.\n\nPF : Berdasarkan kasih setia Yesus Kristus, kini pernikahanmu diteguhkan / dikukuhkan dalam nama Bapa, Anak dan Roh Kudus. Kenakanlah kasih sebagai pengikat yang mempersatukan dan menyempurnakan."],
                ['title' => 'Berkat Nikah', 'is_dynamic' => true, 'static' => "PF : Terimalah berkat Tuhan : Allah, Bapa kita yang rahmani, yang memanggil kamu ke dalam persekutuan nikah kudus ini, memenuhi kamu dengan kasih karuniaNya, dan memberkati kamu, supaya dalam iman, kasih dan kesatuan, kamu dapat hidup suci, langgeng dan bahagia. Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Ya Tuhan, terimalah persembahan syukur kami ini yaitu umatMu yang telah Engkau selamatkan. Jadikanlah persembahan syukur ini, suatu berkat bagi pelayanan kasih dan keadilan, Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan & Berkat', 'is_dynamic' => true, 'static' => "PF : Jemaat, pulanglah dengan damai sejahtera dan jadilah pelaku Firman.\n\nTuhan memberkati engkau dan melindungi engkau. Tuhan menyinari engkau dengan wajahNya dan memberi engkau kasih karunia. Tuhan menghadapkan wajahNya kepadamu dan memberi engkau damai sejahtera.\n\nJemaat : Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh)'],
            ],

            // ==========================================
            // 8. TATA IBADAH PENAHBISAN MAJELIS / TUAGAMA
            // ==========================================
            'Tata Ibadah Penahbisan Majelis / Tuagama' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Prosesi / Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Pertolongan kepada kita adalah di dalam nama Tuhan yang menjadikan langit dan bumi... Salam sejahtera dari Allah Bapa kita dan dari Tuhan Yesus Kristus menyertai saudara-saudara."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Pembacaan SK & Laporan', 'is_dynamic' => true, 'static' => "Pembacaan Surat Keputusan BP Sinode / BP Klasis dan Pembacaan Laporan Umum Majelis Jemaat (Pelepasan Majelis Jemaat yang Lama)."],
                ['title' => 'Pengajaran Jabatan & Janji', 'is_dynamic' => true, 'static' => "PF : Dengarlah sekarang beberapa hal pokok mengenai jabatan Penatua dan Diaken. Tugas Penatua antara lain membantu gembala memimpin dan membina Jemaat. Tugas Diaken antara lain Pelayanan Pengasihan terhadap Jemaat dan dunia...\n\nAdakah saudara-saudara percaya bahwa Allah telah berkenan memanggil saudara-saudara...? \nCalon : YA DENGAN SEGENAP HATIKU."],
                ['title' => 'Pengakuan Iman', 'is_dynamic' => true, 'static' => "Aku percaya kepada Allah Bapa Yang Maha Kuasa..."],
                ['title' => 'Penahbisan & Penumpangan Tangan', 'is_dynamic' => true, 'static' => "PF : Dalam nama BAPA dan ANAK dan ROH KUDUS, saya menahbiskan saudara-saudara dalam jabatan Penatua dan Diaken...\n\nTerimalah Roh Kudus untuk jabatan dalam Gereja Kristus yang dipercayakan kepada saudara-saudara dengan penumpangan tangan. \"Allah sumber segala damai sejahtera, kiranya melengkapi saudara-saudara...\""],
                ['title' => 'Nasehat kepada Majelis / Tuagama', 'is_dynamic' => true, 'static' => "PF : Hai saudara-saudara Penatua, hendaklah dengan rajin dan setia saudara-saudara melaksanakan tugasmu. Hai saudara-saudara Diaken, tolonglah orang-orang sakit, orang-orang miskin dan yang berdukacita..."],
                ['title' => 'Doa Syafaat & Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Amanat Pengutusan & Berkat', 'is_dynamic' => true, 'static' => "PF : Kembalilah ke dalam hidupmu sehari-hari dan lakukanlah Firman Tuhan. Tuhan memberkati engkau..."],
            ],

            // ==========================================
            // 9. TATA IBADAH PELANTIKAN KOMISI / PANITIA
            // ==========================================
            'Tata Ibadah Pelantikan Komisi / Panitia' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Pembacaan Alkitab & Khotbah', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Pengajaran Pelantikan', 'is_dynamic' => true, 'static' => "PF : Pada dasarnya semua Anggota Gereja terpanggil untuk bekerja di ladang Tuhan. Namun ada Anggota Gereja yang dipanggil untuk mengemban suatu tanggungjawab khusus, yaitu mereka yang dilantik sebagai Anggota Komisi, Badan, atau Panitia.\n\nWalaupun tugas dan kewenangan berbeda-beda, namun semua mempunyai satu maksud dan tujuan, yaitu bekerja untuk Tuhan."],
                ['title' => 'Pembacaan SK & Pertanyaan', 'is_dynamic' => true, 'static' => "PF : Adakah saudara-saudara percaya bahwa Allah telah berkenan memanggil saudara-saudara untuk menjadi anggota Komisi/Badan/Panitia...?\n\nCalon : Ya, saya mengakui dan berjanji dengan segenap hati."],
                ['title' => 'Pengakuan Iman', 'is_dynamic' => true, 'static' => "Aku percaya kepada Allah Bapa Yang Maha Kuasa..."],
                ['title' => 'Pelantikan', 'is_dynamic' => true, 'static' => "PF : Dalam nama BAPA dan ANAK dan ROH KUDUS, saya melantik saudara-saudara anggota Komisi/Badan/Panitia masa bakti...... \n\nSemoga Allah Bapa kita dalam Yesus Kristus, yang telah memanggil saudara-saudara dalam pekerjaan pelayanan ini, menerangi saudara-saudara dengan Roh Kudus, supaya saudara-saudara berbuah bagi Allah. Amin."],
                ['title' => 'Doa Syafaat & Persembahan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Amanat Pengutusan & Berkat', 'is_dynamic' => true, 'static' => null],
            ],

            // ==========================================
            // 10. TATA IBADAH PEMAKAMAN / PENGHIBURAN
            // ==========================================
            'Tata Ibadah Pemakaman / Penghiburan' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Nyanyian Persiapan', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Votum & Salam', 'is_dynamic' => true, 'static' => "PF : Asal & sumber hidup kita, hanyalah Tuhan, pencipta langit dan bumi, kasih setiaNya tidak pernah berakhir, bagiNyalah kemuliaan dan kuasa yang kekal.\n\nJemaat : Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Ya Tuhan, sumber penghiburan dan kekuatan, kami hendak mendengarkan suara-Mu lewat kebenaran firman-Mu. Tolong kami dengan kuasa Roh Kudus-Mu, agar firman-Mu menghibur & menguatkan kami. Amin."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Khotbah / Refleksi', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan & Penghiburan'],
                ['title' => 'Penguatan Iman', 'is_dynamic' => true, 'static' => "PF : Kami ingin saudara-saudara agar kamu mengetahui tentang mereka yang meninggal, supaya kamu jangan berduka cita seperti orang-orang lain yang tidak percaya bahwa Yesus Kristus telah mati dan telah bangkit. KarenaNya kita percaya bahwa mereka yang meninggal didalam Yesus Kristus, akan dikumpulkan Allah bersama-sama dengan Dia (1 Tesalonika 4 : 13, 14)."],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Pengantar / Pelepasan', 'is_dynamic' => true, 'static' => "PF : Dunia bukan negeri kita yang kekal. Sebagai umat yang percaya kita mengharapkan untuk kelak mendiami negeri yang akhir itu. Marilah kita membawa tubuh saudara kita ini, dan mengantarnya ke tempat peristirahatannya yang terakhir. Jiwanya kita serahkan kepada Allah, Tuhan akan menjaga keluar masukmu dari sekarang sampai selama-lamanya."],
                ['title' => 'Nyanyian Jemaat (Menuju Pemakaman)', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Di Pemakaman: Pelepasan & Berkat', 'is_dynamic' => true, 'static' => "PF : Tanah memang asal tubuh manusia, sebab itu ia kembali menjadi tanah. Akan tetapi Yesus sumber kebangkitan akan menghidupkan orang yang percaya kepada kehidupan yang kekal.\n\nAllah sumber damai sejahtera menguduskan kamu & memelihara kamu dengan tidak bercacat sampai kedatangan kembali Tuhan Yesus Kristus. Amin."],
            ],
            
            // ==========================================
            // 11. TATA IBADAH MINGGU PRA PASKAH III (BARU)
            // ==========================================
            'Tata Ibadah Minggu Pra Paskah III' => [
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Penyalaan Lilin / Prolog', 'is_dynamic' => true, 'static' => "Narator : “Di jalan penderitaan-Nya, Kristus berjalan menuju salib bukan untuk diri-Nya, melainkan untuk memulihkan hidup manusia. Ia merendahkan diri, taat sampai mati, bahkan sampai mati di kayu salib. Hari ini, ketika lilin Minggu Sengsara kedua dinyalakan, kita diingatkan: Kasih-Nya begitu besar, sehingga kita yang berdosa dibenarkan bukan karena usaha kita, melainkan karena iman kepada-Nya. Biarlah cahaya lilin ini mengingatkan kita untuk hidup dalam pertobatan, mengiring Yesus dengan pengabdian tanpa pamrih, sebab Dialah terang yang tidak pernah padam di tengah gelapnya dunia.”"],
                ['title' => 'Votum dan Salam', 'is_dynamic' => true, 'static' => "PF : Pertolongan kita adalah dalam nama Tuhan, yang menjadikan langit dan bumi. Kasih karunia dan damai sejahtera dari Allah Bapa, dan dari Tuhan Yesus Kristus menyertai saudara-saudara sekalian.\n\nJ : Dan menyertai saudara juga.\n\nPF + J : Amin"],
                ['title' => 'Introitus', 'is_dynamic' => true, 'static' => null],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => "NYANYIAN JEMAAT. KJ. NO. 183 : 1 “MENJULANG NYATA ATAS BUKIT KALA”\n\nMenjulang nyata atas bukit kala t'rang benderang salibMu, Tuhanku.\nDari sinarnya yang menyala-nyala memancarkan kasih agung dan restu.\nSeluruh umat insan menengadah ke arah cahya kasih yang mesra.\nBagai pelaut yang karam merindukan di ufuk timur pagi merekah."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Pengakuan Dosa', 'is_dynamic' => true, 'static' => "Pnt : Saudara-saudara yang kekasih dalam Yesus Kristus, kita datang ke hadapan Allah dengan hati yang hancur, penuh kerendahan hati sambil mengakui segala dosa dan kesalahan kita. Kita berdoa:\n\nPnt : Ya Tuhan, Engkau yang kudus dan benar, kami mengaku bahwa hidup kami sering jauh dari kehendak-Mu.\nJ : Ampunilah kami, ya Yesus Kristus.\nPnt : Kami lebih sering mencari kepentingan diri daripada mengasihi sesama.\nJ : Kasihanilah kami, ya Yesus Kristus.\nPnt : Kami tahu apa yang baik, tetapi kami tidak melakukannya. Sebaliknya, kami sering melakukan apa yang Kau larang.\nJ : Ampunilah kami, ya Yesus Kristus.\nPnt : Di jalan sengsara-Mu, Engkau taat sampai mati di kayu salib. Tetapi kami sering tidak setia dalam mengikut Engkau.\nJ : Kasihanilah kami, ya Yesus Kristus.\nPnt : Kami datang dengan hati yang hancur, menyerahkan diri kepada-Mu.\nJ : Terimalah kami, ya Yesus Kristus, dan pulihkan hidup kami.\nPnt : Ya Allah, Bapa yang penuh kasih, kami mengaku bahwa sering kali kami lebih mengandalkan usaha kami sendiri daripada hidup oleh iman. Kami masih membanggakan kebaikan kami, seakan-akan itu bisa menyelamatkan. Kami sering lalai mengasihi sesama, cepat marah, dan lambat mengampuni. Ampunilah kami, ya Tuhan. Pulihkanlah hidup kami, supaya kami hidup hanya karena kasih karunia-Mu di dalam Kristus Yesus. Amin.\nJ : Amin"],
                ['title' => 'Nyanyian Pengakuan', 'is_dynamic' => true, 'static' => "NYANYIAN BERSAMA KJ. NO. 27 : 1, 3 “MESKI TAK LAYAK DIRIKU”\n\n1. Meski tak layak diriku, tetapi kar'na darahMu\ndan kar'na kau memanggilku, 'ku datang, Yesus, padaMu.\n\n3. Terombang-ambing, berkeluh, gentar di kancah kemelut,\nya Anakdomba Allahku, ku datang kini padaMu."],
                ['title' => 'Berita Anugerah Pengampunan Dosa', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara yang kekasih dalam Kristus, dengarlah kabar sukacita Injil! Firman Tuhan berkata: “Namun aku hidup, tetapi bukan lagi aku sendiri yang hidup, melainkan Kristus yang hidup di dalam aku. Dan hidupku yang kuhidupi sekarang di dalam daging, adalah hidup oleh iman dalam Anak Allah yang telah mengasihi aku dan menyerahkan diri-Nya untuk aku.” (Galatia 2:20)\n\nJuga firman Tuhan berkata: “Sekalipun dosamu merah seperti kirmizi, akan menjadi putih seperti salju; sekalipun berwarna merah seperti kain kesumba, akan menjadi putih seperti bulu domba.” (Yesaya 1:18)"],
                ['title' => 'Nyanyian Anugerah', 'is_dynamic' => true, 'static' => "NYANYIAN BERSAMA. “TRIMA KASIH PADA TUHAN”\n\nTrima kasih pada Tuhan, hidupku diselamatkan\nHatiku menjadi suci, kar’na dosaku diampuni"],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Petunjuk Hidup Baru', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara yang telah menerima anugerah pengampunan di dalam Kristus, sekarang kita dipanggil untuk hidup sebagai manusia baru. Sebagai tanda kesiapan menyambut Amanat Hidup Baru, Jemaat kekasih Tuhan dimohon berdiri. Firman Tuhan menegaskan: “Sebab kamu telah mati dan hidupmu tersembunyi bersama dengan Kristus di dalam Allah. Karena itu matikanlah dalam dirimu segala sesuatu yang duniawi: percabulan, kenajisan, hawa nafsu, nafsu jahat, dan juga keserakahan, yang sama dengan penyembahan berhala.” (Kolose 3:3,5)\n\nJuga Firman Tuhan berkata: “Janganlah kamu menjadi serupa dengan dunia ini, tetapi berubahlah oleh pembaharuan budimu, sehingga kamu dapat membedakan manakah kehendak Allah: apa yang baik, yang berkenan kepada Allah dan yang sempurna.” (Roma 12:2)\n\nMarilah kita hidup sebagai orang yang telah ditebus Kristus: meninggalkan cara hidup lama, dan berjalan dalam kasih, kebenaran, dan pengabdian tanpa pamrih. Itulah tanda kita mengikut Yesus di jalan salib-Nya. Kiranya Roh Kudus menolong kita mewujudkan kemuliaan Allah dalam hidup kita."],
                ['title' => 'Nyanyian Gloria', 'is_dynamic' => true, 'static' => "NYANYIAN BERSAMA: GLORIA BESAR\n\nKemuliaan bagi Allah, di tempat yang Mahatinggi\nDan damai Sejahtera di bumi,\ndiantara manusia yang berkenan kepada-Nya."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Doa Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "PF : Ya Tuhan, bukalah hati dan pikiran kami dengan terang Roh Kudus, supaya saat firman-Mu dibacakan dan diberitakan, kami boleh mengerti kehendak-Mu dan melakukannya dalam hidup kami. Demi Kristus Yesus, Sang Firman Hidup kami mohon:\n\nJ : Bersabdalah TUHAN kami mendengarkan\nBersabdalah TUHAN kami mendengarkan."],
                ['title' => 'Pembacaan Alkitab', 'is_dynamic' => true, 'static' => "Pnt : Pembacaan Firman Tuhan diMinggu Pra-Paskah Kedua hari ini terdapat di dalam Galatia 2 : 15 – 21\n\nPnt : (Setelah Pembacaan Firman Tuhan) \"Demikianlah Sabda TUHAN\".\n\nPF : Syukur kepada Allah yang memberi kita firman-Nya. Marilah kita membuka hati, supaya di tengah jalan salib Kristus, hidup kita diperbarui oleh kebenaran-Nya. Terpujilah Yesus Kristus. Hosiana.\n\nJ : Hosiana………….Hosiana……………. Hosiana…….."],
                ['title' => 'Khotbah', 'is_dynamic' => true, 'static' => 'Pemberitaan Firman Tuhan'],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Pengakuan Iman Rasuli', 'is_dynamic' => true, 'static' => "PF : Marilah kita berdiri dan dengan segala orang percaya di segala tempat dan sepanjang zaman, mengikrarkan iman kita menurut Pengakuan Iman Rasuli.\n\nPF + J : Aku percaya kepada Allah, Bapa yang Mahakuasa, Khalik langit dan bumi. Dan kepada Yesus Kristus, Anak-Nya Yang Tunggal, Tuhan kita. Yang dikandung dari Roh Kudus, lahir dari anak dara Maria; yang menderita di bawah pemerintahan Pontius Pilatus, disalibkan, mati, dan dikuburkan, turun ke dalam kerajaan maut. Pada hari yang ketiga bangkit pula dari antara orang mati; naik ke sorga, duduk di sebelah kanan Allah, Bapa yang Mahakuasa; dan akan datang dari sana untuk menghakimi orang yang hidup dan yang mati. Aku percaya kepada Roh Kudus; Gereja yang kudus dan am; Persekutuan orang kudus; Pengampunan dosa; Kebangkitan daging dan hidup yang kekal."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Duduk)'],
                ['title' => 'Nyanyian Paduan Suara / Jemaat', 'is_dynamic' => true, 'static' => "NYANYIAN: KJ. NO.169 : 1, 2 “MEMANDANG SALIB RAJAKU”\n\n1. Memandang salib Rajaku yang mati untuk dunia,\nkurasa hancur congkakku dan harta hilang harganya.\n\n2. Tak boleh aku bermegah selain di dalam salibMu;\nkubuang nikmat dunia demi darahMu yang kudus.\n\n(Dilanjutkan dengan persembahan PS/VG/SOLO/DUEL/TRIO/KWARTET)"],
                ['title' => 'Ajakan Untuk Memberi', 'is_dynamic' => true, 'static' => "Dkn : Saudara-saudara Jemaat yang terkasih di dalam Tuhan, Kita telah merasakan kasih dan pengampunan Kristus yang rela menyerahkan diri-Nya untuk pengampunan segala dosa dan kesalahan kita. Firman Tuhan berkata:\n\n“Sebab kamu telah mengenal kasih karunia Tuhan kita Yesus Kristus, bahwa Ia yang oleh karena kamu menjadi miskin, sekalipun Ia kaya, supaya kamu menjadi kaya oleh karena kemiskinan-Nya.” (2 Korintus 8:9)\n\nSebagai ungkapan syukur atas kasih yang begitu besar, marilah kita mempersembahkan sebagian dari berkat yang Tuhan titipkan bagi kita. Biarlah persembahan ini bukan sekadar kewajiban, melainkan tanda kasih, pengabdian, dan kerelaan hati kita kepada Allah. Karena sebesar apapun yang kipersembahkan tidak sebanding dengan Pengorbanan-Nya untuk keselamatan kita yang kekal."],
                ['title' => 'Nyanyian Persembahan', 'is_dynamic' => true, 'static' => "NYANYIAN PERSEMBAHAN KJ. NO. 393 : 1, 3 “TUHAN, BETAPA BANYAKNYA”\n\n1. Tuhan, betapa baiknya berkat yang Kauberi,\nistimewa rahmatMu dan hidup abadi.\nReff : T'rima kasih, ya Tuhanku atas keselamatanku!\nPadaku telah Kauberi hidup bahagia abadi.\n\n2. Sanak saudara dan teman Kaub'ri kepadaku;\nberkat terindah ialah 'ku jadi anakMu. Reff :\n\n3. Setiap hari rahmatMu tiada putusnya:\nhendak kupuji namaMu tetap selamanya. Reff:"],
                ['title' => 'Doa Syukur Persembahan', 'is_dynamic' => true, 'static' => "Dkn : Jemaat “Marilah kita berdoa”. Allah Bapa yang penuh kasih, kami datang dengan hati yang penuh syukur. Di tengah perjalanan masa sengsara ini, kami diingatkan akan pengorbanan Anak-Mu, Tuhan Yesus Kristus, yang telah memberikan diri-Nya di salib demi keselamatan kami. Inilah persembahan yang dengan sukacita kami bawa ke hadapan-Mu.\n\nKami sadar, semua yang kami miliki berasal dari-Mu, dan apa yang kami kembalikan hanyalah sebagian kecil dari berkat yang Kau limpahkan. Ya Tuhan, kuduskanlah dan pakailah persembahan ini untuk pekerjaan pelayanan, kesaksian, dan pengabdian jemaat-Mu, supaya melalui persembahan ini, banyak orang boleh merasakan kasih dan terang Kristus.\n\nKuduskan juga hati dan hidup kami, agar kami tidak hanya mempersembahkan harta, tetapi juga seluruh diri kami untuk melayani Engkau dengan pengabdian tanpa pamrih, sebagaimana Kristus telah lebih dahulu mengasihi kami. Di dalam nama Tuhan Yesus Kristus, yang telah menyerahkan hidup-Nya bagi kami, kami berdoa.\n\nJ : Amin."],
                ['title' => 'Doa Syafaat', 'is_dynamic' => true, 'static' => "DOA SYAFAAT (Dilanjutkan dengan persembahan PS/VG/SOLO/DUEL/TRIO/KWARTET)"],
                ['title' => 'Nyanyian Jemaat', 'is_dynamic' => true, 'static' => "NYANYIAN BERSAMA KJ.NO. 453 : 1, 3 “YESUS KAWAN YANG SEJATI”\n\n1. Yesus Kawan yang sejati bagi kita yang lemah.\nTiap hal boleh dibawa dalam doa padaNya.\nO, betapa kita susah dan percuma berlelah,\nBila kurang pasrah diri dalam Doa padaNya.\n\n3. Adakah hatimu sarat, jiwa-ragamu lelah?\nYesuslah Penolong kita; naikkan doa padaNya!\nBiar kawan lain menghilang, Yesus Kawan yang baka.\nIa mau menghibur kita atas doa padaNya.\n\n(Dilanjutkan dengan persembahan PS/VG/SOLO/DUEL/TRIO/KWARTET)"],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Jemaat Berdiri)'],
                ['title' => 'Amanat Pengutusan', 'is_dynamic' => true, 'static' => "PF : Saudara-saudara, kita telah diampuni dan dikuatkan oleh kasih Allah di dalam Kristus. Pergilah, hiduplah sebagai saksi kasih Kristus di tengah dunia.\nJ : Kami pergi dengan hati yang taat, mengasihi seperti Kristus telah mengasihi kami.\nPF : Tuhan memanggil kita untuk memikul salib setiap hari, berjalan dalam ketaatan dan kerendahan hati.\nJ : Kami rela dipimpin oleh Roh Kudus, setia mengikuti jejak Kristus yang menderita demi keselamatan dunia.\nPF : Hiduplah dalam kasih, tebarkan damai, dan jadilah terang bagi dunia yang gelap.\nJ : Kami siap diutus, menjadi saksi Kristus di rumah kami, di jemaat, dan di tengah masyarakat.\nPF : Tuhan menyertai langkah hidupmu."],
                ['title' => 'Nyanyian Pengutusan', 'is_dynamic' => true, 'static' => "NYANYIAN PENGUTUSAN KJ. NO. 406 : 1, 2 “YA TUHAN, BIMBING AKU”\n\n1. Ya Tuhan, bimbing aku di jalanku, sehingga 'ku\nselalu bersamaMu. Engganlah 'ku melangkah setapak pun,\n'pabila Kau tak ada disampingku.\n\n2. Lindungilah hatiku di rahmatMu dan buatlah batinku\ntenang teduh. Dekat kakiMu saja 'ku\nmau rebah dan tidak ragu-ragu 'ku berserah."],
                ['title' => 'Berkat', 'is_dynamic' => true, 'static' => "PF : Arahkanlah hati dan pikiranmu kepada Yesus Kristus dan terimalah Berkat-Nya.\n\nTUHAN memberkati engkau dan melindungi engkau; TUHAN menyinari engkau dengan wajah-Nya dan memberi engkau kasih karunia; TUHAN menghadapkan wajah-Nya kepadamu dan memberi engkau damai sejahtera. Kasih karunia Tuhan Yesus Kristus, kasih Allah Bapa, dan persekutuan yang manis dengan Roh Kudus, menyertai kamu sekalian, dari sekarang ini, sampai selama-lamanya.\n\nJ : Amin, Amin, Amin."],
                ['title' => 'Sikap Jemaat', 'is_dynamic' => true, 'static' => '(Saat Teduh / Lilin Dipadamkan)'],
                ['title' => 'Nyanyian Jabat Tangan', 'is_dynamic' => true, 'static' => "NYANYIAN JABAT TANGAN KJ. NO. 370: 1, 2 \"KU MAU BERJALAN DENGAN JURUS'LAMATKU\"\n\n1. Ku mau berjalan dengan Jurus'lamatku di Lembah\nberbunga dan berair sejuk. Ya, ke mana juga aku mau\nmengikutNya. Sampai aku tiba di neg'ri baka.\nReff : Ikut, ikut, ikut Tuhan Yesus; 'ku tetap mendengar dan\nMengikutNya. Ikut, ikut, ikut Tuhan Yesus; ya, ke mana\nJuga 'ku mengikutNya!\n\n2. Ku mau berjalan dengan Jurus'lamatku di lembah gelap,\ndi badai yang menderu. Aku takkan takut di bahaya apa pun,\nbila 'ku dibimbing tangan Tuhanku. Reff:"],
            ],
        ];

        // 1. Matikan pengecekan Foreign Key sementara
        Schema::disableForeignKeyConstraints();

        // 2. Kosongkan data lama agar tidak menumpuk/bertindih
        LiturgyItem::truncate();
        Liturgy::truncate();

        // 3. Nyalakan kembali pengecekan Foreign Key
        Schema::enableForeignKeyConstraints();

        foreach ($templates as $liturgyName => $items) {
            $liturgy = Liturgy::create(['name' => $liturgyName]);
            
            $order = 1;
            foreach ($items as $item) {
                $liturgy->items()->create([
                    'title' => $item['title'],
                    'is_dynamic' => $item['is_dynamic'],
                    'static_content' => $item['static'],
                    'order_number' => $order++
                ]);
            }
        }
    }
}