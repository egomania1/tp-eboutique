<?php include 'views/header.php'; ?>

<main class="main">
  <div class="smooth-wrapper">
    <div id="smooth-content">
      <section class="about">

        <div id="fluid-hero">
          <div id="fluid-controls">
            <div class="fctrl"><label>DENSITY</label><input type="range" id="sl-d" min="2" max="18" value="9" step="0.1"></div>
            <div class="fctrl"><label>SPEED</label><input type="range" id="sl-s" min="0" max="100" value="28"></div>
            <div class="fctrl"><label>TURB</label><input type="range" id="sl-t" min="0" max="100" value="45"></div>
            <div class="fctrl"><label>SIZE</label><input type="range" id="sl-f" min="10" max="100" value="100"></div>
            <input id="text-field" type="text" value="Root1" placeholder="TYPE..." autocomplete="off" spellcheck="false">
            <button class="fbtn" id="btn-p">&#9646;&#9646;</button>
          </div>
        </div>

        
        
        <div class="about-actions">
          <a href="index.php?page=catalogue" class="hero__btn hero__btn--primary">Voir le catalogue →</a>
          <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="index.php?page=register" class="hero__btn hero__btn--ghost">Créer un compte</a>
          <?php endif; ?>
        </div>


        <div id="fluid-text">
          <div class="about-text" style="opacity: 0; position: absolute; pointer-events: none;">
            Root1
          </div>
        </div>

        <div class="about-down">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <line x1="12" y1="4" x2="12" y2="20"/><polyline points="6,14 12,20 18,14"/>
          </svg>
        </div>

      </section>
    </div>
  </div>
</main>

<?php include 'views/footer.php'; ?>

<script>
// ── Animation GSAP ──
window.addEventListener('load', function() {
  gsap.timeline({ defaults: { ease: 'none' } })
    .fromTo('.about-actions', {
      opacity: 0, y: 10
    }, {
      opacity: 1, y: 0,
      duration: 0.6,
      ease: 'power2.out'
    }, '>0.3')
    .to('.about-down', {
      opacity: 1,
      ease: 'sine.out',
      duration: 0.4
    }, '>');
});
</script>


