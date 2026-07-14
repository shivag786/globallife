<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VELOZ — Electric Two-Wheelers, Built for the Street</title>
<meta name="description" content="VELOZ electric scooters — range, torque, and street-ready design.">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Three.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<style>
  :root{
    --asphalt:#161311;
    --asphalt-2:#1f1b18;
    --paper:#F4EFE4;
    --paper-dim:#c9c2b2;
    --racing-red:#E8462E;
    --racing-red-dim:#a83322;
    --chrome:#C9CDD3;
    --volt:#DFF24C;
    --line: rgba(244,239,228,0.12);
    --radius: 2px;
  }

  *{margin:0;padding:0;box-sizing:border-box;}

  html{scroll-behavior:smooth;}

  body{
    background:var(--asphalt);
    color:var(--paper);
    font-family:'Manrope', sans-serif;
    overflow-x:hidden;
  }

  h1,h2,h3, .display{
    font-family:'Anton', sans-serif;
    text-transform:uppercase;
    letter-spacing:0.5px;
    line-height:0.95;
  }

  a{color:inherit; text-decoration:none;}
  ul{list-style:none;}
  img{max-width:100%; display:block;}

  .wrap{
    max-width:1240px;
    margin:0 auto;
    padding:0 32px;
  }

  .eyebrow{
    font-family:'Manrope',sans-serif;
    font-weight:800;
    font-size:12px;
    letter-spacing:3px;
    text-transform:uppercase;
    color:var(--racing-red);
    display:flex;
    align-items:center;
    gap:10px;
  }
  .eyebrow::before{
    content:"";
    width:24px;
    height:2px;
    background:var(--racing-red);
    display:inline-block;
  }

  /* NAV */
  header{
    position:fixed;
    top:0; left:0; right:0;
    z-index:100;
    background:linear-gradient(to bottom, rgba(22,19,17,0.95), rgba(22,19,17,0));
    padding:22px 0;
    transition:background .3s ease, padding .3s ease;
  }
  header.scrolled{
    background:rgba(22,19,17,0.92);
    backdrop-filter:blur(8px);
    padding:14px 0;
    border-bottom:1px solid var(--line);
  }
  nav{
    display:flex;
    align-items:center;
    justify-content:space-between;
  }
  .logo{
    font-family:'Anton', sans-serif;
    font-size:24px;
    letter-spacing:2px;
  }
  .logo span{color:var(--racing-red);}
  .nav-links{
    display:flex;
    gap:36px;
    font-weight:600;
    font-size:14px;
    letter-spacing:0.5px;
  }
  .nav-links a{
    position:relative;
    opacity:0.85;
    transition:opacity .2s;
  }
  .nav-links a:hover{opacity:1;}
  .nav-links a::after{
    content:"";
    position:absolute;
    left:0; bottom:-6px;
    width:0; height:2px;
    background:var(--racing-red);
    transition:width .25s ease;
  }
  .nav-links a:hover::after{width:100%;}

  .btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:14px 28px;
    font-weight:800;
    font-size:13px;
    letter-spacing:1.5px;
    text-transform:uppercase;
    border-radius:var(--radius);
    cursor:pointer;
    border:none;
    transition:transform .2s ease, box-shadow .2s ease;
  }
  .btn:hover{transform:translateY(-2px);}
  .btn-primary{
    background:var(--racing-red);
    color:var(--paper);
    box-shadow:0 8px 24px -8px rgba(232,70,46,0.6);
  }
  .btn-primary:hover{box-shadow:0 12px 28px -8px rgba(232,70,46,0.75);}
  .btn-ghost{
    background:transparent;
    color:var(--paper);
    border:1.5px solid var(--line);
  }
  .btn-ghost:hover{border-color:var(--paper);}

  /* HERO */
  .hero{
    position:relative;
    min-height:100vh;
    display:flex;
    align-items:center;
    padding-top:120px;
    overflow:hidden;
  }
  .hero-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:40px;
    align-items:center;
    width:100%;
  }
  .hero h1{
    font-size:clamp(52px, 7vw, 96px);
    margin:18px 0 22px;
  }
  .hero h1 .accent{color:var(--racing-red);}
  .hero p{
    font-size:17px;
    color:var(--paper-dim);
    max-width:440px;
    margin-bottom:32px;
    line-height:1.6;
  }
  .hero-actions{
    display:flex;
    gap:16px;
    margin-bottom:48px;
  }
  .hero-stats{
    display:flex;
    gap:36px;
    border-top:1px solid var(--line);
    padding-top:24px;
  }
  .hero-stats div strong{
    display:block;
    font-family:'Anton', sans-serif;
    font-size:30px;
    color:var(--volt);
  }
  .hero-stats div span{
    font-size:12px;
    color:var(--paper-dim);
    letter-spacing:1px;
    text-transform:uppercase;
  }

  .hero-canvas-wrap{
    position:relative;
    height:560px;
  }
  #wheel-canvas{
    width:100%;
    height:100%;
  }
  .speed-lines{
    position:absolute;
    inset:0;
    pointer-events:none;
    background:
      repeating-linear-gradient(100deg, transparent 0 60px, rgba(244,239,228,0.03) 60px 62px);
  }

  /* COLOR CONFIGURATOR CHIPS on hero */
  .color-picker{
    position:absolute;
    bottom:8px;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    gap:14px;
    background:rgba(0,0,0,0.25);
    padding:12px 18px;
    border-radius:40px;
    border:1px solid var(--line);
  }
  .swatch{
    width:26px;
    height:26px;
    border-radius:50%;
    cursor:pointer;
    border:2px solid transparent;
    transition:transform .2s ease, border-color .2s ease;
  }
  .swatch:hover{transform:scale(1.15);}
  .swatch.active{border-color:var(--paper);}

  /* SECTION HEADS */
  section{padding:120px 0;}
  .section-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    margin-bottom:56px;
    gap:24px;
    flex-wrap:wrap;
  }
  .section-head h2{font-size:clamp(34px,4vw,52px);}
  .section-head p{
    color:var(--paper-dim);
    max-width:360px;
    font-size:15px;
    line-height:1.6;
  }

  /* STATS STRIP */
  .strip{
    background:var(--racing-red);
    color:var(--asphalt);
    padding:28px 0;
  }
  .strip .wrap{
    display:flex;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:16px;
    font-family:'Anton',sans-serif;
    font-size:15px;
    letter-spacing:1px;
    text-transform:uppercase;
  }

  /* MODELS */
  .models-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:28px;
  }
  .model-card{
    background:var(--asphalt-2);
    border:1px solid var(--line);
    border-radius:var(--radius);
    padding:32px 28px 28px;
    transition:transform .3s ease, border-color .3s ease;
  }
  .model-card:hover{
    transform:translateY(-6px);
    border-color:var(--racing-red);
  }
  .model-tag{
    font-size:11px;
    font-weight:800;
    letter-spacing:2px;
    color:var(--volt);
    text-transform:uppercase;
  }
  .model-card h3{
    font-size:36px;
    margin:10px 0 4px;
  }
  .model-card .price{
    color:var(--paper-dim);
    font-size:14px;
    margin-bottom:20px;
  }
  .model-visual{
    height:150px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:24px;
    position:relative;
  }
  .model-visual svg{width:100%; height:100%;}
  .spec-list{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
    margin-bottom:26px;
    border-top:1px solid var(--line);
    padding-top:20px;
  }
  .spec-list div span{
    display:block;
    font-size:11px;
    color:var(--paper-dim);
    letter-spacing:1px;
    text-transform:uppercase;
  }
  .spec-list div strong{
    font-size:16px;
    font-weight:700;
  }
  .model-card .btn{width:100%; justify-content:center;}

  /* FEATURES */
  .feature-grid{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:1px;
    background:var(--line);
    border:1px solid var(--line);
  }
  .feature{
    background:var(--asphalt);
    padding:36px 28px;
    transition:background .25s ease;
  }
  .feature:hover{background:var(--asphalt-2);}
  .feature .num{
    font-family:'Anton',sans-serif;
    color:var(--racing-red);
    font-size:13px;
    letter-spacing:2px;
  }
  .feature h3{
    font-size:20px;
    text-transform:none;
    font-family:'Manrope',sans-serif;
    font-weight:800;
    margin:14px 0 10px;
  }
  .feature p{
    font-size:14px;
    color:var(--paper-dim);
    line-height:1.6;
  }

  /* TESTIMONIALS */
  .testimonial-strip{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:28px;
  }
  .t-card{
    border-left:2px solid var(--racing-red);
    padding-left:22px;
  }
  .t-card p{
    font-size:16px;
    line-height:1.7;
    color:var(--paper);
    margin-bottom:18px;
  }
  .t-card .who{
    font-size:13px;
    color:var(--paper-dim);
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:1px;
  }

  /* TEST RIDE FORM */
  .ride-section{
    background:var(--asphalt-2);
    border-top:1px solid var(--line);
    border-bottom:1px solid var(--line);
  }
  .ride-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:64px;
    align-items:center;
  }
  .ride-grid h2{font-size:clamp(32px,4vw,48px); margin-bottom:16px;}
  .ride-grid > div:first-child p{
    color:var(--paper-dim);
    line-height:1.7;
    margin-bottom:28px;
    max-width:420px;
  }
  form.ride-form{
    display:grid;
    gap:18px;
  }
  .form-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
  }
  .field label{
    display:block;
    font-size:11px;
    font-weight:800;
    letter-spacing:1.5px;
    text-transform:uppercase;
    color:var(--paper-dim);
    margin-bottom:8px;
  }
  .field input,
  .field select{
    width:100%;
    background:var(--asphalt);
    border:1px solid var(--line);
    color:var(--paper);
    padding:14px 16px;
    font-family:'Manrope',sans-serif;
    font-size:14px;
    border-radius:var(--radius);
    outline:none;
    transition:border-color .2s ease;
  }
  .field input:focus,
  .field select:focus{border-color:var(--racing-red);}
  .field-error{
    color:var(--racing-red);
    font-size:12px;
    margin-top:6px;
  }
  .form-success{
    background:rgba(223,242,76,0.12);
    border:1px solid var(--volt);
    color:var(--volt);
    padding:14px 16px;
    font-size:13px;
    border-radius:var(--radius);
  }

  /* FOOTER */
  footer{
    padding:60px 0 32px;
  }
  .footer-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    flex-wrap:wrap;
    gap:32px;
    padding-bottom:40px;
    border-bottom:1px solid var(--line);
    margin-bottom:24px;
  }
  .footer-cols{
    display:flex;
    gap:64px;
  }
  .footer-cols h4{
    font-size:12px;
    letter-spacing:2px;
    text-transform:uppercase;
    color:var(--paper-dim);
    margin-bottom:16px;
  }
  .footer-cols ul li{margin-bottom:10px; font-size:14px;}
  .footer-cols ul li a:hover{color:var(--racing-red);}
  .footer-bottom{
    display:flex;
    justify-content:space-between;
    font-size:12px;
    color:var(--paper-dim);
    flex-wrap:wrap;
    gap:12px;
  }

  @media (max-width: 900px){
    .hero-grid, .ride-grid{grid-template-columns:1fr;}
    .hero-canvas-wrap{height:380px; order:-1;}
    .models-grid{grid-template-columns:1fr;}
    .feature-grid{grid-template-columns:1fr 1fr;}
    .testimonial-strip{grid-template-columns:1fr;}
    .form-row{grid-template-columns:1fr;}
    .nav-links{display:none;}
    .strip .wrap{justify-content:center; text-align:center;}
  }

  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto;}
  }
