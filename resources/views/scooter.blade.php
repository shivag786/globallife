<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VELOZ — Electric Two-Wheelers, Built for the Street</title>
<meta name="description" content="VELOZ electric scooters — range, torque, and street-ready design.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<style>
  :root{
    --asphalt:#161311;
    --asphalt-2:#1f1b18;
    --asphalt-3:#292320;
    --paper:#F4EFE4;
    --paper-dim:#c9c2b2;
    --racing-red:#E8462E;
    --racing-red-dim:#a83322;
    --chrome:#C9CDD3;
    --volt:#DFF24C;
    --blue:#2D6CDF;
    --line: rgba(244,239,228,0.12);
    --radius: 2px;
  }

  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{background:var(--asphalt); color:var(--paper); font-family:'Manrope', sans-serif; overflow-x:hidden;}
  h1,h2,h3, .display{font-family:'Anton', sans-serif; text-transform:uppercase; letter-spacing:0.5px; line-height:0.95;}
  a{color:inherit; text-decoration:none;}
  ul{list-style:none;}
  img{max-width:100%; display:block;}

  .wrap{max-width:1240px; margin:0 auto; padding:0 32px;}

  .eyebrow{
    font-weight:800; font-size:12px; letter-spacing:3px; text-transform:uppercase;
    color:var(--racing-red); display:flex; align-items:center; gap:10px;
  }
  .eyebrow::before{content:""; width:24px; height:2px; background:var(--racing-red); display:inline-block;}

  /* NAV */
  header{
    position:fixed; top:0; left:0; right:0; z-index:100;
    background:linear-gradient(to bottom, rgba(22,19,17,0.95), rgba(22,19,17,0));
    padding:22px 0; transition:background .3s ease, padding .3s ease;
  }
  header.scrolled{background:rgba(22,19,17,0.92); backdrop-filter:blur(8px); padding:14px 0; border-bottom:1px solid var(--line);}
  nav{display:flex; align-items:center; justify-content:space-between;}
  .logo{font-family:'Anton', sans-serif; font-size:24px; letter-spacing:2px;}
  .logo span{color:var(--racing-red);}
  .nav-links{display:flex; gap:32px; font-weight:600; font-size:14px; letter-spacing:0.5px;}
  .nav-links a{position:relative; opacity:0.85; transition:opacity .2s;}
  .nav-links a:hover{opacity:1;}
  .nav-links a::after{content:""; position:absolute; left:0; bottom:-6px; width:0; height:2px; background:var(--racing-red); transition:width .25s ease;}
  .nav-links a:hover::after{width:100%;}

  .btn{
    display:inline-flex; align-items:center; gap:8px; padding:14px 28px; font-weight:800;
    font-size:13px; letter-spacing:1.5px; text-transform:uppercase; border-radius:var(--radius);
    cursor:pointer; border:none; transition:transform .2s ease, box-shadow .2s ease;
  }
  .btn:hover{transform:translateY(-2px);}
  .btn-primary{background:var(--racing-red); color:var(--paper); box-shadow:0 8px 24px -8px rgba(232,70,46,0.6);}
  .btn-primary:hover{box-shadow:0 12px 28px -8px rgba(232,70,46,0.75);}
  .btn-ghost{background:transparent; color:var(--paper); border:1.5px solid var(--line);}
  .btn-ghost:hover{border-color:var(--paper);}

  /* HERO */
  .hero{position:relative; min-height:100vh; display:flex; align-items:center; padding-top:120px; overflow:hidden;}
  .hero-grid{display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:center; width:100%;}
  .hero h1{font-size:clamp(52px, 7vw, 96px); margin:18px 0 22px;}
  .hero h1 .accent{color:var(--racing-red);}
  .hero p{font-size:17px; color:var(--paper-dim); max-width:440px; margin-bottom:32px; line-height:1.6;}
  .hero-actions{display:flex; gap:16px; margin-bottom:48px;}
  .hero-stats{display:flex; gap:36px; border-top:1px solid var(--line); padding-top:24px;}
  .hero-stats div strong{display:block; font-family:'Anton', sans-serif; font-size:30px; color:var(--volt);}
  .hero-stats div span{font-size:12px; color:var(--paper-dim); letter-spacing:1px; text-transform:uppercase;}

  .hero-canvas-wrap{position:relative; height:560px;}
  #wheel-canvas{width:100%; height:100%; cursor:grab;}
  .speed-lines{
    position:absolute; inset:0; pointer-events:none;
    background:repeating-linear-gradient(100deg, transparent 0 60px, rgba(244,239,228,0.03) 60px 62px);
  }
  .color-picker{
    position:absolute; bottom:8px; left:50%; transform:translateX(-50%);
    display:flex; gap:14px; background:rgba(0,0,0,0.25); padding:12px 18px; border-radius:40px; border:1px solid var(--line);
  }
  .swatch{width:26px; height:26px; border-radius:50%; cursor:pointer; border:2px solid transparent; transition:transform .2s ease, border-color .2s ease;}
  .swatch:hover{transform:scale(1.15);}
  .swatch.active{border-color:var(--paper);}

  section{padding:120px 0;}
  .section-head{display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:56px; gap:24px; flex-wrap:wrap;}
  .section-head h2{font-size:clamp(34px,4vw,52px);}
  .section-head p{color:var(--paper-dim); max-width:360px; font-size:15px; line-height:1.6;}

  .strip{background:var(--racing-red); color:var(--asphalt); padding:28px 0;}
  .strip .wrap{display:flex; justify-content:space-between; flex-wrap:wrap; gap:16px; font-family:'Anton',sans-serif; font-size:15px; letter-spacing:1px; text-transform:uppercase;}

  /* INTRO / WHY ELECTRIC */
  .intro-grid{display:grid; grid-template-columns:1.1fr 0.9fr; gap:64px; align-items:center;}
  .intro-grid p{color:var(--paper-dim); line-height:1.75; font-size:16px; margin-bottom:18px;}
  .compare-table{width:100%; border-collapse:collapse; font-size:14px;}
  .compare-table th, .compare-table td{padding:14px 16px; border-bottom:1px solid var(--line); text-align:left;}
  .compare-table th{color:var(--paper-dim); font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:800;}
  .compare-table td.electric{color:var(--volt); font-weight:700;}
  .compare-table td.petrol{color:var(--paper-dim);}
  .compare-table tr:last-child td{border-bottom:none;}

  /* MODELS */
  .models-grid{display:grid; grid-template-columns:repeat(3, 1fr); gap:28px;}
  .model-card{background:var(--asphalt-2); border:1px solid var(--line); border-radius:var(--radius); padding:32px 28px 28px; transition:transform .3s ease, border-color .3s ease;}
  .model-card:hover{transform:translateY(-6px); border-color:var(--racing-red);}
  .model-tag{font-size:11px; font-weight:800; letter-spacing:2px; color:var(--volt); text-transform:uppercase;}
  .model-card h3{font-size:36px; margin:10px 0 4px;}
  .model-card .price{color:var(--paper-dim); font-size:14px; margin-bottom:16px;}
  .model-visual{height:160px; margin-bottom:20px; position:relative;}
  .model-visual canvas{width:100%; height:100%;}
  .spec-list{display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:26px; border-top:1px solid var(--line); padding-top:20px;}
  .spec-list div span{display:block; font-size:11px; color:var(--paper-dim); letter-spacing:1px; text-transform:uppercase;}
  .spec-list div strong{font-size:16px; font-weight:700;}
  .model-card .btn{width:100%; justify-content:center;}

  /* SHOWCASE (BIG 3D) */
  .showcase{background:var(--asphalt-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);}
  .showcase-grid{display:grid; grid-template-columns:1fr 1fr; gap:0; align-items:center;}
  .showcase-stage{height:560px; position:relative;}
  .showcase-stage canvas{width:100%; height:100%; cursor:grab;}
  .showcase-hint{position:absolute; bottom:16px; left:16px; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:var(--paper-dim);}
  .showcase-copy{padding:60px 40px;}
  .showcase-copy p{color:var(--paper-dim); line-height:1.7; margin-bottom:28px; max-width:420px;}
  .showcase-tabs{display:flex; gap:10px; margin-bottom:32px;}
  .showcase-tab{
    padding:10px 18px; border:1px solid var(--line); border-radius:40px; font-size:12px; font-weight:800;
    letter-spacing:1px; text-transform:uppercase; cursor:pointer; background:transparent; color:var(--paper-dim);
    transition:all .2s ease;
  }
  .showcase-tab.active{background:var(--racing-red); color:var(--paper); border-color:var(--racing-red);}
  .showcase-specs{display:grid; grid-template-columns:1fr 1fr; gap:20px;}
  .showcase-specs div span{display:block; font-size:11px; color:var(--paper-dim); letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;}
  .showcase-specs div strong{font-size:22px; font-family:'Anton',sans-serif;}

  /* FEATURES with 3D icons */
  .feature-grid{display:grid; grid-template-columns:repeat(4, 1fr); gap:1px; background:var(--line); border:1px solid var(--line);}
  .feature{background:var(--asphalt); padding:32px 26px; transition:background .25s ease;}
  .feature:hover{background:var(--asphalt-2);}
  .feature-icon{width:64px; height:64px; margin-bottom:16px;}
  .feature-icon canvas{width:100%; height:100%;}
  .feature h3{font-size:19px; text-transform:none; font-family:'Manrope',sans-serif; font-weight:800; margin-bottom:8px;}
  .feature p{font-size:14px; color:var(--paper-dim); line-height:1.6;}

  /* ACCESSORIES */
  .accessory-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:24px;}
  .accessory-card{background:var(--asphalt-2); border:1px solid var(--line); padding:24px; text-align:center;}
  .accessory-visual{height:120px; margin-bottom:16px;}
  .accessory-visual canvas{width:100%; height:100%;}
  .accessory-card h4{font-size:15px; margin-bottom:6px; font-weight:800;}
  .accessory-card p{font-size:13px; color:var(--paper-dim);}

  /* HOW CHARGING WORKS */
  .steps{display:grid; grid-template-columns:repeat(3,1fr); gap:28px;}
  .step{position:relative; padding-left:0;}
  .step .step-num{font-family:'Anton',sans-serif; font-size:52px; color:var(--racing-red); opacity:.5; display:block; margin-bottom:8px;}
  .step h3{font-size:20px; text-transform:none; font-family:'Manrope',sans-serif; font-weight:800; margin-bottom:10px;}
  .step p{color:var(--paper-dim); font-size:14px; line-height:1.6;}

  /* TESTIMONIALS */
  .testimonial-strip{display:grid; grid-template-columns:repeat(2,1fr); gap:28px;}
  .t-card{border-left:2px solid var(--racing-red); padding-left:22px;}
  .t-card p{font-size:16px; line-height:1.7; color:var(--paper); margin-bottom:18px;}
  .t-card .who{font-size:13px; color:var(--paper-dim); font-weight:700; text-transform:uppercase; letter-spacing:1px;}

  /* FAQ */
  .faq-list{max-width:760px;}
  .faq-item{border-bottom:1px solid var(--line); padding:22px 0;}
  .faq-item summary{cursor:pointer; font-weight:700; font-size:16px; list-style:none; display:flex; justify-content:space-between; align-items:center;}
  .faq-item summary::-webkit-details-marker{display:none;}
  .faq-item summary::after{content:"+"; color:var(--racing-red); font-family:'Anton',sans-serif; font-size:20px;}
  .faq-item[open] summary::after{content:"–";}
  .faq-item p{color:var(--paper-dim); font-size:14px; line-height:1.7; margin-top:14px; max-width:600px;}

  /* TEST RIDE FORM */
  .ride-section{background:var(--asphalt-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);}
  .ride-grid{display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center;}
  .ride-grid h2{font-size:clamp(32px,4vw,48px); margin-bottom:16px;}
  .ride-grid > div:first-child p{color:var(--paper-dim); line-height:1.7; margin-bottom:28px; max-width:420px;}
  form.ride-form{display:grid; gap:18px;}
  .form-row{display:grid; grid-template-columns:1fr 1fr; gap:18px;}
  .field label{display:block; font-size:11px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; color:var(--paper-dim); margin-bottom:8px;}
  .field input, .field select{
    width:100%; background:var(--asphalt); border:1px solid var(--line); color:var(--paper);
    padding:14px 16px; font-family:'Manrope',sans-serif; font-size:14px; border-radius:var(--radius); outline:none; transition:border-color .2s ease;
  }
  .field input:focus, .field select:focus{border-color:var(--racing-red);}
  .field-error{color:var(--racing-red); font-size:12px; margin-top:6px;}
  .form-success{background:rgba(223,242,76,0.12); border:1px solid var(--volt); color:var(--volt); padding:14px 16px; font-size:13px; border-radius:var(--radius);}

  footer{padding:60px 0 32px;}
  .footer-top{display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:32px; padding-bottom:40px; border-bottom:1px solid var(--line); margin-bottom:24px;}
  .footer-cols{display:flex; gap:64px;}
  .footer-cols h4{font-size:12px; letter-spacing:2px; text-transform:uppercase; color:var(--paper-dim); margin-bottom:16px;}
  .footer-cols ul li{margin-bottom:10px; font-size:14px;}
  .footer-cols ul li a:hover{color:var(--racing-red);}
  .footer-bottom{display:flex; justify-content:space-between; font-size:12px; color:var(--paper-dim); flex-wrap:wrap; gap:12px;}

  @media (max-width: 900px){
    .hero-grid, .ride-grid, .intro-grid, .showcase-grid{grid-template-columns:1fr;}
    .hero-canvas-wrap{height:380px; order:-1;}
    .models-grid, .accessory-grid{grid-template-columns:1fr 1fr;}
    .feature-grid, .steps{grid-template-columns:1fr 1fr;}
    .testimonial-strip{grid-template-columns:1fr;}
    .form-row{grid-template-columns:1fr;}
    .nav-links{display:none;}
    .strip .wrap{justify-content:center; text-align:center;}
    .showcase-stage{height:380px;}
  }

  @media (prefers-reduced-motion: reduce){ html{scroll-behavior:auto;} }
