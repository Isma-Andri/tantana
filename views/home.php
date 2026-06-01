<?php
// views/home.php
$pageTitle = 'Accueil';
require __DIR__ . '/partials/header.php';
?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'DM Sans',sans-serif;overflow:hidden}
#hero{position:relative;width:100%;height:100vh;overflow:hidden;background:#000}
#mesh-canvas{position:absolute;inset:0;width:100%;height:100%;z-index:1}
#wireframe-canvas{position:absolute;inset:0;width:100%;height:100%;z-index:2;opacity:.35}
#hero::after{content:'';position:absolute;inset:0;z-index:3;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='1'/%3E%3C/svg%3E");opacity:.04;pointer-events:none}
#svg-filters{position:absolute;width:0;height:0;overflow:hidden}
#site-header{position:absolute;top:0;left:0;right:0;z-index:30;display:flex;align-items:center;justify-content:space-between;padding:1.5rem}
.logo-wrap{display:flex;align-items:center}
.logo-wrap svg{color:#fff}
.site-nav{display:flex;align-items:center;gap:.25rem}
.site-nav a{color:rgba(255,255,255,.8);text-decoration:none;font-size:.75rem;font-weight:300;padding:.5rem .75rem;border-radius:9999px;transition:color .2s,background .2s}
.site-nav a:hover{color:#fff;background:rgba(255,255,255,.1)}
.gooey-btn-group{position:relative;display:flex;align-items:center}
.btn-login{position:relative;z-index:10;padding:0 1.5rem;height:2rem;border-radius:9999px;background:#fff;color:#000;font-size:.75rem;font-weight:400;border:none;cursor:pointer;display:flex;align-items:center;text-decoration:none;transition:background .15s;white-space:nowrap}
.btn-login:hover{background:rgba(255,255,255,.9)}
.btn-arrow{position:absolute;right:0;z-index:9;width:2rem;height:2rem;border-radius:9999px;background:#fff;color:#000;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transform:translateX(-2.5rem);transition:transform .3s cubic-bezier(.34,1.56,.64,1)}
.gooey-btn-group:hover .btn-arrow{transform:translateX(-4.8rem)}
.btn-arrow svg{width:.75rem;height:.75rem}
#hero-content{position:absolute;bottom:2rem;left:2rem;z-index:20;max-width:30rem;animation:fadeUp .7s .1s both}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
.hero-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .9rem;border-radius:9999px;background:rgba(255,255,255,.06);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);margin-bottom:1rem;position:relative;overflow:hidden}
.hero-badge::before{content:'';position:absolute;top:0;left:.25rem;right:.25rem;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);border-radius:9999px}
.hero-badge span{color:rgba(255,255,255,.9);font-size:.75rem;font-weight:300;position:relative;z-index:1}
.hero-h1{font-size:clamp(2.5rem,6vw,4rem);line-height:1.1;font-weight:300;color:#fff;letter-spacing:-.03em;margin-bottom:1rem}
.hero-h1 em{font-style:italic;font-weight:500;font-family:'Syne',sans-serif}
.hero-desc{font-size:.8rem;font-weight:300;color:rgba(255,255,255,.65);line-height:1.7;margin-bottom:1.25rem;max-width:26rem}
.hero-btns{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
.btn-outline{padding:.7rem 2rem;border-radius:9999px;background:transparent;border:1px solid rgba(255,255,255,.3);color:#fff;font-size:.75rem;font-weight:400;cursor:pointer;text-decoration:none;transition:background .2s,border-color .2s;display:inline-flex;align-items:center}
.btn-outline:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.5)}
.btn-white{padding:.7rem 2rem;border-radius:9999px;background:#fff;color:#000;font-size:.75rem;font-weight:400;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:background .2s;border:none}
.btn-white:hover{background:rgba(255,255,255,.9)}
#pulsing-circle{position:absolute;bottom:2rem;right:2rem;z-index:30;width:5rem;height:5rem;display:flex;align-items:center;justify-content:center;animation:fadeUp .7s .3s both}
#pulse-canvas{position:absolute;inset:0;border-radius:50%}
.rotating-text-svg{position:absolute;width:160%;height:160%;top:-30%;left:-30%;animation:spin 20s linear infinite;pointer-events:none}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.rotating-text-svg text{font-size:8px;fill:rgba(255,255,255,.75);font-family:'DM Sans',sans-serif;font-weight:300;letter-spacing:.5px}
@media(max-width:640px){.site-nav{display:none}#hero-content{left:1rem;bottom:1rem;max-width:calc(100% - 7rem)}#pulsing-circle{bottom:1rem;right:1rem}}
</style>

<svg id="svg-filters" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <defs>
    <filter id="glass-effect" x="-50%" y="-50%" width="200%" height="200%">
      <feTurbulence baseFrequency="0.005" numOctaves="1" result="noise"/>
      <feDisplacementMap in="SourceGraphic" in2="noise" scale="0.3"/>
    </filter>
    <filter id="gooey-filter" x="-50%" y="-50%" width="200%" height="200%">
      <feGaussianBlur in="SourceGraphic" stdDeviation="4" result="blur"/>
      <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="gooey"/>
      <feComposite in="SourceGraphic" in2="gooey" operator="atop"/>
    </filter>
  </defs>
</svg>

<section id="hero" aria-label="Bienvenue sur Tantana">
  <canvas id="mesh-canvas" aria-hidden="true"></canvas>
  <canvas id="wireframe-canvas" aria-hidden="true"></canvas>

  <header id="site-header">
    <div class="logo-wrap">
      <svg width="36" height="36" viewBox="0 0 400 400" fill="currentColor" aria-label="Tantana logo">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M358.333 0C381.345 0 400 18.6548 400 41.6667V295.833C400 298.135 398.134 300 395.833 300H270.833C268.532 300 266.667 301.865 266.667 304.167V395.833C266.667 398.134 264.801 400 262.5 400H41.6667C18.6548 400 0 381.345 0 358.333V304.72C0 301.793 1.54269 299.081 4.05273 297.575L153.76 207.747C157.159 205.708 156.02 200.679 152.376 200.065L151.628 200H4.16667C1.86548 200 0 198.135 0 195.833V104.167C0 101.865 1.86548 100 4.16667 100H162.5C164.801 100 166.667 98.1345 166.667 95.8333V4.16667C166.667 1.86548 168.532 0 170.833 0H358.333ZM170.833 100C168.532 100 166.667 101.865 166.667 104.167V295.833C166.667 298.135 168.532 300 170.833 300H262.5C264.801 300 266.667 298.135 266.667 295.833V104.167C266.667 101.865 264.801 100 262.5 100H170.833Z"/>
      </svg>
    </div>
    <nav class="site-nav" aria-label="Navigation principale">
      <a href="#features">Fonctionnalités</a>
      <a href="#pricing">Tarifs</a>
      <a href="#docs">Documentation</a>
    </nav>
    <div class="gooey-btn-group" style="filter:url(#gooey-filter)">
      <a href="login" class="btn-login" aria-label="Se connecter">Connexion</a>
      <button class="btn-arrow" aria-hidden="true" tabindex="-1">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 17L17 7M17 7H7M17 7V17"/>
        </svg>
      </button>
    </div>
  </header>

  <main id="hero-content">
    <div class="hero-badge" style="filter:url(#glass-effect)">
      <span>Gérez vos projets facilement</span>
    </div>
    <h1 class="hero-h1">
      <em>Simple</em> et puissant<br>
      <span style="font-weight:300">pour votre équipe</span>
    </h1>
    <p class="hero-desc">
      Organisez vos tâches, collaborez en temps réel et livrez vos projets dans les délais.
      Tantana adapte chaque flux de travail à la réalité de votre équipe.
    </p>
    <div class="hero-btns">
      <a href="login"    class="btn-outline">Se connecter</a>
      <a href="register" class="btn-white">Commencer gratuitement</a>
    </div>
  </main>

  <div id="pulsing-circle" aria-hidden="true">
    <canvas id="pulse-canvas" width="80" height="80"></canvas>
    <svg class="rotating-text-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <defs><path id="txt-circle" d="M 50,50 m -35,0 a 35,35 0 1,1 70,0 a 35,35 0 1,1 -70,0"/></defs>
      <text><textPath href="#txt-circle" startOffset="0%">Tantana - Gestion de projet • Tantana - Gestion de projet •</textPath></text>
    </svg>
  </div>
</section>

<script>
(function(){
  "use strict";
  const mc=document.getElementById('mesh-canvas'),mx=mc.getContext('2d');
  const wc=document.getElementById('wireframe-canvas'),wx=wc.getContext('2d');
  const pc=document.getElementById('pulse-canvas'),px=pc.getContext('2d');

  const MESH_COLORS=[[0,0,0],[139,69,19],[255,255,255],[62,39,35],[93,64,55]];
  const SPOT_COLORS=["#BEECFF","#E77EDC","#FF4C3E","#00FF88","#FFD700","#FF6B35","#8A2BE2"];

  function hash(n){let x=Math.sin(n)*43758.5453;return x-Math.floor(x);}
  function noise(x,y,t){const ix=Math.floor(x),iy=Math.floor(y),fx=x-ix,fy=y-iy,ux=fx*fx*(3-2*fx),uy=fy*fy*(3-2*fy);return hash(ix+iy*57+t*13)+(hash(ix+1+iy*57+t*13)-hash(ix+iy*57+t*13))*ux+(hash(ix+(iy+1)*57+t*13)-hash(ix+iy*57+t*13))*uy;}
  function lerp(a,b,t){return a.map((v,i)=>Math.round(v+(b[i]-v)*t));}
  function pickColor(n,t){const s=((n+t*.15)%1)*(MESH_COLORS.length-1),lo=Math.floor(s);return lerp(MESH_COLORS[lo],MESH_COLORS[Math.min(lo+1,MESH_COLORS.length-1)],s-lo);}
  function hexRgba(h,a){return `rgba(${parseInt(h.slice(1,3),16)},${parseInt(h.slice(3,5),16)},${parseInt(h.slice(5,7),16)},${a})`;}

  function resize(){
    mc.width=wc.width=window.innerWidth;
    mc.height=wc.height=window.innerHeight;
  }

  let mt=0,wt=0,pt=0;
  const CELLS=6,SPOTS=12;

  function drawMesh(){
    const W=mc.width,H=mc.height,cw=W/CELLS,ch=H/CELLS;
    for(let cy=0;cy<CELLS;cy++)for(let cx=0;cx<CELLS;cx++){
      const[r,g,b]=pickColor(noise(cx*.6,cy*.6,mt),mt);
      const grd=mx.createRadialGradient(cx*cw+cw/2,cy*ch+ch/2,0,cx*cw+cw/2,cy*ch+ch/2,Math.max(cw,ch)*.9);
      grd.addColorStop(0,`rgba(${r},${g},${b},.85)`);grd.addColorStop(1,`rgba(${r},${g},${b},0)`);
      mx.fillStyle=grd;mx.beginPath();mx.ellipse(cx*cw+cw/2,cy*ch+ch/2,Math.max(cw,ch)*1.1,Math.max(cw,ch)*.8,Math.sin(mt+cx)*.5,0,Math.PI*2);mx.fill();
    }
    mt+=.003;
  }

  function drawWireframe(){
    const W=wc.width,H=wc.height;wx.clearRect(0,0,W,H);wx.strokeStyle="rgba(255,255,255,.18)";wx.lineWidth=.5;
    for(let i=0;i<=14;i++){const f=i/14;
      wx.beginPath();for(let x=0;x<=W;x+=4){const y=f*H+12*Math.sin(.003*x+wt+f*Math.PI*2);x===0?wx.moveTo(x,y):wx.lineTo(x,y);}wx.stroke();
      wx.beginPath();for(let y=0;y<=H;y+=4){const x=f*W+10*Math.sin(.003*y-wt+f*Math.PI*2);y===0?wx.moveTo(x,y):wx.lineTo(x,y);}wx.stroke();
    }
    wt+=.012;
  }

  function drawPulse(){
    const W=pc.width,H=pc.height,cx=W/2,cy=H/2,R=W*.38;
    px.clearRect(0,0,W,H);
    px.beginPath();px.arc(cx,cy,R,0,Math.PI*2);px.fillStyle="rgba(0,0,0,.25)";px.fill();
    for(let i=0;i<SPOTS;i++){
      const a=(i/SPOTS)*Math.PI*2+pt,ha=.22+.08*Math.sin(pt*2+i),br=.6+.4*Math.abs(Math.sin(pt+i)),pu=.8+.2*Math.sin(pt*3+i*.7);
      const grd=px.createRadialGradient(cx+Math.cos(a)*R,cy+Math.sin(a)*R,0,cx,cy,R*1.1);
      grd.addColorStop(0,hexRgba(SPOT_COLORS[i%SPOT_COLORS.length],br*pu*.9));grd.addColorStop(1,hexRgba(SPOT_COLORS[i%SPOT_COLORS.length],0));
      px.beginPath();px.arc(cx,cy,R,a-ha,a+ha);px.strokeStyle=grd;px.lineWidth=4*pu;px.lineCap="round";px.stroke();
    }
    px.beginPath();px.arc(cx,cy,R,0,Math.PI*2);px.strokeStyle="rgba(255,255,255,.12)";px.lineWidth=.5;px.stroke();
    pt+=.025;
  }

  function animate(){
    mx.fillStyle="rgba(0,0,0,.04)";mx.fillRect(0,0,mc.width,mc.height);
    drawMesh();drawWireframe();drawPulse();
    requestAnimationFrame(animate);
  }

  window.addEventListener('resize',resize);
  resize();
  mx.fillStyle="#000";mx.fillRect(0,0,mc.width,mc.height);
  animate();
})();
</script>

</body>
</html>