</style>
</head>
<body>

<header id="site-header">
  <div class="wrap">
    <nav>
      <div class="logo">VEL<span>O</span>Z</div>
      <ul class="nav-links">
        <li><a href="#models">Models</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#reviews">Reviews</a></li>
        <li><a href="#test-ride">Test Ride</a></li>
      </ul>
      <a href="#test-ride" class="btn btn-primary">Book Test Ride</a>
    </nav>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <div class="eyebrow">100% Electric · Made for Indian Roads</div>
      <h1>Own the<br>last <span class="accent">mile.</span></h1>
      <p>VELOZ electric scooters pair instant torque with a 151 km real-world range, so your commute stops being the worst part of your day.</p>
      <div class="hero-actions">
        <a href="#models" class="btn btn-primary">Explore Models</a>
        <a href="#test-ride" class="btn btn-ghost">Book a Test Ride</a>
      </div>
      <div class="hero-stats">
        <div><strong>151 km</strong><span>Range / charge</span></div>
        <div><strong>0–40</strong><span>In 3.2 seconds</span></div>
        <div><strong>4 hrs</strong><span>0–100% charge</span></div>
      </div>
    </div>

    <div class="hero-canvas-wrap">
      <canvas id="wheel-canvas"></canvas>
      <div class="speed-lines"></div>
      <div class="color-picker" id="color-picker">
        <div class="swatch active" style="background:#E8462E" data-color="0xE8462E"></div>
        <div class="swatch" style="background:#DFF24C" data-color="0xDFF24C"></div>
        <div class="swatch" style="background:#C9CDD3" data-color="0xC9CDD3"></div>
        <div class="swatch" style="background:#2D6CDF" data-color="0x2D6CDF"></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS STRIP -->
