<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(){ return view('auth.login'); }
    public function showRegister(){ return view('auth.register'); }
    public function login(Request $request){
        $credentials = $request->validate(['email'=>'required|email','password'=>'required|string']);
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, ' . $user->name . '!');
    }
    public function register(Request $request){
        $data=$request->validate(['name'=>'required|string|max:120','email'=>'required|email|max:190|unique:users,email','password'=>'required|string|min:8|confirmed']);
        $user=User::create($data+['role'=>'learner']);
        Auth::login($user); $request->session()->regenerate();
        try { $user->sendEmailVerificationNotification(); }
        catch (\Throwable $e) { report($e); return redirect()->route('verification.notice')->with('success', 'Akun berhasil dibuat.')->with('mail_error', 'Email verifikasi belum dapat dikirim saat ini. Tekan tombol "Kirim Ulang Email Verifikasi" di halaman ini.'); }
        return redirect()->route('verification.notice')->with('success', 'Akun berhasil dibuat. Kami sudah mengirim tautan verifikasi ke ' . $user->email . '. Silakan periksa email Anda.');
    }
    public function logout(Request $request){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home')->with('success', 'Anda sudah keluar. Sampai jumpa lagi!'); }
}