<script>
(() => {
  const { WebGLRenderer, Scene, OrthographicCamera, PlaneGeometry, Mesh, ShaderMaterial, CanvasTexture, Vector2, Vector4, LinearFilter } = THREE;
  const RENDERER    = { MAX_PIXEL_RATIO: 2 };
  const MASK        = { PROBE_FONT_SIZE: 300, PROBE_CANVAS_W: 2000, PROBE_CANVAS_H: 800, INK_BRIGHTNESS_THRESHOLD: 64, MAX_HEIGHT_FRACTION: 0.98 };
  const FONT_SCALE  = { FULL_WIDTH: 100 };
  const INPUT       = { DEFAULT_TEXT: "Root1", REBUILD_DEBOUNCE_MS: 60 };
  const RIPPLE      = { SLOT_COUNT: 4, DECAY_RATE: 1.1, RING_FREQUENCY: 7.0, RING_SPEED_MUL: 9.0, DIST_FALLOFF: 2.2, AMPLITUDE: 1.8 };
  const MOUSE_SWELL = { DIST_FALLOFF: 2.5, FREQUENCY: 9.0, SPEED_MUL: 5.0, AMPLITUDE: 1.2 };
  const CONTOUR     = { LINE_WIDTH: 0.22, AA_BASE: 0.015, AA_DENSITY_SCALE: 0.005, WAVE_SCALE: 0.5 };
  const MASK_BLEND  = { EDGE_LO: 0.38, EDGE_HI: 0.62 };
  // Dark bg (#060606) + green accent (#c8ff00)
  const PALETTE     = { BG: [0.024, 0.024, 0.024], FILL: [0.784, 1.0, 0.0] };

  const BASE_WAVES = [
    { amp:1.0,  fx: 2.4, fy: 0.9,  ts: 1.15,  phase: 0 },
    { amp:0.82, fx:-1.3, fy: 2.6,  ts:-0.87,  phase: Math.PI/2 },
    { amp:0.65, fx: 1.7, fy:-2.0,  ts: 1.41,  phase: Math.PI },
    { amp:0.7,  fx:-2.8, fy:-1.2,  ts:-0.66,  phase: 0.8 },
    { amp:0.5,  fx: 0.9, fy: 3.1,  ts: 1.05,  phase: 2.3 },
    { amp:0.38, fx: 3.2, fy: 0.7,  ts:-1.23,  phase: 4.7 },
    { amp:0.3,  fx:-1.0, fy:-2.4,  ts: 1.56,  phase: 5.5 },
    { amp:0.28, fx: 2.1, fy: 1.7,  ts: 0.54,  phase: 1.1 }
  ];
  const TURB_WAVES = [
    { amp:0.35, fx:5.6,  fy: 4.4,  ts: 1.1,  hFold:0.8 },
    { amp:0.18, fx:8.3,  fy:-7.0,  ts:-0.9,  hFold:1.3 },
    { amp:0.1,  fx:12.0, fy: 9.5,  ts: 1.6,  hFold:1.8 }
  ];

  const gf = n => Number.isInteger(n) ? n.toFixed(1) : String(n);
  const f4 = n => n.toFixed(4);
  const bw = w => { const ph = w.phase!==0 ? ` + ${f4(w.phase)}` : ""; return `        h += ${f4(w.amp)} * sin(${f4(w.fx)}*p.x + ${f4(w.fy)}*p.y + t*${f4(w.ts)}${ph});`; };
  const tw = w => `          h += uTurb * ${f4(w.amp)} * sin(${f4(w.fx)}*p.x + ${f4(w.fy)}*p.y + t*${f4(w.ts)} + h*${f4(w.hFold)});`;

  const hero = document.getElementById("fluid-hero");

  document.fonts.ready.then(() => {
    const W0 = hero.offsetWidth, H0 = hero.offsetHeight;

    const renderer = new WebGLRenderer({ antialias: true, alpha: true });
    renderer.setClearColor(0x000000, 0);
    renderer.setPixelRatio(Math.min(devicePixelRatio, RENDERER.MAX_PIXEL_RATIO));
    renderer.setSize(W0, H0);
    renderer.domElement.id = "fluid-canvas";
    hero.prepend(renderer.domElement);
    const scene  = new Scene();
    const camera = new OrthographicCamera(-1, 1, 1, -1, 0, 1);

    const maskCanvas = document.createElement("canvas");
    const maskCtx    = maskCanvas.getContext("2d");
    let maskTex = null;

    function buildMask(text) {
      const W = hero.offsetWidth, H = hero.offsetHeight;
      maskCanvas.width = W; maskCanvas.height = H;
      maskCtx.fillStyle = "#000"; maskCtx.fillRect(0, 0, W, H);
      const str = (text || "").toUpperCase() || INPUT.DEFAULT_TEXT;

      const pc = document.createElement("canvas");
      pc.width = MASK.PROBE_CANVAS_W; pc.height = MASK.PROBE_CANVAS_H;
      const cx = pc.getContext("2d");
      cx.fillStyle = "#000"; cx.fillRect(0, 0, pc.width, pc.height);
      cx.fillStyle = "#fff";
      cx.font = `900 ${MASK.PROBE_FONT_SIZE}px Unbounded, "Arial Black", sans-serif`;
      cx.textAlign = "center"; cx.textBaseline = "middle";
      cx.fillText(str, pc.width/2, pc.height/2);

      const pd = cx.getImageData(0, 0, pc.width, pc.height).data;
      let minX = pc.width, maxX = 0;
      for (let y = 0; y < pc.height; y++)
        for (let x = 0; x < pc.width; x++)
          if (pd[(y*pc.width+x)*4] > MASK.INK_BRIGHTNESS_THRESHOLD) { if(x<minX)minX=x; if(x>maxX)maxX=x; }
      const inkW = Math.max(1, maxX - minX);
      const fullFS = MASK.PROBE_FONT_SIZE * (W / inkW);
      const scale  = +document.getElementById("sl-f").value / FONT_SCALE.FULL_WIDTH;
      const finalFS = Math.min(fullFS * scale, H * MASK.MAX_HEIGHT_FRACTION);

      maskCtx.fillStyle = "#fff";
      maskCtx.font = `900 ${finalFS}px Unbounded, "Arial Black", sans-serif`;
      maskCtx.textAlign = "center"; maskCtx.textBaseline = "middle";
      maskCtx.fillText(str, W/2, H/2);

      if (maskTex) { maskTex.image = maskCanvas; maskTex.needsUpdate = true; }
      else { maskTex = new CanvasTexture(maskCanvas); maskTex.minFilter = LinearFilter; maskTex.magFilter = LinearFilter; }
    }

    buildMask(INPUT.DEFAULT_TEXT);
    console.log("First script - Mask built for:", INPUT.DEFAULT_TEXT);

    const uniforms = {
      uTime:    { value: 0.0 },
      uRes:     { value: new Vector2(W0 * renderer.getPixelRatio(), H0 * renderer.getPixelRatio()) },
      uMouse:   { value: new Vector2(0.5, 0.5) },
      uDensity: { value: 9.0 },
      uSpeed:   { value: 0.28 },
      uTurb:    { value: 0.45 },
      uMask:    { value: maskTex },
      uR0: { value: new Vector4(0,0,-1,0) },
      uR1: { value: new Vector4(0,0,-1,0) },
      uR2: { value: new Vector4(0,0,-1,0) },
      uR3: { value: new Vector4(0,0,-1,0) }
    };

    const fragmentShader = `
      precision highp float;
      uniform float uTime; uniform vec2 uRes; uniform vec2 uMouse;
      uniform float uDensity; uniform float uSpeed; uniform float uTurb;
      uniform sampler2D uMask;
      uniform vec4 uR0, uR1, uR2, uR3;
      const vec3 COLOR_BG   = vec3(${PALETTE.BG.map(v=>v.toFixed(3)).join(", ")});
      const vec3 COLOR_FILL = vec3(${PALETTE.FILL.map(v=>v.toFixed(3)).join(", ")});
      const float MASK_EDGE_LO = ${gf(MASK_BLEND.EDGE_LO)};
      const float MASK_EDGE_HI = ${gf(MASK_BLEND.EDGE_HI)};
      const float RIPPLE_DECAY_RATE   = ${gf(RIPPLE.DECAY_RATE)};
      const float RIPPLE_RING_FREQ    = ${gf(RIPPLE.RING_FREQUENCY)};
      const float RIPPLE_RING_SPEED   = ${gf(RIPPLE.RING_SPEED_MUL)};
      const float RIPPLE_DIST_FALLOFF = ${gf(RIPPLE.DIST_FALLOFF)};
      const float RIPPLE_AMPLITUDE    = ${gf(RIPPLE.AMPLITUDE)};
      const float SWELL_DIST_FALLOFF  = ${gf(MOUSE_SWELL.DIST_FALLOFF)};
      const float SWELL_FREQUENCY     = ${gf(MOUSE_SWELL.FREQUENCY)};
      const float SWELL_SPEED_MUL     = ${gf(MOUSE_SWELL.SPEED_MUL)};
      const float SWELL_AMPLITUDE     = ${gf(MOUSE_SWELL.AMPLITUDE)};
      const float CONTOUR_LINE_WIDTH  = ${gf(CONTOUR.LINE_WIDTH)};
      const float CONTOUR_AA_BASE     = ${gf(CONTOUR.AA_BASE)};
      const float CONTOUR_AA_DENSITY  = ${gf(CONTOUR.AA_DENSITY_SCALE)};
      const float CONTOUR_WAVE_SCALE  = ${gf(CONTOUR.WAVE_SCALE)};
      float tri(float x){ return abs(fract(x+0.5)-0.5)*2.0; }
      float ripple(vec4 r, vec2 uv, float t){
        if(r.z<0.0) return 0.0;
        float age=t-r.z; float decay=exp(-age*RIPPLE_DECAY_RATE);
        float ar=uRes.x/uRes.y; vec2 delta=(uv-r.xy)*vec2(ar,1.0);
        float dist=length(delta);
        return decay*sin(dist*RIPPLE_RING_FREQ-age*uSpeed*RIPPLE_RING_SPEED)*exp(-dist*RIPPLE_DIST_FALLOFF)*RIPPLE_AMPLITUDE;
      }
      void main(){
        vec2 uv=gl_FragCoord.xy/uRes;
        float ar=uRes.x/uRes.y;
        vec2 p=vec2(uv.x*ar, uv.y);
        float t=uTime*uSpeed;
        float mask=smoothstep(MASK_EDGE_LO,MASK_EDGE_HI,texture2D(uMask,uv).r);
        if(mask<0.001){ gl_FragColor=vec4(0.0,0.0,0.0,0.0); return; }
        float h=0.0;
${BASE_WAVES.map(bw).join("\n")}
        if(uTurb>0.0){
${TURB_WAVES.map(tw).join("\n")}
        }
        vec2 sd=(uv-uMouse)*vec2(ar,1.0); float sdist=length(sd);
        h+=SWELL_AMPLITUDE*exp(-sdist*SWELL_DIST_FALLOFF)*sin(sdist*SWELL_FREQUENCY-t*SWELL_SPEED_MUL);
        h+=ripple(uR0,uv,uTime); h+=ripple(uR1,uv,uTime);
        h+=ripple(uR2,uv,uTime); h+=ripple(uR3,uv,uTime);
        float bands=tri(h*uDensity*CONTOUR_WAVE_SCALE);
        float aa=CONTOUR_AA_BASE+CONTOUR_AA_DENSITY*uDensity;
        float line=1.0-smoothstep(CONTOUR_LINE_WIDTH-aa,CONTOUR_LINE_WIDTH+aa,bands);
        vec3 col=mix(COLOR_FILL,COLOR_BG,line);
        gl_FragColor=vec4(col, mask);
      }
    `;

    const mat = new ShaderMaterial({ uniforms, vertexShader:`void main(){gl_Position=vec4(position,1.0);}`, fragmentShader, transparent: true });
    scene.add(new Mesh(new PlaneGeometry(2,2), mat));

    window.addEventListener("resize", () => {
      const W=hero.offsetWidth, H=hero.offsetHeight;
      renderer.setSize(W,H);
      const pr=renderer.getPixelRatio();
      uniforms.uRes.value.set(W*pr, H*pr);
      buildMask(INPUT.DEFAULT_TEXT);
      uniforms.uMask.value=maskTex;
    });

    hero.addEventListener("mousemove", e => {
      const r=hero.getBoundingClientRect();
      uniforms.uMouse.value.set((e.clientX-r.left)/r.width, 1-(e.clientY-r.top)/r.height);
    });

    const rSlots=[uniforms.uR0,uniforms.uR1,uniforms.uR2,uniforms.uR3];
    let ri=0;
    hero.addEventListener("click", e => {
      if(e.target.closest("#fluid-controls")) return;
      const r=hero.getBoundingClientRect();
      rSlots[ri%RIPPLE.SLOT_COUNT].value.set((e.clientX-r.left)/r.width, 1-(e.clientY-r.top)/r.height, uniforms.uTime.value, 1);
      ri++;
    });

    document.getElementById("sl-d").addEventListener("input", e => { uniforms.uDensity.value=+e.target.value; });
    document.getElementById("sl-s").addEventListener("input", e => { uniforms.uSpeed.value=+e.target.value/100; });
    document.getElementById("sl-t").addEventListener("input", e => { uniforms.uTurb.value=+e.target.value/100; });
    document.getElementById("sl-f").addEventListener("input", () => { const tf=document.getElementById('text-field'); buildMask(tf?tf.value:INPUT.DEFAULT_TEXT); uniforms.uMask.value=maskTex; });

    let paused=false;
    const tfEl=document.getElementById('text-field');
    if(tfEl){ let rbT=null; tfEl.addEventListener('input',()=>{ clearTimeout(rbT); rbT=setTimeout(()=>{ buildMask(tfEl.value); uniforms.uMask.value=maskTex; },60); }); }
    document.getElementById("btn-p").addEventListener("click", function(){ paused=!paused; this.innerHTML=paused?"&#9654;":"&#9646;&#9646;"; });

    document.addEventListener("visibilitychange", () => { if(!document.hidden && paused===false) loop(performance.now()); });

    let lastTs=null, elapsed=0, rafId;
    function loop(ts){
      rafId=requestAnimationFrame(loop);
      const dt=lastTs===null?0:Math.min((ts-lastTs)/1000,0.05);
      lastTs=ts;
      if(!paused) elapsed+=dt;
      uniforms.uTime.value=elapsed;
      renderer.render(scene,camera);
    }
    loop(0);
  });
})();
</script>