<div class="strip">
  <div class="wrap">
    <span>IP67 Battery</span>
    <span>3 Yr / 40,000 km Warranty</span>
    <span>OTA Updates</span>
    <span>Reverse Assist</span>
    <span>Anti-Theft Alarm</span>
  </div>
</div>

<!-- MODELS -->
<section id="models">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">The Lineup</div>
        <h2>Three builds.<br>One philosophy.</h2>
      </div>
      <p>Every VELOZ shares the same motor platform — the difference is how far, how fast, and how loaded you want to go.</p>
    </div>

    <div class="models-grid">

      <div class="model-card">
        <div class="model-tag">City</div>
        <h3>VELOZ S1</h3>
        <div class="price">₹ 94,999 <span style="opacity:.6">onwards</span></div>
        <div class="model-visual">
          <svg viewBox="0 0 200 100"><circle cx="45" cy="75" r="20" fill="none" stroke="#C9CDD3" stroke-width="4"/><circle cx="155" cy="75" r="20" fill="none" stroke="#C9CDD3" stroke-width="4"/><path d="M45 75 L90 40 L140 40 L155 75 M90 40 L100 20 L130 20" fill="none" stroke="#E8462E" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="spec-list">
          <div><span>Range</span><strong>112 km</strong></div>
          <div><span>Top Speed</span><strong>75 km/h</strong></div>
          <div><span>Charge Time</span><strong>4.5 hrs</strong></div>
          <div><span>Boot Space</span><strong>26 L</strong></div>
        </div>
        <a href="#test-ride" class="btn btn-ghost">Configure S1</a>
      </div>

      <div class="model-card" style="border-color:var(--racing-red);">
        <div class="model-tag">Performance</div>
        <h3>VELOZ R1</h3>
        <div class="price">₹ 1,28,999 <span style="opacity:.6">onwards</span></div>
        <div class="model-visual">
          <svg viewBox="0 0 200 100"><circle cx="40" cy="75" r="22" fill="none" stroke="#DFF24C" stroke-width="4"/><circle cx="160" cy="75" r="22" fill="none" stroke="#DFF24C" stroke-width="4"/><path d="M40 75 L85 35 L145 35 L160 75 M85 35 L95 15 L135 15" fill="none" stroke="#E8462E" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="spec-list">
          <div><span>Range</span><strong>151 km</strong></div>
          <div><span>Top Speed</span><strong>105 km/h</strong></div>
          <div><span>Charge Time</span><strong>4 hrs</strong></div>
          <div><span>Boot Space</span><strong>32 L</strong></div>
        </div>
        <a href="#test-ride" class="btn btn-primary">Configure R1</a>
      </div>

      <div class="model-card">
        <div class="model-tag">Cargo</div>
        <h3>VELOZ X1</h3>
        <div class="price">₹ 1,09,999 <span style="opacity:.6">onwards</span></div>
        <div class="model-visual">
          <svg viewBox="0 0 200 100"><circle cx="42" cy="75" r="20" fill="none" stroke="#C9CDD3" stroke-width="4"/><circle cx="158" cy="75" r="20" fill="none" stroke="#C9CDD3" stroke-width="4"/><rect x="95" y="35" width="45" height="30" fill="none" stroke="#E8462E" stroke-width="4"/><path d="M42 75 L95 55 M140 55 L158 75" fill="none" stroke="#E8462E" stroke-width="4" stroke-linecap="round"/></svg>
        </div>
        <div class="spec-list">
          <div><span>Range</span><strong>128 km</strong></div>
          <div><span>Top Speed</span><strong>68 km/h</strong></div>
          <div><span>Charge Time</span><strong>5 hrs</strong></div>
          <div><span>Payload</span><strong>90 kg</strong></div>
        </div>
        <a href="#test-ride" class="btn btn-ghost">Configure X1</a>
      </div>

    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="features" style="padding-top:0;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">Engineering</div>
        <h2>Built to be lived with,<br>not just ridden.</h2>
      </div>
    </div>
  </div>
  <div class="feature-grid">
    <div class="feature">
      <div class="num">01</div>
      <h3>IP67 Battery Pack</h3>
      <p>Fully sealed against monsoon flooding and dust — rated for total submersion up to 1 metre.</p>
    </div>
    <div class="feature">
      <div class="num">02</div>
      <h3>Regenerative Braking</h3>
      <p>Recovers energy on every deceleration, adding up to 8% back to range in stop-start traffic.</p>
    </div>
    <div class="feature">
      <div class="num">03</div>
      <h3>Connected App</h3>
      <p>Live location, geofencing alerts, and ride analytics, synced over 4G straight to your phone.</p>
    </div>
    <div class="feature">
      <div class="num">04</div>
      <h3>Swappable Battery</h3>
      <p>Pull the pack out and charge it at your desk — no need to park near an outlet.</p>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section id="reviews">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">Owners</div>
        <h2>From the street,<br>not a script.</h2>
      </div>
    </div>
    <div class="testimonial-strip">
      <div class="t-card">
        <p>"Switched from petrol two years ago. The R1 keeps up with traffic and I haven't touched a fuel pump since."</p>
        <div class="who">Ankit R. — Pune</div>
      </div>
      <div class="t-card">
        <p>"I run deliveries all day on the X1. The boot space alone paid for the upgrade in a month."</p>
        <div class="who">Farah S. — Lucknow</div>
      </div>
      <div class="t-card">
        <p>"Charges overnight on a normal socket. Genuinely forgot what a service station queue feels like."</p>
        <div class="who">Devraj K. — Indore</div>
      </div>
    </div>
  </div>