</style>
</head>
<body>

<header id="site-header">
  <div class="wrap">
    <nav>
      <div class="logo">VEL<span>O</span>Z</div>
      <ul class="nav-links">
        <li><a href="#models">Models</a></li>
        <li><a href="#showcase">3D Studio</a></li>
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

<!-- WHY ELECTRIC -->
<section id="why-electric">
  <div class="wrap intro-grid">
    <div>
      <div class="eyebrow">Why Switch</div>
      <h2 style="font-size:clamp(32px,4vw,48px); margin:16px 0 24px;">Petrol runs out.<br>Volts don't.</h2>
      <p>A petrol two-wheeler loses value the moment it leaves the showroom, and keeps costing you at every fuel stop after that. VELOZ owners plug in overnight and start every morning with a full tank that never touched a pump.</p>
      <p>Over three years of city riding, the average VELOZ owner spends about ₹0.15 per km on electricity, against ₹2.20 per km on petrol for a comparable scooter — even before servicing costs are counted in.</p>
    </div>
    <div>
      <table class="compare-table">
        <thead>
          <tr><th>What matters</th><th>Petrol Scooter</th><th>VELOZ</th></tr>
        </thead>
        <tbody>
          <tr><td>Running cost / km</td><td class="petrol">₹2.20</td><td class="electric">₹0.15</td></tr>
          <tr><td>Refuel / recharge time</td><td class="petrol">3–5 min, at a pump</td><td class="electric">Plug in at home</td></tr>
          <tr><td>Moving parts to service</td><td class="petrol">Engine, clutch, exhaust</td><td class="electric">Brakes, tyres, suspension</td></tr>
          <tr><td>0–40 km/h</td><td class="petrol">~4.8 sec</td><td class="electric">3.2 sec</td></tr>
          <tr><td>Emissions at tailpipe</td><td class="petrol">Yes</td><td class="electric">None</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- MODELS -->
