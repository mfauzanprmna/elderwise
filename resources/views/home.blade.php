@extends('layouts.app')
@section('content')
    <section class="hero">
        <div class="hero-copy">
            <div class="eyebrow">DIGITAL CONFIDENCE JOURNEY</div>
            <h1>Belajar teknologi dengan tenang.<br><span>Learn · Try · Grow.</span></h1>
            <p>ElderWiSE membantu guru dan pendidik dewasa membangun kepercayaan diri digital melalui langkah kecil, praktik
                nyata, dan perjalanan belajar yang aman.</p>
            <div class="hero-actions"><a class="btn btn-primary"
                    href="{{ auth()->check() ? route('dashboard') : route('register') }}">Mulai Perjalanan <span>→</span></a><a
                    class="btn btn-ghost" href="#principles">Lihat cara belajar</a></div>
            <div class="hero-note">“Saya tidak takut lagi menggunakan teknologi.”</div>
        </div>
        <div class="hero-visual">
            <div class="orb orb-a"></div>
            <div class="orb orb-b"></div>
            <div class="journey-card">
                <div class="card-top"><span>MY JOURNEY</span><b>0%</b></div>
                <div class="progress"><i style="width:0%"></i></div>
                <div class="mini-level active"><span>01</span>
                    <div><b>Getting Started</b><small>I Can Do It!</small></div><em>→</em>
                </div>
                <div class="mini-level"><span>02</span>
                    <div><b>Digital Essentials</b><small>Send, Share & Collaborate</small></div><em>🔒</em>
                </div>
                <div class="mini-level"><span>03</span>
                    <div><b>Digital Detective</b><small>Smart Digital Information</small></div><em>🔒</em>
                </div>
            </div>
        </div>
    </section>
    <section id="principles" class="section">
        <div class="section-heading">
            <div>
                <div class="eyebrow">DESAIN BELAJAR</div>
                <h2>Pelan-pelan, satu langkah setiap kali.</h2>
            </div>
            <p>Dirancang untuk peserta basic digital literacy dengan huruf besar, instruksi singkat, dan latihan yang dekat
                dengan pekerjaan sehari-hari.</p>
        </div>
        <div class="principles">
            <article><span>01</span>
                <h3>No Fear</h3>
                <p>Tidak takut salah.</p>
            </article>
            <article><span>02</span>
                <h3>Learn by Doing</h3>
                <p>Belajar langsung melalui praktik.</p>
            </article>
            <article><span>03</span>
                <h3>Small Steps</h3>
                <p>Kemampuan dibangun sedikit demi sedikit.</p>
            </article>
            <article><span>04</span>
                <h3>Celebrate Progress</h3>
                <p>Menghargai kemajuan, bukan kesempurnaan.</p>
            </article>
        </div>
    </section>
@endsection