</section>

<!-- TEST RIDE FORM -->
<section class="ride-section" id="test-ride">
  <div class="wrap ride-grid">
    <div>
      <div class="eyebrow">Book Now</div>
      <h2>Ride it before<br>you decide.</h2>
      <p>Pick a model and your nearest VELOZ studio. We'll confirm a slot within the hour and bring the scooter to you if there's a studio nearby.</p>

      @if(session('success'))
        <div class="form-success">{{ session('success') }}</div>
      @endif
    </div>

    <form class="ride-form" method="POST" action="{{ route('test-ride.store') }}">
      @csrf
      <div class="form-row">
        <div class="field">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Your name" required>
          @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="field">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="10-digit mobile" required>
          @error('phone') <div class="field-error">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
        @error('email') <div class="field-error">{{ $message }}</div> @enderror
      </div>

      <div class="form-row">
        <div class="field">
          <label for="model">Model</label>
          <select id="model" name="model" required>
            <option value="">Select a model</option>
            <option value="S1" {{ old('model')=='S1' ? 'selected' : '' }}>VELOZ S1 — City</option>
            <option value="R1" {{ old('model')=='R1' ? 'selected' : '' }}>VELOZ R1 — Performance</option>
            <option value="X1" {{ old('model')=='X1' ? 'selected' : '' }}>VELOZ X1 — Cargo</option>
          </select>
          @error('model') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="field">
          <label for="city">Nearest City</label>
          <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="e.g. Jhansi" required>
          @error('city') <div class="field-error">{{ $message }}</div> @enderror
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="justify-content:center; margin-top:8px;">Confirm Test Ride</button>
    </form>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="footer-top">
      <div>
        <div class="logo" style="margin-bottom:14px;">VEL<span>O</span>Z</div>
        <p style="color:var(--paper-dim); font-size:14px; max-width:280px; line-height:1.6;">Electric two-wheelers engineered in India, for Indian roads.</p>
      </div>
      <div class="footer-cols">
        <div>
          <h4>Models</h4>
          <ul>
            <li><a href="#models">S1 — City</a></li>
            <li><a href="#models">R1 — Performance</a></li>
            <li><a href="#models">X1 — Cargo</a></li>
          </ul>
        </div>
        <div>
          <h4>Company</h4>
          <ul>
            <li><a href="#">About</a></li>
            <li><a href="#">Studios</a></li>
            <li><a href="#">Careers</a></li>
          </ul>
        </div>
        <div>
          <h4>Support</h4>
          <ul>
            <li><a href="#">Service Centers</a></li>
            <li><a href="#">Warranty</a></li>
            <li><a href="#test-ride">Book Test Ride</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; {{ date('Y') }} VELOZ Motors. All rights reserved.</span>
      <span>Made for the road ahead.</span>
    </div>
  </div>