<section id="models" style="padding-top:0;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">The Lineup</div>
        <h2>Three builds.<br>One philosophy.</h2>
      </div>
      <p>Every VELOZ shares the same motor platform — the difference is how far, how fast, and how loaded you want to go. Drag any render below to spin it.</p>
    </div>

    <div class="models-grid">

      <div class="model-card">
        <div class="model-tag">City</div>
        <h3>VELOZ S1</h3>
        <div class="price">₹ 94,999 <span style="opacity:.6">onwards</span></div>
        <div class="model-visual"><canvas id="card-s1"></canvas></div>
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
        <div class="model-visual"><canvas id="card-r1"></canvas></div>
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
        <div class="model-visual"><canvas id="card-x1"></canvas></div>
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

<!-- BIG 3D SHOWCASE -->
<section class="showcase" id="showcase">
  <div class="showcase-grid">
    <div class="showcase-stage">
      <canvas id="showcase-canvas"></canvas>
      <div class="showcase-hint">Drag to rotate · scroll to zoom</div>
    </div>
    <div class="showcase-copy">
      <div class="eyebrow">3D Studio</div>
      <h2 style="font-size:clamp(30px,3.5vw,44px); margin:14px 0 18px;">Look at it<br>from every angle.</h2>
      <p>This is the same render our design team uses to sign off proportions before a scooter ever reaches tooling. Switch models to see how the platform stretches across the lineup.</p>

      <div class="showcase-tabs" id="showcase-tabs">
        <button type="button" class="showcase-tab active" data-model="s1">S1</button>
        <button type="button" class="showcase-tab" data-model="r1">R1</button>
        <button type="button" class="showcase-tab" data-model="x1">X1</button>
      </div>

      <div class="showcase-specs" id="showcase-specs">
        <div><span>Range</span><strong>112 km</strong></div>
        <div><span>Top Speed</span><strong>75 km/h</strong></div>
        <div><span>Kerb Weight</span><strong>98 kg</strong></div>
        <div><span>Ground Clearance</span><strong>165 mm</strong></div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="features">
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
      <div class="feature-icon"><canvas id="icon-battery"></canvas></div>
      <h3>IP67 Battery Pack</h3>
      <p>Fully sealed against monsoon flooding and dust — rated for total submersion up to 1 metre.</p>
    </div>
    <div class="feature">
      <div class="feature-icon"><canvas id="icon-brake"></canvas></div>
      <h3>Regenerative Braking</h3>
      <p>Recovers energy on every deceleration, adding up to 8% back to range in stop-start traffic.</p>
    </div>
    <div class="feature">
      <div class="feature-icon"><canvas id="icon-motor"></canvas></div>
      <h3>Hub Motor, No Chain</h3>
      <p>Direct-drive hub motor means nothing to lubricate, adjust, or replace every few thousand km.</p>
    </div>
    <div class="feature">
      <div class="feature-icon"><canvas id="icon-connect"></canvas></div>
      <h3>Connected App</h3>
      <p>Live location, geofencing alerts, and ride analytics, synced over 4G straight to your phone.</p>
    </div>
  </div>