<script>
(() => {
  const { WebGLRenderer, Scene, OrthographicCamera, PlaneGeometry, Mesh, ShaderMaterial, CanvasTexture, Vector2, Vector4, LinearFilter } = THREE;
  const PALETTE     = { BG: [0.024, 0.024, 0.024], FILL: [0.784, 1.0, 0.0] };
  const SPEED       = 0.18;
  const DENSITY     = 6.5;
  const TURB_VAL    = 0.30;
  const CONTOUR     = { LINE_WIDTH: 0.22, AA_BASE: 0.015, AA_DENSITY_SCALE: 0.005, WAVE_SCALE: 0.5 };
  const MASK_BLEND  = { EDGE_LO: 0.28, EDGE_HI: 0.58 };
  const MOUSE_SWELL = { DIST_FALLOFF: 2.5, FREQUENCY: 9.0, SPEED_MUL: 5.0, AMPLITUDE: 1.2 };
  const RIPPLE      = { SLOT_COUNT: 4, DECAY_RATE: 1.1, RING_FREQUENCY: 7.0, RING_SPEED_MUL: 9.0, DIST_FALLOFF: 2.2, AMPLITUDE: 1.8 };

  const BASE_WAVES = [
    { amp:1.0,  fx: 2.4, fy: 0.9,  ts: 1.15,  phase: 0 },
    { amp:0.82, fx:-1.3, fy: 2.6,  ts:-0.87,  phase: Math.PI/2 },
    { amp:0.65, fx: 1.7, fy:-2.0,  ts: 1.41,  phase: Math.PI },
    { amp:0.7,  fx:-2.8, fy:-1.2,  ts:-0.66,  phase: 0.8 },
    { amp:0.5,  fx: 0.9, fy: 3.1,  ts: 1.05,  phase: 2.3 },
    { amp:0.38, fx: 3.2, fy: 0.7,  ts:-1.23,  phase: 4.7 },
    { amp:0.3,  fx:-1.0, fy:-2.4,  ts: 1.56,  phase: 5.5 },
    { amp:0.28, fx: 2.1, fy: 1.7,  ts: 0.54,  phase: 1.1 }
  ];
  const TURB_WAVES = [
    { amp:0.35, fx:5.6,  fy: 4.4,  ts: 1.1,  hFold:0.8 },
    { amp:0.18, fx:8.3,  fy:-7.0,  ts:-0.9,  hFold:1.3 },
    { amp:0.1,  fx:12.0, fy: 9.5,  ts: 1.6,  hFold:1.8 }
  ];

  const gf = n => Number.isInteger(n) ? n.toFixed(1) : String(n);
  const f4 = n => n.toFixed(4);
  const bw = w => { const ph = w.phase!==0 ? ` + ${f4(w.phase)}` : ""; return `        h += ${f4(w.amp)} * sin(${f4(w.fx)}*p.x + ${f4(w.fy)}*p.y + t*${f4(w.ts)}${ph});`; };
  const tw = w => `          h += uTurb * ${f4(w.amp)} * sin(${f4(w.fx)}*p.x + ${f4(w.fy)}*p.y + t*${f4(w.ts)} + h*${f4(w.hFold)});`;

  // Texte brut pour le masque canvas (word-wrap manuel)
  const PLAIN_TEXT = "Explorez notre sélection de produits dédiés à la cybersécurité et au développement. Des outils pensés pour les passionnés, disponibles sur une plateforme construite entièrement en PHP natif.";

  function buildMask(container, maskCanvas, maskCtx) {
    const W = container.offsetWidth;
    const H = container.offsetHeight;
    if (!W || !H) return;

    maskCanvas.width  = W;
    maskCanvas.height = H;
    maskCtx.fillStyle = '#000';
    maskCtx.fillRect(0, 0, W, H);

    const textEl = container.querySelector('.about-text');
    if (!textEl) return;
    const cs = getComputedStyle(textEl);
    const fontSize = parseFloat(cs.fontSize);
    const lh       = fontSize * 1.45;

    maskCtx.font         = `400 ${fontSize}px 'Cormorant Garamond', serif`;
    maskCtx.fillStyle    = '#fff';
    maskCtx.textAlign    = 'center';
    maskCtx.textBaseline = 'alphabetic';

    // Word-wrap manuel pour coller au rendu navigateur
    const maxW = Math.min(W * 0.92, 860);
    const words = PLAIN_TEXT.split(' ');
    const lines = [];
    let line = '';
    for (const w of words) {
      const test = line ? line + ' ' + w : w;
      if (maskCtx.measureText(test).width > maxW && line) { lines.push(line); line = w; }
      else { line = test; }
    }
    if (line) lines.push(line);

    const totalH = lines.length * lh;
    let y = (H - totalH) / 2 + fontSize;
    for (const l of lines) { maskCtx.fillText(l, W / 2, y); y += lh; }
  }

  document.fonts.ready.then(() => {
    const container = document.getElementById('fluid-text');
    if (!container) return;

    const W0 = container.offsetWidth  || 900;
    const H0 = container.offsetHeight || 220;

    const renderer = new WebGLRenderer({ antialias: true, alpha: true });
    renderer.setClearColor(0x000000, 0);
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
    renderer.setSize(W0, H0);
    renderer.domElement.id = 'fluid-text-canvas';
    container.prepend(renderer.domElement);

    const scene  = new Scene();
    const camera = new OrthographicCamera(-1, 1, 1, -1, 0, 1);

    const maskCanvas = document.createElement('canvas');
    const maskCtx    = maskCanvas.getContext('2d');
    let   maskTex    = null;
    let   uniforms   = null;

    function rebuildMask() {
      buildMask(container, maskCanvas, maskCtx);
      if (maskTex) { maskTex.image = maskCanvas; maskTex.needsUpdate = true; }
      else { maskTex = new CanvasTexture(maskCanvas); maskTex.minFilter = LinearFilter; maskTex.magFilter = LinearFilter; }
      if (uniforms) uniforms.uMask.value = maskTex;
    }
    rebuildMask();

    uniforms = {
      uTime:    { value: 0.0 },
      uRes:     { value: new Vector2(W0 * renderer.getPixelRatio(), H0 * renderer.getPixelRatio()) },
      uMouse:   { value: new Vector2(0.5, 0.5) },
      uDensity: { value: DENSITY },
      uSpeed:   { value: SPEED },
      uTurb:    { value: TURB_VAL },
      uMask:    { value: maskTex },
      uR0: { value: new Vector4(0,0,-1,0) },
      uR1: { value: new Vector4(0,0,-1,0) },
      uR2: { value: new Vector4(0,0,-1,0) },
      uR3: { value: new Vector4(0,0,-1,0) }
    };

    const fragmentShader = `
      precision highp float;
      uniform float uTime; uniform vec2 uRes; uniform vec2 uMouse;
      uniform float uDensity; uniform float uSpeed; uniform float uTurb;
      uniform sampler2D uMask;
      uniform vec4 uR0, uR1, uR2, uR3;
      const vec3 COLOR_BG   = vec3(${PALETTE.BG.map(v=>v.toFixed(3)).join(", ")});
      const vec3 COLOR_FILL = vec3(${PALETTE.FILL.map(v=>v.toFixed(3)).join(", ")});
      const float MASK_EDGE_LO = ${gf(MASK_BLEND.EDGE_LO)};
      const float MASK_EDGE_HI = ${gf(MASK_BLEND.EDGE_HI)};
      const float RIPPLE_DECAY_RATE   = ${gf(RIPPLE.DECAY_RATE)};
      const float RIPPLE_RING_FREQ    = ${gf(RIPPLE.RING_FREQUENCY)};
      const float RIPPLE_RING_SPEED   = ${gf(RIPPLE.RING_SPEED_MUL)};
      const float RIPPLE_DIST_FALLOFF = ${gf(RIPPLE.DIST_FALLOFF)};
      const float RIPPLE_AMPLITUDE    = ${gf(RIPPLE.AMPLITUDE)};
      const float SWELL_DIST_FALLOFF  = ${gf(MOUSE_SWELL.DIST_FALLOFF)};
      const float SWELL_FREQUENCY     = ${gf(MOUSE_SWELL.FREQUENCY)};
      const float SWELL_SPEED_MUL     = ${gf(MOUSE_SWELL.SPEED_MUL)};
      const float SWELL_AMPLITUDE     = ${gf(MOUSE_SWELL.AMPLITUDE)};
      const float CONTOUR_LINE_WIDTH  = ${gf(CONTOUR.LINE_WIDTH)};
      const float CONTOUR_AA_BASE     = ${gf(CONTOUR.AA_BASE)};
      const float CONTOUR_AA_DENSITY  = ${gf(CONTOUR.AA_DENSITY_SCALE)};
      const float CONTOUR_WAVE_SCALE  = ${gf(CONTOUR.WAVE_SCALE)};
      float tri(float x){ return abs(fract(x+0.5)-0.5)*2.0; }
      float ripple(vec4 r, vec2 uv, float t){
        if(r.z<0.0) return 0.0;
        float age=t-r.z; float decay=exp(-age*RIPPLE_DECAY_RATE);
        float ar=uRes.x/uRes.y; vec2 delta=(uv-r.xy)*vec2(ar,1.0);
        float dist=length(delta);
        return decay*sin(dist*RIPPLE_RING_FREQ-age*uSpeed*RIPPLE_RING_SPEED)*exp(-dist*RIPPLE_DIST_FALLOFF)*RIPPLE_AMPLITUDE;
      }
      void main(){
        vec2 uv=gl_FragCoord.xy/uRes;
        float ar=uRes.x/uRes.y;
        vec2 p=vec2(uv.x*ar, uv.y);
        float t=uTime*uSpeed;
        float mask=smoothstep(MASK_EDGE_LO,MASK_EDGE_HI,texture2D(uMask,uv).r);
        if(mask<0.001){ gl_FragColor=vec4(0.0,0.0,0.0,0.0); return; }
        float h=0.0;
${BASE_WAVES.map(bw).join("\n")}
        if(uTurb>0.0){
${TURB_WAVES.map(tw).join("\n")}
        }
        vec2 sd=(uv-uMouse)*vec2(ar,1.0); float sdist=length(sd);
        h+=SWELL_AMPLITUDE*exp(-sdist*SWELL_DIST_FALLOFF)*sin(sdist*SWELL_FREQUENCY-t*SWELL_SPEED_MUL);
        h+=ripple(uR0,uv,uTime); h+=ripple(uR1,uv,uTime);
        h+=ripple(uR2,uv,uTime); h+=ripple(uR3,uv,uTime);
        float bands=tri(h*uDensity*CONTOUR_WAVE_SCALE);
        float aa=CONTOUR_AA_BASE+CONTOUR_AA_DENSITY*uDensity;
        float line=1.0-smoothstep(CONTOUR_LINE_WIDTH-aa,CONTOUR_LINE_WIDTH+aa,bands);
        vec3 col=mix(COLOR_FILL,COLOR_BG,line);
        gl_FragColor=vec4(col, mask);
      }
    `;

    const mat = new ShaderMaterial({
      uniforms,
      vertexShader: `void main(){ gl_Position = vec4(position, 1.0); }`,
      fragmentShader,
      transparent: true
    });
    scene.add(new Mesh(new PlaneGeometry(2, 2), mat));

    window.addEventListener('resize', () => {
      const W = container.offsetWidth, H = container.offsetHeight;
      renderer.setSize(W, H);
      const pr = renderer.getPixelRatio();
      uniforms.uRes.value.set(W * pr, H * pr);
      rebuildMask();
    });

    container.addEventListener('mousemove', e => {
      const r = container.getBoundingClientRect();
      uniforms.uMouse.value.set((e.clientX - r.left) / r.width, 1 - (e.clientY - r.top) / r.height);
    });

    const rSlots = [uniforms.uR0, uniforms.uR1, uniforms.uR2, uniforms.uR3];
    let ri = 0;
    container.addEventListener('click', e => {
      if (e.target.closest('.about-hover')) return; // ne pas interférer avec les liens hover
      const r = container.getBoundingClientRect();
      rSlots[ri % RIPPLE.SLOT_COUNT].value.set((e.clientX - r.left) / r.width, 1 - (e.clientY - r.top) / r.height, uniforms.uTime.value, 1);
      ri++;
    });

    let lastTs = null, elapsed = 0;
    function loop(ts) {
      requestAnimationFrame(loop);
      const dt = lastTs === null ? 0 : Math.min((ts - lastTs) / 1000, 0.05);
      lastTs = ts;
      elapsed += dt;
      uniforms.uTime.value = elapsed;
      renderer.render(scene, camera);
    }
    loop(0);
  });
})();
</script>
