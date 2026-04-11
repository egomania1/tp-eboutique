<!-- Fermeture de la zone principale -->
</main>


<!-- ===== SCRIPTS CDN ===== -->

<script>
// ============================================
// BEAMS BACKGROUND — MeshStandardMaterial + onBeforeCompile
// Reproduction fidèle du composant React Beams
// ============================================
(function () {
  var wrap = document.querySelector('.canvas-wrap');
  if (!wrap || typeof THREE === 'undefined') return;

  // ── Config (identique au composant React) ───
  var BEAM_WIDTH      = 2.6;
  var BEAM_HEIGHT     = 17;
  var BEAM_NUMBER     = 15;
  var SPEED           = 10;
  var NOISE_INTENSITY = 1.75;
  var SCALE           = 0.2;
  var ROTATION_DEG    = 30;

  // ── Renderer ────────────────────────────────
  var W = window.innerWidth, H = window.innerHeight;
  var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(W, H);
  renderer.domElement.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;display:block;';
  wrap.appendChild(renderer.domElement);

  // ── Scene & Camera ───────────────────────────
  var scene  = new THREE.Scene();
  scene.background = new THREE.Color(0x060606);
  var camera = new THREE.PerspectiveCamera(30, W / H, 0.1, 100);
  camera.position.set(0, 0, 20);
  camera.updateMatrixWorld();

  // ── Lights ───────────────────────────────────
  scene.add(new THREE.AmbientLight(0xffffff, 1));
  var dirLight = new THREE.DirectionalLight(0xffffff, 1);
  dirLight.position.set(0, 3, 10);
  scene.add(dirLight);

  // ── Noise GLSL (Perlin 3D — identique au composant) ─
  var NOISE_GLSL = '\n\
float random2(in vec2 st){return fract(sin(dot(st.xy,vec2(12.9898,78.233)))*43758.5453123);}\n\
float noise2d(in vec2 st){\n\
  vec2 i=floor(st);vec2 f=fract(st);\n\
  float a=random2(i);float b=random2(i+vec2(1,0));\n\
  float c=random2(i+vec2(0,1));float d=random2(i+vec2(1,1));\n\
  vec2 u=f*f*(3.0-2.0*f);\n\
  return mix(a,b,u.x)+(c-a)*u.y*(1.0-u.x)+(d-b)*u.x*u.y;\n\
}\n\
vec4 bpermute(vec4 x){return mod(((x*34.0)+1.0)*x,289.0);}\n\
vec4 btaylorInvSqrt(vec4 r){return 1.79284291400159-0.85373472095314*r;}\n\
vec3 bfade(vec3 t){return t*t*t*(t*(t*6.0-15.0)+10.0);}\n\
float cnoise(vec3 P){\n\
  vec3 Pi0=floor(P);vec3 Pi1=Pi0+vec3(1.0);\n\
  Pi0=mod(Pi0,289.0);Pi1=mod(Pi1,289.0);\n\
  vec3 Pf0=fract(P);vec3 Pf1=Pf0-vec3(1.0);\n\
  vec4 ix=vec4(Pi0.x,Pi1.x,Pi0.x,Pi1.x);\n\
  vec4 iy=vec4(Pi0.yy,Pi1.yy);\n\
  vec4 iz0=Pi0.zzzz;vec4 iz1=Pi1.zzzz;\n\
  vec4 ixy=bpermute(bpermute(ix)+iy);\n\
  vec4 ixy0=bpermute(ixy+iz0);vec4 ixy1=bpermute(ixy+iz1);\n\
  vec4 gx0=ixy0/7.0;vec4 gy0=fract(floor(gx0)/7.0)-0.5;\n\
  gx0=fract(gx0);vec4 gz0=vec4(0.5)-abs(gx0)-abs(gy0);\n\
  vec4 sz0=step(gz0,vec4(0.0));\n\
  gx0-=sz0*(step(0.0,gx0)-0.5);gy0-=sz0*(step(0.0,gy0)-0.5);\n\
  vec4 gx1=ixy1/7.0;vec4 gy1=fract(floor(gx1)/7.0)-0.5;\n\
  gx1=fract(gx1);vec4 gz1=vec4(0.5)-abs(gx1)-abs(gy1);\n\
  vec4 sz1=step(gz1,vec4(0.0));\n\
  gx1-=sz1*(step(0.0,gx1)-0.5);gy1-=sz1*(step(0.0,gy1)-0.5);\n\
  vec3 g000=vec3(gx0.x,gy0.x,gz0.x);vec3 g100=vec3(gx0.y,gy0.y,gz0.y);\n\
  vec3 g010=vec3(gx0.z,gy0.z,gz0.z);vec3 g110=vec3(gx0.w,gy0.w,gz0.w);\n\
  vec3 g001=vec3(gx1.x,gy1.x,gz1.x);vec3 g101=vec3(gx1.y,gy1.y,gz1.y);\n\
  vec3 g011=vec3(gx1.z,gy1.z,gz1.z);vec3 g111=vec3(gx1.w,gy1.w,gz1.w);\n\
  vec4 norm0=btaylorInvSqrt(vec4(dot(g000,g000),dot(g010,g010),dot(g100,g100),dot(g110,g110)));\n\
  g000*=norm0.x;g010*=norm0.y;g100*=norm0.z;g110*=norm0.w;\n\
  vec4 norm1=btaylorInvSqrt(vec4(dot(g001,g001),dot(g011,g011),dot(g101,g101),dot(g111,g111)));\n\
  g001*=norm1.x;g011*=norm1.y;g101*=norm1.z;g111*=norm1.w;\n\
  float n000=dot(g000,Pf0);float n100=dot(g100,vec3(Pf1.x,Pf0.yz));\n\
  float n010=dot(g010,vec3(Pf0.x,Pf1.y,Pf0.z));float n110=dot(g110,vec3(Pf1.xy,Pf0.z));\n\
  float n001=dot(g001,vec3(Pf0.xy,Pf1.z));float n101=dot(g101,vec3(Pf1.x,Pf0.y,Pf1.z));\n\
  float n011=dot(g011,vec3(Pf0.x,Pf1.yz));float n111=dot(g111,Pf1);\n\
  vec3 fade_xyz=bfade(Pf0);\n\
  vec4 n_z=mix(vec4(n000,n100,n010,n110),vec4(n001,n101,n011,n111),fade_xyz.z);\n\
  vec2 n_yz=mix(n_z.xy,n_z.zw,fade_xyz.y);\n\
  return 2.2*mix(n_yz.x,n_yz.y,fade_xyz.x);\n\
}\n';

  // ── Inject dans MeshStandardMaterial via onBeforeCompile ──
  // C'est exactement ce que fait extendMaterial() dans le composant React
  var shaderRef = null;

  var material = new THREE.MeshStandardMaterial({
    color:    0x000000,
    roughness: 0.3,
    metalness: 0.3
  });

  material.onBeforeCompile = function (shader) {
    // Uniforms custom
    shader.uniforms.time           = { value: 0 };
    shader.uniforms.uSpeed         = { value: SPEED };
    shader.uniforms.uScale         = { value: SCALE };
    shader.uniforms.uNoiseIntensity = { value: NOISE_INTENSITY };

    // ── Vertex shader ──
    // On injecte le noise + les fonctions helper avant le code THREE
    var vertexHeader = NOISE_GLSL + '\n\
uniform float time;\n\
uniform float uSpeed;\n\
uniform float uScale;\n\
float getPos(vec3 pos){\n\
  vec3 noisePos=vec3(pos.x*0.,pos.y-uv.y,pos.z+time*uSpeed*3.)*uScale;\n\
  return cnoise(noisePos);\n\
}\n\
vec3 getCurrentPos(vec3 pos){vec3 p=pos;p.z+=getPos(pos);return p;}\n\
vec3 getNormal(vec3 pos){\n\
  vec3 cur=getCurrentPos(pos);\n\
  vec3 nx=getCurrentPos(pos+vec3(0.01,0.0,0.0));\n\
  vec3 nz=getCurrentPos(pos+vec3(0.0,-0.01,0.0));\n\
  return normalize(cross(normalize(nz-cur),normalize(nx-cur)));\n\
}\n';

    shader.vertexShader = vertexHeader + shader.vertexShader;

    // Déplacement Z — remplace #include <begin_vertex>
    shader.vertexShader = shader.vertexShader.replace(
      '#include <begin_vertex>',
      '#include <begin_vertex>\ntransformed.z += getPos(transformed.xyz);'
    );

    // Normale recalculée — remplace #include <beginnormal_vertex>
    shader.vertexShader = shader.vertexShader.replace(
      '#include <beginnormal_vertex>',
      '#include <beginnormal_vertex>\nobjectNormal = getNormal(position.xyz);'
    );

    // ── Fragment shader ──
    var fragHeader = '\nuniform float uNoiseIntensity;\nfloat random2(in vec2 st){return fract(sin(dot(st.xy,vec2(12.9898,78.233)))*43758.5453123);}\n';
    shader.fragmentShader = fragHeader + shader.fragmentShader;

    // Grain de bruit sur le fragment final
    shader.fragmentShader = shader.fragmentShader.replace(
      '#include <dithering_fragment>',
      '#include <dithering_fragment>\n\
float bRnd = random2(gl_FragCoord.xy);\n\
gl_FragColor.rgb -= bRnd / 15.0 * uNoiseIntensity;\n'
    );

    shaderRef = shader;
  };

  // ── Geometry (identique au composant React) ──
  function makeBeamsGeometry(n, width, height, segs) {
    var totalW = n * width + (n - 1) * 0;
    var xBase  = -totalW / 2;
    var numV   = n * (segs + 1) * 2;
    var numF   = n * segs * 2;
    var pos    = new Float32Array(numV * 3);
    var uvArr  = new Float32Array(numV * 2);
    var idx    = new Uint32Array(numF * 3);
    var vi = 0, ii = 0, ui = 0;

    for (var i = 0; i < n; i++) {
      var xOff   = xBase + i * width;
      var uvXOff = Math.random() * 300;
      var uvYOff = Math.random() * 300;
      for (var j = 0; j <= segs; j++) {
        var y   = height * (j / segs - 0.5);
        var uvY = j / segs;
        pos.set([xOff, y, 0, xOff + width, y, 0], vi * 3);
        uvArr.set([uvXOff, uvY + uvYOff, uvXOff + 1, uvY + uvYOff], ui);
        if (j < segs) {
          var a = vi, b = vi+1, c = vi+2, d = vi+3;
          idx.set([a,b,c, c,b,d], ii);
          ii += 6;
        }
        vi += 2; ui += 4;
      }
    }

    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('uv',       new THREE.BufferAttribute(uvArr, 2));
    geo.setIndex(new THREE.BufferAttribute(idx, 1));
    geo.computeVertexNormals();
    return geo;
  }

  var geo   = makeBeamsGeometry(BEAM_NUMBER, BEAM_WIDTH, BEAM_HEIGHT, 100);
  var mesh  = new THREE.Mesh(geo, material);
  var group = new THREE.Group();
  group.rotation.z = ROTATION_DEG * Math.PI / 180;
  group.add(mesh);
  scene.add(group);

  // ── Resize ───────────────────────────────────
  window.addEventListener('resize', function () {
    W = window.innerWidth; H = window.innerHeight;
    renderer.setSize(W, H);
    camera.aspect = W / H;
    camera.updateProjectionMatrix();
  });

  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) loop(performance.now());
  });

  // ── Render loop ──────────────────────────────
  var lastTs = 0;
  function loop(ts) {
    requestAnimationFrame(loop);
    var delta = Math.min((ts - lastTs) / 1000, 0.05);
    lastTs = ts;
    if (shaderRef) shaderRef.uniforms.time.value += 0.1 * delta;
    renderer.render(scene, camera);
  }
  loop(0);
})();
</script>