</section>

<!-- ACCESSORIES -->
<section id="accessories">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">Gear Up</div>
        <h2>Everything else<br>you'll actually use.</h2>
      </div>
      <p>Genuine VELOZ accessories, designed alongside the scooter rather than bolted on after.</p>
    </div>
    <div class="accessory-grid">
      <div class="accessory-card">
        <div class="accessory-visual"><canvas id="acc-helmet"></canvas></div>
        <h4>Smart Helmet</h4>
        <p>Bluetooth intercom, built-in LED brake light.</p>
      </div>
      <div class="accessory-card">
        <div class="accessory-visual"><canvas id="acc-charger"></canvas></div>
        <h4>Fast Charger</h4>
        <p>0–80% in under 3 hours from any 15A socket.</p>
      </div>
      <div class="accessory-card">
        <div class="accessory-visual"><canvas id="acc-lock"></canvas></div>
        <h4>Smart Lock</h4>
        <p>App-based immobiliser with tamper alerts.</p>
      </div>
      <div class="accessory-card">
        <div class="accessory-visual"><canvas id="acc-topbox"></canvas></div>
        <h4>Top Box, 40L</h4>
        <p>Tool-free mounting, fits two full-face helmets.</p>
      </div>
    </div>
  </div>
</section>

<!-- HOW CHARGING WORKS -->
<section id="charging">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">Daily Routine</div>
        <h2>Charging, without<br>the thinking.</h2>
      </div>
    </div>
    <div class="steps">
      <div class="step">
        <span class="step-num">01</span>
        <h3>Park and plug in</h3>
        <p>Any 5A or 15A household socket works — no special wiring or wall box required.</p>
      </div>
      <div class="step">
        <span class="step-num">02</span>
        <h3>Walk away</h3>
        <p>The app tracks charge percentage and notifies you when it crosses 80% and 100%.</p>
      </div>
      <div class="step">
        <span class="step-num">03</span>
        <h3>Ride, repeat</h3>
        <p>A full overnight charge easily covers a typical day of city commuting and errands.</p>
      </div>
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
      <div class="t-card">
        <p>"Bought the S1 for my daughter's college commute. The app's geofencing alert is what sold me."</p>
        <div class="who">Meenal T. — Jhansi</div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq" style="padding-top:0;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div class="eyebrow">Questions</div>
        <h2>Before you book<br>a test ride.</h2>
      </div>
    </div>
    <div class="faq-list">
      <details class="faq-item" open>
        <summary>How long does the battery actually last?</summary>
        <p>VELOZ battery packs are rated for 40,000 km or 3 years, whichever comes first, and retain at least 80% capacity through that period under normal use.</p>
      </details>
      <details class="faq-item">
        <summary>Can I charge it at a normal home socket?</summary>
        <p>Yes — every VELOZ ships with a portable charger that works with any standard 5A or 15A socket. No separate installation needed.</p>
      </details>
      <details class="faq-item">
        <summary>What's covered under warranty?</summary>
        <p>The battery, motor, and controller are covered for 3 years or 40,000 km. Wear items like tyres and brake pads are not included.</p>
      </details>
      <details class="faq-item">
        <summary>Is finance available?</summary>
        <p>Yes, VELOZ studios offer EMI options starting at ₹2,499/month through partner NBFCs, subject to credit approval.</p>
      </details>
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

    <form class="ride-form" method="POST">
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
  const header = document.getElementById('site-header');
  window.addEventListener('scroll', () => header.classList.toggle('scrolled', window.scrollY > 40));

  /* ============ SHARED 3D HELPERS ============ */
  const registry = []; // { renderer, scene, camera, group, speed }

  function addMesh(group, geo, mat, x=0, y=0, z=0, rx=0, ry=0, rz=0){
    const m = new THREE.Mesh(geo, mat);
    m.position.set(x,y,z);
    m.rotation.set(rx,ry,rz);
    group.add(m);
    return m;
  }

  function makeViewport(canvas, { camZ = 6, camY = 0, fov = 40, speed = 0.006, tiltX = 0.12 } = {}){
    const wrap = canvas.parentElement;
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(fov, Math.max(wrap.clientWidth,1) / Math.max(wrap.clientHeight,1), 0.1, 100);
    camera.position.set(0, camY, camZ);
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(wrap.clientWidth, wrap.clientHeight);

    scene.add(new THREE.AmbientLight(0xffffff, 0.55));
    const key = new THREE.PointLight(0xffffff, 1.1); key.position.set(5,5,6); scene.add(key);
    const rimLight = new THREE.PointLight(0xDFF24C, 0.55); rimLight.position.set(-5,-3,-3); scene.add(rimLight);

    const group = new THREE.Group();
    group.rotation.x = tiltX;
    scene.add(group);

    window.addEventListener('resize', () => {
      const w = wrap.clientWidth, h = wrap.clientHeight;
      if (!w || !h) return;
      camera.aspect = w / h; camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    });

    const entry = { renderer, scene, camera, group, speed, wrap, dragging:false };
    registry.push(entry);
    return entry;
  }

  function enableDrag(entry, canvas){
    let lastX = 0;
    canvas.style.cursor = 'grab';
    canvas.addEventListener('pointerdown', (e) => { entry.dragging = true; lastX = e.clientX; canvas.style.cursor='grabbing'; });
    window.addEventListener('pointerup', () => { entry.dragging = false; canvas.style.cursor='grab'; });
    window.addEventListener('pointermove', (e) => {
      if (!entry.dragging) return;
      const delta = e.clientX - lastX;
      entry.group.rotation.y += delta * 0.01;
      lastX = e.clientX;
    });
  }

  function enableZoom(entry, canvas){
    canvas.addEventListener('wheel', (e) => {
      e.preventDefault();
      entry.camera.position.z = Math.min(12, Math.max(3.5, entry.camera.position.z + e.deltaY * 0.01));
    }, { passive:false });
  }

  /* ============ BUILDERS ============ */

  // Standalone wheel (hero)
  function buildWheel(group, rimColor){
    addMesh(group, new THREE.TorusGeometry(3, 0.55, 24, 64), new THREE.MeshStandardMaterial({ color:0x0e0c0b, roughness:0.85, metalness:0.1 }));
    const rimMesh = addMesh(group, new THREE.TorusGeometry(2.15, 0.14, 16, 48), new THREE.MeshStandardMaterial({ color: rimColor, roughness:0.35, metalness:0.6 }));
    const hub = addMesh(group, new THREE.CylinderGeometry(0.5,0.5,0.35,24), new THREE.MeshStandardMaterial({ color:0xC9CDD3, roughness:0.3, metalness:0.8 }));
    hub.rotation.x = Math.PI/2;
    const spokeMat = new THREE.MeshStandardMaterial({ color:0xC9CDD3, roughness:0.4, metalness:0.7 });
    for (let i=0;i<8;i++){
      const a = (i/8)*Math.PI*2;
      addMesh(group, new THREE.BoxGeometry(0.12,2.0,0.12), spokeMat, Math.cos(a)*1.05, Math.sin(a)*1.05, 0, 0,0, a+Math.PI/2);
    }
    return rimMesh;
  }

  // Simplified full scooter silhouette from primitives
  function buildScooter(group, accentColor, { wheelbase = 3.2, seatLen = 1.1, boxy = false } = {}){
    const tireMat = new THREE.MeshStandardMaterial({ color:0x0e0c0b, roughness:0.9 });
    const rimMat  = new THREE.MeshStandardMaterial({ color:0xC9CDD3, roughness:0.35, metalness:0.7 });
    const bodyMat = new THREE.MeshStandardMaterial({ color: accentColor, roughness:0.4, metalness:0.3 });
    const darkMat = new THREE.MeshStandardMaterial({ color:0x2a2622, roughness:0.6, metalness:0.2 });
    const glassMat= new THREE.MeshStandardMaterial({ color:0xF4EFE4, roughness:0.2, metalness:0.1, emissive:0x554f3f, emissiveIntensity:0.3 });

    const half = wheelbase/2;

    // wheels
    [-half, half].forEach(x => {
      addMesh(group, new THREE.TorusGeometry(0.55,0.18,14,28), tireMat, x, -0.6, 0, 0,0,Math.PI/2);
      addMesh(group, new THREE.TorusGeometry(0.3,0.05,10,24), rimMat, x, -0.6, 0, 0,0,Math.PI/2);
    });

    // floorboard / body base
    addMesh(group, new THREE.BoxGeometry(wheelbase*0.72, 0.28, 0.85), bodyMat, 0, -0.25, 0);

    // front fork + headlight
    addMesh(group, new THREE.CylinderGeometry(0.06,0.06,1.3,10), darkMat, half-0.15, 0.05, 0, 0,0,-0.4);
    addMesh(group, new THREE.SphereGeometry(0.18,16,16), glassMat, half+0.15, 0.55, 0, 0,0,0);
    addMesh(group, new THREE.BoxGeometry(0.7,0.08,0.08), darkMat, half+0.05, 0.85, 0);

    // seat
    addMesh(group, boxy ? new THREE.BoxGeometry(seatLen+0.5, 0.22, 0.9) : new THREE.BoxGeometry(seatLen, 0.22, 0.75),
      darkMat, -0.15, 0.35, 0);

    // rear grab rail
    addMesh(group, new THREE.TorusGeometry(0.35,0.04,8,20,Math.PI), rimMat, -half+0.1, 0.4, 0, 0, Math.PI/2, 0);

    // cargo box for X1
    if (boxy){
      addMesh(group, new THREE.BoxGeometry(0.9,0.55,0.9), bodyMat, -half+0.55, 0.55, 0);
    }
  }

  function buildBattery(group, color){
    addMesh(group, new THREE.CylinderGeometry(0.55,0.55,1.1,20), new THREE.MeshStandardMaterial({ color, roughness:0.4, metalness:0.4 }));
    addMesh(group, new THREE.CylinderGeometry(0.18,0.18,0.25,12), new THREE.MeshStandardMaterial({ color:0xC9CDD3, roughness:0.3, metalness:0.7 }), 0, 0.68, 0);
  }

  function buildBrake(group){
    addMesh(group, new THREE.TorusGeometry(0.7,0.05,10,40), new THREE.MeshStandardMaterial({ color:0xC9CDD3, roughness:0.3, metalness:0.8 }));
    for (let i=0;i<6;i++){
      const a = (i/6)*Math.PI*2;
      addMesh(group, new THREE.BoxGeometry(0.05,0.35,0.05), new THREE.MeshStandardMaterial({ color:0x2a2622 }), Math.cos(a)*0.4, Math.sin(a)*0.4, 0, 0,0,a);
    }
    addMesh(group, new THREE.BoxGeometry(0.35,0.5,0.2), new THREE.MeshStandardMaterial({ color:0xE8462E, roughness:0.4 }), 0.7, 0, 0.15);
  }

  function buildMotor(group){
    addMesh(group, new THREE.CylinderGeometry(0.65,0.65,0.5,24), new THREE.MeshStandardMaterial({ color:0xC9CDD3, roughness:0.35, metalness:0.75 }), 0,0,0, Math.PI/2,0,0);
    for (let i=0;i<10;i++){
      const a = (i/10)*Math.PI*2;
      addMesh(group, new THREE.BoxGeometry(0.12,0.3,0.12), new THREE.MeshStandardMaterial({ color:0xDFF24C, roughness:0.5 }), Math.cos(a)*0.55, Math.sin(a)*0.55, 0.28, 0,0,a);
    }
  }

  function buildConnect(group){
    addMesh(group, new THREE.BoxGeometry(0.7,1.3,0.12), new THREE.MeshStandardMaterial({ color:0x2a2622, roughness:0.4 }));
    addMesh(group, new THREE.BoxGeometry(0.55,1.0,0.02), new THREE.MeshStandardMaterial({ color:0xDFF24C, emissive:0xDFF24C, emissiveIntensity:0.4, roughness:0.3 }), 0,0,0.08);
  }

  function buildHelmet(group){
    const shell = addMesh(group, new THREE.SphereGeometry(0.75,24,24,0,Math.PI*2,0,Math.PI*0.62), new THREE.MeshStandardMaterial({ color:0xE8462E, roughness:0.35, metalness:0.3 }));
    shell.rotation.x = Math.PI;
    addMesh(group, new THREE.SphereGeometry(0.78,24,12,0,Math.PI,Math.PI*0.35,Math.PI*0.35), new THREE.MeshStandardMaterial({ color:0xF4EFE4, roughness:0.15, metalness:0.1, transparent:true, opacity:0.5 }), 0,-0.05,0.1, 0,Math.PI/2,0);
  }

  function buildCharger(group){
    addMesh(group, new THREE.BoxGeometry(0.9,1.1,0.35), new THREE.MeshStandardMaterial({ color:0x2a2622, roughness:0.5 }));
    addMesh(group, new THREE.BoxGeometry(0.6,0.15,0.05), new THREE.MeshStandardMaterial({ color:0xDFF24C, emissive:0xDFF24C, emissiveIntensity:0.5 }), 0,0.3,0.2);
    addMesh(group, new THREE.CylinderGeometry(0.05,0.05,0.5,8), new THREE.MeshStandardMaterial({ color:0xC9CDD3, metalness:0.7 }), 0,-0.8,0);
  }

  function buildLock(group){
    addMesh(group, new THREE.TorusGeometry(0.5,0.14,16,32,Math.PI), new THREE.MeshStandardMaterial({ color:0xC9CDD3, metalness:0.8, roughness:0.25 }), 0,0.3,0);
    addMesh(group, new THREE.BoxGeometry(0.9,0.7,0.3), new THREE.MeshStandardMaterial({ color:0x2a2622, roughness:0.5 }), 0,-0.15,0);
    addMesh(group, new THREE.CylinderGeometry(0.06,0.06,0.15,10), new THREE.MeshStandardMaterial({ color:0xDFF24C, emissive:0xDFF24C, emissiveIntensity:0.6 }), 0,-0.1,0.16);
  }

  function buildTopbox(group){
    addMesh(group, new THREE.BoxGeometry(1.3,0.75,0.9), new THREE.MeshStandardMaterial({ color:0x1f1b18, roughness:0.5 }));
    addMesh(group, new THREE.BoxGeometry(1.34,0.1,0.94), new THREE.MeshStandardMaterial({ color:0xE8462E, roughness:0.4 }), 0,0.42,0);
  }

  /* ============ INSTANTIATE ============ */

  // Hero wheel (draggable + color picker)
  const heroEntry = makeViewport(document.getElementById('wheel-canvas'), { camZ:9, speed:0.006, tiltX:0.15 });
  const heroRim = buildWheel(heroEntry.group, 0xE8462E);
  enableDrag(heroEntry, document.getElementById('wheel-canvas'));
  document.querySelectorAll('.swatch').forEach(sw => {
    sw.addEventListener('click', () => {
      document.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
      sw.classList.add('active');
      heroRim.material.color.setHex(parseInt(sw.dataset.color, 16));
    });
  });

  // Model card mini scooters
  const s1Entry = makeViewport(document.getElementById('card-s1'), { camZ:6.5, speed:0.01, tiltX:0.2 });
  buildScooter(s1Entry.group, 0xC9CDD3, { wheelbase:3.0 });

  const r1Entry = makeViewport(document.getElementById('card-r1'), { camZ:6.5, speed:0.012, tiltX:0.2 });
  buildScooter(r1Entry.group, 0xE8462E, { wheelbase:3.3 });

  const x1Entry = makeViewport(document.getElementById('card-x1'), { camZ:6.5, speed:0.01, tiltX:0.2 });
  buildScooter(x1Entry.group, 0xDFF24C, { wheelbase:3.1, boxy:true });

  // Big showcase (draggable + zoomable + model switch)
  const showcaseCanvas = document.getElementById('showcase-canvas');
  const showcaseEntry = makeViewport(showcaseCanvas, { camZ:7, speed:0.004, tiltX:0.18 });
  enableDrag(showcaseEntry, showcaseCanvas);
  enableZoom(showcaseEntry, showcaseCanvas);

  const showcaseModels = {
    s1: { color:0xC9CDD3, opts:{ wheelbase:3.0 }, specs:[['Range','112 km'],['Top Speed','75 km/h'],['Kerb Weight','98 kg'],['Ground Clearance','165 mm']] },
    r1: { color:0xE8462E, opts:{ wheelbase:3.3 }, specs:[['Range','151 km'],['Top Speed','105 km/h'],['Kerb Weight','106 kg'],['Ground Clearance','170 mm']] },
    x1: { color:0xDFF24C, opts:{ wheelbase:3.1, boxy:true }, specs:[['Payload','90 kg'],['Top Speed','68 km/h'],['Kerb Weight','114 kg'],['Ground Clearance','160 mm']] }
  };

  function renderShowcase(key){
    while (showcaseEntry.group.children.length) showcaseEntry.group.remove(showcaseEntry.group.children[0]);
    const cfg = showcaseModels[key];
    buildScooter(showcaseEntry.group, cfg.color, cfg.opts);
    const specsEl = document.getElementById('showcase-specs');
    specsEl.innerHTML = cfg.specs.map(([label,val]) => `<div><span>${label}</span><strong>${val}</strong></div>`).join('');
  }
  renderShowcase('s1');

  document.querySelectorAll('.showcase-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.showcase-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      renderShowcase(tab.dataset.model);
    });
  });

  // Feature icons
  buildBattery(makeViewport(document.getElementById('icon-battery'), { camZ:3.2, speed:0.015 }).group, 0xDFF24C);
  buildBrake(makeViewport(document.getElementById('icon-brake'), { camZ:2.6, speed:0.012 }).group);
  buildMotor(makeViewport(document.getElementById('icon-motor'), { camZ:2.6, speed:0.015 }).group);
  buildConnect(makeViewport(document.getElementById('icon-connect'), { camZ:2.6, speed:0.01 }).group);

  // Accessory icons
  buildHelmet(makeViewport(document.getElementById('acc-helmet'), { camZ:3, speed:0.012 }).group);
  buildCharger(makeViewport(document.getElementById('acc-charger'), { camZ:3, speed:0.012 }).group);
  buildLock(makeViewport(document.getElementById('acc-lock'), { camZ:3, speed:0.012 }).group);
  buildTopbox(makeViewport(document.getElementById('acc-topbox'), { camZ:3.4, speed:0.012 }).group);

  /* ============ SINGLE RENDER LOOP ============ */
  function animate(){
    requestAnimationFrame(animate);
    registry.forEach(entry => {
      if (!entry.dragging) entry.group.rotation.y += entry.speed;
      entry.renderer.render(entry.scene, entry.camera);
    });
  }
  animate();
</script>

</body>
</html>