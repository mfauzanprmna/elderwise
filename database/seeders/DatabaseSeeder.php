<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $modules=[
            [1,'Getting Started','I Can Do It!','Membangun keberanian dan kepercayaan diri dasar dalam menggunakan teknologi digital.','Digital Explorer'],
            [2,'Digital Essentials','Send, Share & Collaborate','Komunikasi dan kolaborasi digital.','Digital Explorer'],
            [3,'Smart Digital Information','Digital Detective','Evaluasi informasi dan keamanan digital.','Digital Explorer'],
            [4,'My First Digital Teaching Material','I Can Create','Membuat bahan ajar digital.','Digital Creator'],
            [5,'Introduction to AI','AI Without Fear','Mengenal dan mencoba AI secara bertanggung jawab.','AI Explorer'],
            [6,'My Digital Confidence','Look How Far I’ve Come!','Refleksi dan perayaan pencapaian.','ElderWiSE Achievement'],
        ];
        foreach($modules as $i=>$m){ Module::updateOrCreate(['number'=>$m[0]],['title'=>$m[1],'slogan'=>$m[2],'description'=>$m[3],'badge'=>$m[4],'sort_order'=>$i+1]); }
        $this->seedModule1(Module::where('number',1)->first());
        $this->seedModule2(Module::where('number',2)->first());
        $this->seedModule3(Module::where('number',3)->first());
        if(!User::where('email','admin@elderwise.test')->exists()) User::create(['name'=>'ElderWiSE Admin','email'=>'admin@elderwise.test','password'=>'password123','role'=>'admin']);
    }
    private function saveLessons(Module $module, array $rows): void
    {
        foreach($rows as $r){ Lesson::updateOrCreate(['module_id'=>$module->id,'code'=>$r['code']],['title'=>$r['title'],'type'=>$r['type'],'content'=>$r['content']??null,'pdf_path'=>$r['pdf_path']??null,'quiz'=>$r['quiz']??null,'sort_order'=>$r['sort_order']]); }
    }
    private function seedModule1(Module $m): void
    {
        $q=[['question'=>'Apa arti download?','options'=>['Mengirim file ke internet','Menyimpan/mengambil file dari internet ke perangkat','Menghapus file'],'answer'=>1],['question'=>'Apa arti upload?','options'=>['Mengirim file dari perangkat ke internet/platform','Mencetak file','Mengganti nama folder'],'answer'=>0]];
        $rows=[
        ['1.1','Welcome to Module 1','text','Selamat datang di langkah pertama perjalanan ElderWiSE Anda. Tujuannya bukan membuat Anda menjadi ahli teknologi, melainkan membantu Anda merasa berani mencoba.\n\nSaya bisa belajar teknologi, sedikit demi sedikit.',1],
        ['1.2','What Will I Learn?','text','Dalam modul ini Anda akan belajar digital mindset, mengenal dashboard dan misi, perangkat dasar, navigasi, file & folder, unduh & unggah, serta menyelesaikan Quest pertama “I Can Do It!”.',2],
        ['1.3','Digital Confidence & Digital Mindset','text','Digital confidence adalah rasa percaya diri saat menggunakan teknologi. Growth mindset berarti: “Saya belum bisa hari ini, tapi saya bisa belajar sedikit demi sedikit.”',3],
        ['1.4','Getting to Know ElderWiSE','text','Dashboard adalah halaman utama setelah masuk. Modul adalah bab pembelajaran. Quest/Misi adalah tugas utama. XP adalah penanda kemajuan.',4],
        ['1.5','Basic Smartphone & Laptop Skills','text','Aktivitas: nyalakan perangkat, buka dan tutup aplikasi, coba tap/swipe atau klik, periksa koneksi Wi-Fi dan notifikasi.',5],
        ['1.6','Login & Navigation','text','Buka ElderWiSE, masukkan email dan kata sandi, lihat dashboard, buka Modul 1, lalu kembali ke dashboard.',6],
        ['1.7','Files & Folders','text','Buat folder “Bahan Ajar Saya”, pilih satu file, pindahkan ke folder, lalu beri nama yang mudah dikenali.',7],
        ['1.8','Download & Upload','text','Unduh satu dokumen contoh dari ElderWiSE, periksa folder Unduhan, lalu unggah satu file dari folder Bahan Ajar Saya.',8],
        ['1.9','My First Mission — I Can Do It!','quiz','Quest utama. Siapkan satu folder pribadi berisi bahan ajar dan buktikan berhasil mengunggah serta mengunduh materi.',9],
        ];
        $out=[]; foreach($rows as $r)$out[]=['code'=>$r[0],'title'=>$r[1],'type'=>$r[2],'content'=>$r[3],'quiz'=>$r[0]==='1.9'?$q:null,'sort_order'=>$r[4]]; $this->saveLessons($m,$out);
    }
    private function seedModule2(Module $m): void
    {
        $q=[['question'=>'Akses mana yang tepat untuk file jadi yang hanya perlu dibaca?','options'=>['Editor','Viewer','Owner'],'answer'=>1],['question'=>'Apa fungsi lampiran pada email?','options'=>['File tambahan yang ikut dikirim','Menghapus penerima','Mengubah password'],'answer'=>0]];
        $rows=[
        ['2.1','Welcome to Module 2','text','Selamat datang kembali! Sekarang saatnya terhubung dan bekerja sama secara digital.',1],['2.2','What Will I Learn?','text','Gmail, Google Drive, berbagi file dengan aman, Google Docs, Google Forms, Google Meet, dan Quest kedua.',2],['2.3','Email Essentials (Gmail)','text','Email adalah surat digital. Bagian penting: Kepada, Subjek, Isi Pesan, Lampiran. Periksa penerima serta lampiran sebelum mengirim.',3],['2.4','Google Drive & Cloud Storage','text','Google Drive menyimpan berkas secara online. File dapat diakses dari perangkat mana saja selama terhubung internet.',4],['2.5','Sharing Files Safely','text','Pastikan penerima tepat, pilih Viewer atau Editor sesuai kebutuhan, dan jangan sembarang membagikan data pribadi.',5],['2.6','Basic Google Docs','text','Buat dokumen baru, beri judul, tulis beberapa kalimat, dan perhatikan bahwa perubahan tersimpan otomatis.',6],['2.7','Basic Google Forms','text','Buat formulir sederhana seperti kehadiran kelas, tambahkan 2–3 pertanyaan, lalu lihat hasilnya.',7],['2.8','Joining an Online Meeting (Google Meet)','text','Buka tautan rapat, periksa kamera dan mikrofon, tekan Gabung, coba mute/unmute, dan kirim satu pesan di chat.',8],['2.9','My Second Mission — Send, Share & Collaborate','quiz','Quest: kirim bahan ajar lewat email, simpan dan bagikan di Google Drive, buat Google Form, dan bila memungkinkan lakukan rapat daring singkat.',9],];
        $out=[]; foreach($rows as $r)$out[]=['code'=>$r[0],'title'=>$r[1],'type'=>$r[2],'content'=>$r[3],'quiz'=>$r[0]==='2.9'?$q:null,'sort_order'=>$r[4]]; $this->saveLessons($m,$out);
    }
    private function seedModule3(Module $m): void
    {
        $q=[['question'=>'Apa kebiasaan utama seorang Digital Detective sebelum percaya atau membagikan informasi?','options'=>['Berhenti lalu periksa','Langsung membagikan','Hanya membaca judul'],'answer'=>0],['question'=>'Manakah yang termasuk 5 Pertanyaan Digital Detective?','options'=>['Siapa, kapan, dari mana, buktinya apa, ada pembanding?','Berapa banyak like dan komentar?','Siapa yang paling cepat membagikan?'],'answer'=>0],['question'=>'Jika menerima pesan “SEBARKAN SEKARANG JUGA!”, tindakan yang sesuai materi adalah…','options'=>['Langsung meneruskan','Memeriksa sumber terlebih dahulu','Memberikan password kepada pengirim'],'answer'=>1]];
        $rows=[
        ['3.1','Welcome to Module 3: Menjadi Digital Detective','pdf','Baca bahan belajar mandiri Module 3 pada PDF di bawah. Setelah selesai membaca, kerjakan checkpoint quiz. Materi ini menekankan kebiasaan: Berhenti → Periksa → Baru Percaya atau Bagikan.','materials/Modul_3_SMART_DIGITAL_INFORMATION.pdf',$q,1],
        ['3.2','What Will I Learn?: Perjalanan Kita di Module 3','text','Dalam modul ini kita belajar Search, Search Smarter, Check, Be Careful, Stay Safe, dan Use Responsibly.',2],
        ['3.3','Searching for Information: Mencari Informasi di Internet','text','Gunakan kata kunci sederhana. Jika hasil belum sesuai, coba kata kunci lain dan buat lebih spesifik.',3],
        ['3.4','Searching Smarter: Mencari dengan Lebih Cerdas','text','Tambahkan kata yang menjelaskan kebutuhan. Bandingkan beberapa hasil dan perhatikan judul, sumber, tanggal, dan isi.',4],
        ['3.5','How to Evaluate Online Information','text','Gunakan 5 Pertanyaan Digital Detective: Siapa? Kapan? Dari mana? Buktinya apa? Ada pembanding?',5],
        ['3.6','Recognizing Hoax & Misinformation','text','Waspadai permintaan menyebarkan segera, judul menghebohkan, sumber tidak jelas, tidak ada tanggal, “katanya”, dan gambar/video di luar konteks. Gunakan STOP: Stop, Telusuri, Observe, Proceed.',6],
        ['3.7','Digital Safety','text','Jangan asal klik link, hati-hati dengan file, periksa situs sebelum login, jangan memberikan informasi pribadi sembarangan, dan gunakan perangkat dengan aman.',7],
        ['3.8','Password & Privacy','text','Hindari password mudah ditebak. Gunakan password lebih kuat, jangan berikan password atau OTP, aktifkan verifikasi tambahan, dan jaga data pribadi.',8],
        ['3.9','Copyright & Responsible Use of Content','text','Sebelum menggunakan konten: cari sumber, periksa izin/lisensi, dan berikan atribusi jika diperlukan.',9],
        ['3.10','My Third Mission — “Digital Detective”','text','Cari, Periksa, dan Putuskan. Periksa sumber, tanggal, bukti, pembanding, tanda bahaya, keamanan, dan lisensi konten.',10],
        ];
        $out=[]; foreach($rows as $r){ $out[]=['code'=>$r[0],'title'=>$r[1],'type'=>$r[2],'content'=>$r[3],'pdf_path'=>$r[4]??null,'quiz'=>$r[5]??null,'sort_order'=>isset($r[6])?$r[6]:$r[4]]; } $this->saveLessons($m,$out);
    }
}