<script>
// ============================================
// LIQUID ETHER BACKGROUND
// ============================================
(function() {
  var container = document.getElementById('liquid-bg');
  if (!container || typeof THREE === 'undefined') return;

  var COLORS = ['#FFD600', '#FFF176', '#FFAB00'];
  var CONFIG = {
    mouseForce: 20, cursorSize: 100, isViscous: true, viscous: 30,
    iterationsViscous: 32, iterationsPoisson: 32, dt: 0.014, BFECC: true,
    resolution: 0.5, isBounce: false,
    autoDemo: true, autoSpeed: 0.5, autoIntensity: 2.2,
    takeoverDuration: 0.25, autoResumeDelay: 3000, autoRampDuration: 0.6
  };

  function makePaletteTexture(stops) {
    var arr = (stops && stops.length) ? (stops.length === 1 ? [stops[0], stops[0]] : stops) : ['#fff','#fff'];
    var w = arr.length;
    var data = new Uint8Array(w * 4);
    for (var i = 0; i < w; i++) {
      var c = new THREE.Color(arr[i]);
      data[i*4]   = Math.round(c.r*255);
      data[i*4+1] = Math.round(c.g*255);
      data[i*4+2] = Math.round(c.b*255);
      data[i*4+3] = 255;
    }
    var tex = new THREE.DataTexture(data, w, 1, THREE.RGBAFormat);
    tex.magFilter = THREE.LinearFilter;
    tex.minFilter = THREE.LinearFilter;
    tex.wrapS = THREE.ClampToEdgeWrapping;
    tex.wrapT = THREE.ClampToEdgeWrapping;
    tex.generateMipmaps = false;
    tex.needsUpdate = true;
    return tex;
  }

  var paletteTex = makePaletteTexture(COLORS);
  var bgVec4 = new THREE.Vector4(0,0,0,0);

  // ── Shaders ──
  var face_vert = 'attribute vec3 position;uniform vec2 px;uniform vec2 boundarySpace;varying vec2 uv;precision highp float;void main(){vec3 pos=position;vec2 scale=1.0-boundarySpace*2.0;pos.xy=pos.xy*scale;uv=vec2(0.5)+(pos.xy)*0.5;gl_Position=vec4(pos,1.0);}';
  var line_vert = 'attribute vec3 position;uniform vec2 px;precision highp float;varying vec2 uv;void main(){vec3 pos=position;uv=0.5+pos.xy*0.5;vec2 n=sign(pos.xy);pos.xy=abs(pos.xy)-px*1.0;pos.xy*=n;gl_Position=vec4(pos,1.0);}';
  var mouse_vert = 'precision highp float;attribute vec3 position;attribute vec2 uv;uniform vec2 center;uniform vec2 scale;uniform vec2 px;varying vec2 vUv;void main(){vec2 pos=position.xy*scale*2.0*px+center;vUv=uv;gl_Position=vec4(pos,0.0,1.0);}';
  var advection_frag = 'precision highp float;uniform sampler2D velocity;uniform float dt;uniform bool isBFECC;uniform vec2 fboSize;uniform vec2 px;varying vec2 uv;void main(){vec2 ratio=max(fboSize.x,fboSize.y)/fboSize;if(isBFECC==false){vec2 vel=texture2D(velocity,uv).xy;vec2 uv2=uv-vel*dt*ratio;vec2 newVel=texture2D(velocity,uv2).xy;gl_FragColor=vec4(newVel,0.0,0.0);}else{vec2 spot_new=uv;vec2 vel_old=texture2D(velocity,uv).xy;vec2 spot_old=spot_new-vel_old*dt*ratio;vec2 vel_new1=texture2D(velocity,spot_old).xy;vec2 spot_new2=spot_old+vel_new1*dt*ratio;vec2 error=spot_new2-spot_new;vec2 spot_new3=spot_new-error/2.0;vec2 vel_2=texture2D(velocity,spot_new3).xy;vec2 spot_old2=spot_new3-vel_2*dt*ratio;vec2 newVel2=texture2D(velocity,spot_old2).xy;gl_FragColor=vec4(newVel2,0.0,0.0);}}';
  var color_frag = 'precision highp float;uniform sampler2D velocity;uniform sampler2D palette;uniform vec4 bgColor;varying vec2 uv;void main(){vec2 vel=texture2D(velocity,uv).xy;float lenv=clamp(length(vel),0.0,1.0);vec3 c=texture2D(palette,vec2(lenv,0.5)).rgb;vec3 outRGB=mix(bgColor.rgb,c,lenv);float outA=mix(bgColor.a,1.0,lenv);gl_FragColor=vec4(outRGB,outA);}';
  var divergence_frag = 'precision highp float;uniform sampler2D velocity;uniform float dt;uniform vec2 px;varying vec2 uv;void main(){float x0=texture2D(velocity,uv-vec2(px.x,0.0)).x;float x1=texture2D(velocity,uv+vec2(px.x,0.0)).x;float y0=texture2D(velocity,uv-vec2(0.0,px.y)).y;float y1=texture2D(velocity,uv+vec2(0.0,px.y)).y;float divergence=(x1-x0+y1-y0)/2.0;gl_FragColor=vec4(divergence/dt);}';
  var externalForce_frag = 'precision highp float;uniform vec2 force;uniform vec2 center;uniform vec2 scale;uniform vec2 px;varying vec2 vUv;void main(){vec2 circle=(vUv-0.5)*2.0;float d=1.0-min(length(circle),1.0);d*=d;gl_FragColor=vec4(force*d,0.0,1.0);}';
  var poisson_frag = 'precision highp float;uniform sampler2D pressure;uniform sampler2D divergence;uniform vec2 px;varying vec2 uv;void main(){float p0=texture2D(pressure,uv+vec2(px.x*2.0,0.0)).r;float p1=texture2D(pressure,uv-vec2(px.x*2.0,0.0)).r;float p2=texture2D(pressure,uv+vec2(0.0,px.y*2.0)).r;float p3=texture2D(pressure,uv-vec2(0.0,px.y*2.0)).r;float div=texture2D(divergence,uv).r;float newP=(p0+p1+p2+p3)/4.0-div;gl_FragColor=vec4(newP);}';
  var pressure_frag = 'precision highp float;uniform sampler2D pressure;uniform sampler2D velocity;uniform vec2 px;uniform float dt;varying vec2 uv;void main(){float p0=texture2D(pressure,uv+vec2(px.x,0.0)).r;float p1=texture2D(pressure,uv-vec2(px.x,0.0)).r;float p2=texture2D(pressure,uv+vec2(0.0,px.y)).r;float p3=texture2D(pressure,uv-vec2(0.0,px.y)).r;vec2 v=texture2D(velocity,uv).xy;vec2 gradP=vec2(p0-p1,p2-p3)*0.5;v=v-gradP*dt;gl_FragColor=vec4(v,0.0,1.0);}';
  var viscous_frag = 'precision highp float;uniform sampler2D velocity;uniform sampler2D velocity_new;uniform float v;uniform vec2 px;uniform float dt;varying vec2 uv;void main(){vec2 old=texture2D(velocity,uv).xy;vec2 new0=texture2D(velocity_new,uv+vec2(px.x*2.0,0.0)).xy;vec2 new1=texture2D(velocity_new,uv-vec2(px.x*2.0,0.0)).xy;vec2 new2=texture2D(velocity_new,uv+vec2(0.0,px.y*2.0)).xy;vec2 new3=texture2D(velocity_new,uv-vec2(0.0,px.y*2.0)).xy;vec2 newv=4.0*old+v*dt*(new0+new1+new2+new3);newv/=4.0*(1.0+v*dt);gl_FragColor=vec4(newv,0.0,0.0);}';

  // ── Common ──
  var Common = {
    width:0, height:0, renderer:null, clock:null, time:0, delta:0,
    init: function(c) {
      this.container = c;
      this.resize();
      this.renderer = new THREE.WebGLRenderer({ antialias:true, alpha:true });
      this.renderer.autoClear = false;
      this.renderer.setClearColor(new THREE.Color(0x000000), 0);
      this.renderer.setPixelRatio(Math.min(window.devicePixelRatio||1,2));
      this.renderer.setSize(this.width, this.height);
      this.renderer.domElement.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;display:block;';
      this.clock = new THREE.Clock(); this.clock.start();
    },
    resize: function() {
      if (!this.container) return;
      var r = this.container.getBoundingClientRect();
      this.width  = Math.max(1, Math.floor(r.width));
      this.height = Math.max(1, Math.floor(r.height));
      if (this.renderer) this.renderer.setSize(this.width, this.height, false);
    },
    update: function() { this.delta = this.clock.getDelta(); this.time += this.delta; }
  };

  // ── Mouse ──
  var Mouse = {
    mouseMoved:false, coords:new THREE.Vector2(), coords_old:new THREE.Vector2(),
    diff:new THREE.Vector2(), timer:null, isAutoActive:false, autoIntensity:2.0,
    hasUserControl:false, takeoverActive:false, takeoverStartTime:0,
    takeoverDuration:0.25, takeoverFrom:new THREE.Vector2(), takeoverTo:new THREE.Vector2(),
    onInteract:null,
    init: function() {
      var self = this;
      window.addEventListener('mousemove', function(e) {
        var r = container.getBoundingClientRect();
        var nx = (e.clientX - r.left) / r.width;
        var ny = (e.clientY - r.top)  / r.height;
        if (self.onInteract) self.onInteract();
        if (self.isAutoActive && !self.hasUserControl && !self.takeoverActive) {
          self.takeoverFrom.copy(self.coords);
          self.takeoverTo.set(nx*2-1, -(ny*2-1));
          self.takeoverStartTime = performance.now();
          self.takeoverActive = true;
          self.hasUserControl = true;
          self.isAutoActive = false;
          return;
        }
        self.coords.set(nx*2-1, -(ny*2-1));
        self.hasUserControl = true;
      });
      window.addEventListener('touchmove', function(e) {
        if (e.touches.length !== 1) return;
        var t = e.touches[0];
        var r = container.getBoundingClientRect();
        self.coords.set((t.clientX-r.left)/r.width*2-1, -((t.clientY-r.top)/r.height*2-1));
        if (self.onInteract) self.onInteract();
        self.hasUserControl = true;
      }, { passive:true });
    },
    setNormalized: function(nx, ny) { this.coords.set(nx, ny); },
    update: function() {
      if (this.takeoverActive) {
        var t = (performance.now() - this.takeoverStartTime) / (this.takeoverDuration * 1000);
        if (t >= 1) {
          this.takeoverActive = false;
          this.coords.copy(this.takeoverTo);
          this.coords_old.copy(this.coords);
          this.diff.set(0,0);
        } else {
          var k = t*t*(3-2*t);
          this.coords.copy(this.takeoverFrom).lerp(this.takeoverTo, k);
        }
      }
      this.diff.subVectors(this.coords, this.coords_old);
      this.coords_old.copy(this.coords);
      if (this.coords_old.x===0 && this.coords_old.y===0) this.diff.set(0,0);
      if (this.isAutoActive && !this.takeoverActive) this.diff.multiplyScalar(this.autoIntensity);
    }
  };

  // ── AutoDriver ──
  function AutoDriver(opts) {
    this.enabled = opts.enabled;
    this.speed = opts.speed;
    this.resumeDelay = opts.resumeDelay || 3000;
    this.rampDurationMs = (opts.rampDuration || 0) * 1000;
    this.active = false;
    this.current = new THREE.Vector2(0,0);
    this.target  = new THREE.Vector2();
    this.lastTime = performance.now();
    this.activationTime = 0;
    this.lastUserInteraction = performance.now();
    this._tmpDir = new THREE.Vector2();
    this.pickNewTarget();
    var self = this;
    Mouse.onInteract = function() { self.lastUserInteraction = performance.now(); self.forceStop(); };
  }
  AutoDriver.prototype.pickNewTarget = function() {
    var m = 0.2;
    this.target.set((Math.random()*2-1)*(1-m), (Math.random()*2-1)*(1-m));
  };
  AutoDriver.prototype.forceStop = function() { this.active = false; Mouse.isAutoActive = false; };
  AutoDriver.prototype.update = function() {
    if (!this.enabled) return;
    var now = performance.now();
    if (now - this.lastUserInteraction < this.resumeDelay) { if (this.active) this.forceStop(); return; }
    if (!this.active) { this.active = true; this.current.copy(Mouse.coords); this.lastTime = now; this.activationTime = now; }
    Mouse.isAutoActive = true;
    var dtSec = Math.min((now - this.lastTime) / 1000, 0.05);
    this.lastTime = now;
    var dir = this._tmpDir.subVectors(this.target, this.current);
    var dist = dir.length();
    if (dist < 0.01) { this.pickNewTarget(); return; }
    dir.normalize();
    var ramp = 1;
    if (this.rampDurationMs > 0) { var tr = Math.min(1,(now-this.activationTime)/this.rampDurationMs); ramp=tr*tr*(3-2*tr); }
    var move = Math.min(this.speed * dtSec * ramp, dist);
    this.current.addScaledVector(dir, move);
    Mouse.setNormalized(this.current.x, this.current.y);
  };

  // ── ShaderPass ──
  function ShaderPass(props) {
    this.props = props || {};
    this.uniforms = this.props.material && this.props.material.uniforms;
    this.scene = new THREE.Scene();
    this.camera = new THREE.Camera();
    if (this.uniforms) {
      this.material = new THREE.RawShaderMaterial(this.props.material);
      var PlaneGeo = THREE.PlaneGeometry || THREE.PlaneBufferGeometry;
      this.geometry = new PlaneGeo(2.0, 2.0);
      this.plane = new THREE.Mesh(this.geometry, this.material);
      this.scene.add(this.plane);
    }
  }
  ShaderPass.prototype.update = function() {
    Common.renderer.setRenderTarget(this.props.output || null);
    Common.renderer.render(this.scene, this.camera);
    Common.renderer.setRenderTarget(null);
  };

  function createFBOs() {
    var isIOS = /(iPad|iPhone|iPod)/i.test(navigator.userAgent);
    var type = isIOS ? THREE.HalfFloatType : THREE.FloatType;
    var opts = { type:type, depthBuffer:false, stencilBuffer:false, minFilter:THREE.LinearFilter, magFilter:THREE.LinearFilter, wrapS:THREE.ClampToEdgeWrapping, wrapT:THREE.ClampToEdgeWrapping };
    var fboSize = new THREE.Vector2(Math.max(1,Math.round(CONFIG.resolution*Common.width)), Math.max(1,Math.round(CONFIG.resolution*Common.height)));
    var cellScale = new THREE.Vector2(1/fboSize.x, 1/fboSize.y);
    var fbos = {};
    ['vel_0','vel_1','vel_viscous0','vel_viscous1','div','pressure_0','pressure_1'].forEach(function(k) {
      fbos[k] = new THREE.WebGLRenderTarget(fboSize.x, fboSize.y, opts);
    });
    return { fbos:fbos, fboSize:fboSize, cellScale:cellScale, boundarySpace: new THREE.Vector2() };
  }

  // ── Init ──
  Common.init(container);
  container.appendChild(Common.renderer.domElement);
  Mouse.init();
  Mouse.autoIntensity = CONFIG.autoIntensity;
  Mouse.takeoverDuration = CONFIG.takeoverDuration;

  var autoDriver = new AutoDriver({
    enabled: CONFIG.autoDemo, speed: CONFIG.autoSpeed,
    resumeDelay: CONFIG.autoResumeDelay, rampDuration: CONFIG.autoRampDuration
  });

  var sim = createFBOs();
  var fbos = sim.fbos, fboSize = sim.fboSize, cellScale = sim.cellScale, boundarySpace = sim.boundarySpace;

  // Advection
  var advectionPass = new ShaderPass({ material:{ vertexShader:face_vert, fragmentShader:advection_frag, uniforms:{ boundarySpace:{value:cellScale}, px:{value:cellScale}, fboSize:{value:fboSize}, velocity:{value:fbos.vel_0.texture}, dt:{value:CONFIG.dt}, isBFECC:{value:true} } }, output:fbos.vel_1 });
  var bndG = new THREE.BufferGeometry();
  bndG.setAttribute('position', new THREE.BufferAttribute(new Float32Array([-1,-1,0,-1,1,0,-1,1,0,1,1,0,1,1,0,1,-1,0,1,-1,0,-1,-1,0]),3));
  var bndL = new THREE.LineSegments(bndG, new THREE.RawShaderMaterial({ vertexShader:line_vert, fragmentShader:advection_frag, uniforms:advectionPass.uniforms }));
  advectionPass.scene.add(bndL);

  // External Force
  var forcePass = new ShaderPass({ output:fbos.vel_1 });
  var PlaneGeo = THREE.PlaneGeometry || THREE.PlaneBufferGeometry;
  var mouseM = new THREE.RawShaderMaterial({ vertexShader:mouse_vert, fragmentShader:externalForce_frag, blending:THREE.AdditiveBlending, depthWrite:false, uniforms:{ px:{value:cellScale}, force:{value:new THREE.Vector2()}, center:{value:new THREE.Vector2()}, scale:{value:new THREE.Vector2(CONFIG.cursorSize,CONFIG.cursorSize)} } });
  var mouseMesh = new THREE.Mesh(new PlaneGeo(1,1), mouseM);
  forcePass.scene.add(mouseMesh);

  // Viscous
  var viscousPass = new ShaderPass({ material:{ vertexShader:face_vert, fragmentShader:viscous_frag, uniforms:{ boundarySpace:{value:boundarySpace}, velocity:{value:fbos.vel_1.texture}, velocity_new:{value:fbos.vel_viscous0.texture}, v:{value:CONFIG.viscous}, px:{value:cellScale}, dt:{value:CONFIG.dt} } }, output:fbos.vel_viscous1, output0:fbos.vel_viscous0, output1:fbos.vel_viscous1 });

  // Divergence
  var divPass = new ShaderPass({ material:{ vertexShader:face_vert, fragmentShader:divergence_frag, uniforms:{ boundarySpace:{value:boundarySpace}, velocity:{value:fbos.vel_1.texture}, px:{value:cellScale}, dt:{value:CONFIG.dt} } }, output:fbos.div });

  // Poisson
  var poissonPass = new ShaderPass({ material:{ vertexShader:face_vert, fragmentShader:poisson_frag, uniforms:{ boundarySpace:{value:boundarySpace}, pressure:{value:fbos.pressure_0.texture}, divergence:{value:fbos.div.texture}, px:{value:cellScale} } }, output:fbos.pressure_1, output0:fbos.pressure_0, output1:fbos.pressure_1 });

  // Pressure
  var pressurePass = new ShaderPass({ material:{ vertexShader:face_vert, fragmentShader:pressure_frag, uniforms:{ boundarySpace:{value:boundarySpace}, pressure:{value:fbos.pressure_0.texture}, velocity:{value:fbos.vel_viscous0.texture}, px:{value:cellScale}, dt:{value:CONFIG.dt} } }, output:fbos.vel_0 });

  // Output render
  var outputScene  = new THREE.Scene();
  var outputCamera = new THREE.Camera();
  var outputMesh   = new THREE.Mesh(new PlaneGeo(2,2), new THREE.RawShaderMaterial({ vertexShader:face_vert, fragmentShader:color_frag, transparent:true, depthWrite:false, uniforms:{ velocity:{value:fbos.vel_0.texture}, boundarySpace:{value:new THREE.Vector2()}, palette:{value:paletteTex}, bgColor:{value:bgVec4} } }));
  outputScene.add(outputMesh);

  function simUpdate() {
    // Advection
    advectionPass.uniforms.dt.value = CONFIG.dt;
    advectionPass.uniforms.isBFECC.value = CONFIG.BFECC;
    bndL.visible = CONFIG.isBounce;
    advectionPass.update();

    // External force
    var fx = (Mouse.diff.x/2) * CONFIG.mouseForce;
    var fy = (Mouse.diff.y/2) * CONFIG.mouseForce;
    var csx = CONFIG.cursorSize * cellScale.x;
    var csy = CONFIG.cursorSize * cellScale.y;
    mouseM.uniforms.force.value.set(fx, fy);
    mouseM.uniforms.center.value.set(
      Math.min(Math.max(Mouse.coords.x, -1+csx+cellScale.x*2), 1-csx-cellScale.x*2),
      Math.min(Math.max(Mouse.coords.y, -1+csy+cellScale.y*2), 1-csy-cellScale.y*2)
    );
    forcePass.update();

    // Viscous
    var vel = fbos.vel_1;
    if (CONFIG.isViscous) {
      viscousPass.uniforms.v.value = CONFIG.viscous;
      for (var vi = 0; vi < CONFIG.iterationsViscous; vi++) {
        var vo_in  = vi%2===0 ? viscousPass.props.output0 : viscousPass.props.output1;
        var vo_out = vi%2===0 ? viscousPass.props.output1 : viscousPass.props.output0;
        viscousPass.uniforms.velocity_new.value = vo_in.texture;
        viscousPass.props.output = vo_out;
        viscousPass.update();
      }
      vel = viscousPass.props.output;
    }

    // Divergence
    divPass.uniforms.velocity.value = vel.texture;
    divPass.update();

    // Poisson
    var pressure = fbos.pressure_0;
    for (var pi = 0; pi < CONFIG.iterationsPoisson; pi++) {
      var p_in  = pi%2===0 ? poissonPass.props.output0 : poissonPass.props.output1;
      var p_out = pi%2===0 ? poissonPass.props.output1 : poissonPass.props.output0;
      poissonPass.uniforms.pressure.value = p_in.texture;
      poissonPass.props.output = p_out;
      poissonPass.update();
      pressure = p_out;
    }

    // Pressure
    pressurePass.uniforms.velocity.value = vel.texture;
    pressurePass.uniforms.pressure.value = pressure.texture;
    pressurePass.update();

    // Render output
    Common.renderer.setRenderTarget(null);
    Common.renderer.render(outputScene, outputCamera);
  }

  var rafId = null;
  var running = false;
  function loop() {
    if (!running) return;
    autoDriver.update();
    Mouse.update();
    Common.update();
    simUpdate();
    rafId = requestAnimationFrame(loop);
  }
  function start() { if (running) return; running=true; loop(); }
  function pause() { running=false; if(rafId){cancelAnimationFrame(rafId);rafId=null;} }

  window.addEventListener('resize', function() { Common.resize(); });
  document.addEventListener('visibilitychange', function() { document.hidden ? pause() : start(); });

  start();
})();

// ============================================
// MENU MOBILE
// ============================================
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');
const nav       = document.getElementById('nav');

navToggle.addEventListener('click', function() {
    navLinks.classList.toggle('open');
    navToggle.classList.toggle('active');
});
window.addEventListener('scroll', function() {
    nav.classList.toggle('scrolled', window.scrollY > 60);
}, { passive: true });

if (document.querySelector('.page-section')) gsap.fromTo('.page-section',
    { y: 30, opacity: 0 },
    { y: 0, opacity: 1, duration: 0.7, ease: 'power2.out' }
);

</script>

</body>
</html>