</footer>

<script>
  // Sticky header background on scroll
  const header = document.getElementById('site-header');
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 40);
  });

  // ---- THREE.JS 3D WHEEL SHOWCASE ----
  (function(){
    const canvas = document.getElementById('wheel-canvas');
    const wrap = canvas.parentElement;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, wrap.clientWidth / wrap.clientHeight, 0.1, 100);
    camera.position.set(0, 0, 9);

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(wrap.clientWidth, wrap.clientHeight);

    // Lighting
    scene.add(new THREE.AmbientLight(0xffffff, 0.5));
    const key = new THREE.PointLight(0xffffff, 1.2);
    key.position.set(6, 6, 8);
    scene.add(key);
    const rim = new THREE.PointLight(0xDFF24C, 0.8);
    rim.position.set(-6, -3, -4);
    scene.add(rim);

    // Wheel group: tire + rim + spokes
    const wheelGroup = new THREE.Group();

    const tireGeo = new THREE.TorusGeometry(3, 0.55, 24, 64);
    const tireMat = new THREE.MeshStandardMaterial({ color: 0x0e0c0b, roughness: 0.85, metalness: 0.1 });
    const tire = new THREE.Mesh(tireGeo, tireMat);
    wheelGroup.add(tire);

    const rimGeo = new THREE.TorusGeometry(2.15, 0.14, 16, 48);
    const rimMat = new THREE.MeshStandardMaterial({ color: 0xE8462E, roughness: 0.35, metalness: 0.6 });
    const rimMesh = new THREE.Mesh(rimGeo, rimMat);
    wheelGroup.add(rimMesh);

    const hubGeo = new THREE.CylinderGeometry(0.5, 0.5, 0.35, 24);
    const hubMat = new THREE.MeshStandardMaterial({ color: 0xC9CDD3, roughness: 0.3, metalness: 0.8 });
    const hub = new THREE.Mesh(hubGeo, hubMat);
    hub.rotation.x = Math.PI / 2;
    wheelGroup.add(hub);

    const spokeCount = 8;
    const spokeMat = new THREE.MeshStandardMaterial({ color: 0xC9CDD3, roughness: 0.4, metalness: 0.7 });
    for (let i = 0; i < spokeCount; i++) {
      const spokeGeo = new THREE.BoxGeometry(0.12, 2.0, 0.12);
      const spoke = new THREE.Mesh(spokeGeo, spokeMat);
      const angle = (i / spokeCount) * Math.PI * 2;
      spoke.position.set(Math.cos(angle) * 1.05, Math.sin(angle) * 1.05, 0);
      spoke.rotation.z = angle + Math.PI / 2;
      wheelGroup.add(spoke);
    }

    scene.add(wheelGroup);
    wheelGroup.rotation.x = 0.15;

    function onResize() {
      const w = wrap.clientWidth, h = wrap.clientHeight;
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    }
    window.addEventListener('resize', onResize);

    let autoRotate = true;
    canvas.addEventListener('pointerdown', () => autoRotate = false);
    canvas.addEventListener('pointerup', () => autoRotate = true);

    let lastX = 0;
    canvas.addEventListener('pointermove', (e) => {
      if (autoRotate) { lastX = e.clientX; return; }
      const delta = e.clientX - lastX;
      wheelGroup.rotation.y += delta * 0.01;
      lastX = e.clientX;
    });

    function animate() {
      requestAnimationFrame(animate);
      if (autoRotate) wheelGroup.rotation.y += 0.006;
      renderer.render(scene, camera);
    }
    animate();

    // Color configurator
    const swatches = document.querySelectorAll('.swatch');
    swatches.forEach(sw => {
      sw.addEventListener('click', () => {
        swatches.forEach(s => s.classList.remove('active'));
        sw.classList.add('active');
        const color = parseInt(sw.dataset.color, 16);
        rimMat.color.setHex(color);
      });
    });
  })();
</script>

</body>
</html>