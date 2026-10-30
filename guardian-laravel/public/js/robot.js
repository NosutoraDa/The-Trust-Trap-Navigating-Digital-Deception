/*
 * Shared Three.js background and SVG mascot presentation for Guardian.
 */
(function () {
  const STATES = {
    idle: {
      label: 'Mode Siaga (Standby)',
      glow: 'glow-blue',
      color: '#00E5FF',
      mouth: 'M 82 116 Q 100 130 118 116',
      badge: 'state-idle',
    },
    scanning: {
      label: 'Sedang menganalisis...',
      glow: 'glow-amber',
      color: '#FBBF24',
      mouth: 'M 82 120 Q 100 120 118 120',
      badge: 'state-scanning',
    },
    danger: {
      label: 'Waspada: indikator terdeteksi',
      glow: 'glow-red',
      color: '#EF4444',
      mouth: 'M 82 124 Q 100 106 118 124',
      badge: 'state-danger',
    },
    safe: {
      label: 'Pemeriksaan selesai',
      glow: 'glow-green',
      color: '#10B981',
      mouth: 'M 80 114 Q 100 134 120 114',
      badge: 'state-safe',
    },
  };

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let pulseTimeout = null;
  let radialMenuOpen = false;
  let threeScriptLoading = false;
  let scene;
  let camera;
  let renderer;
  let terrain;
  let terrainGeometry;
  let starField;
  let raycaster;
  let pointerPlane;
  let mouseNdc;
  let mouseWorld;
  let animationFrame = null;
  let lastFrameTime = 0;
  let isVisible = !document.hidden;

  function setRadialMenu(open) {
    radialMenuOpen = open;
    const dial = document.getElementById('radial-dial-container');
    const track = document.getElementById('arc-track-svg');
    const radialButton = document.getElementById('toggle-radial-btn');
    const mascotButton = document.getElementById('mascot-toggle-menu');

    if (dial) {
      dial.classList.toggle('arc-menu-active', open);
      dial.setAttribute('aria-hidden', String(!open));
    }
    if (track) track.classList.toggle('arc-track-active', open);
    [radialButton, mascotButton].forEach(button => {
      if (button) button.setAttribute('aria-expanded', String(open));
    });
    document.querySelectorAll('.arc-item').forEach(item => {
      item.tabIndex = open ? 0 : -1;
    });

    return radialMenuOpen;
  }

  function toggleRadialMenu() {
    const open = setRadialMenu(!radialMenuOpen);
    setState('idle', open
      ? 'Pilih salah satu menu inspeksi di sekeliling saya.'
      : 'Saya Si Guardian, siap membantu menjaga keamanan digitalmu.');
  }

  function toggleQuickTips(forceOpen) {
    const panel = document.getElementById('quick-tips-panel');
    const button = document.getElementById('toggle-quick-tips-btn');
    if (!panel) return;

    const open = typeof forceOpen === 'boolean' ? forceOpen : panel.hidden;
    panel.hidden = !open;
    if (button) button.setAttribute('aria-expanded', String(open));
  }

  function setState(name, message) {
    const stateName = Object.prototype.hasOwnProperty.call(STATES, name) ? name : 'idle';
    const state = STATES[stateName];
    const speech = document.getElementById('speech');
    const speechText = document.getElementById('speech-text');
    const status = document.querySelector('.status-badge');
    const statusText = document.getElementById('status-text');
    const aura = document.getElementById('robot-glow-aura');
    const eyes = [
      document.getElementById('left-eye'),
      document.getElementById('right-eye'),
    ];
    const mouth = document.getElementById('smooth-mouth');

    if (speech) speech.className = 'speech ' + state.glow;
    if (message && speechText) speechText.textContent = message;
    if (status) status.className = 'status-badge ' + state.badge;
    if (statusText) statusText.textContent = state.label;
    if (aura) aura.className = 'robot-glow ' + state.badge;

    eyes.forEach(eye => {
      if (eye) eye.setAttribute('fill', state.color);
    });

    if (mouth) {
      mouth.setAttribute('d', state.mouth);
      mouth.setAttribute('stroke', state.color);
    }
  }

  function pulse() {
    const mascot = document.getElementById('robot-svg');
    if (!mascot) return;

    mascot.classList.remove('robot-pulsing');
    void mascot.getBoundingClientRect();
    mascot.classList.add('robot-pulsing');

    if (pulseTimeout !== null) window.clearTimeout(pulseTimeout);
    pulseTimeout = window.setTimeout(() => {
      mascot.classList.remove('robot-pulsing');
      pulseTimeout = null;
    }, 1500);
  }

  function updatePointer(event) {
    if (!camera || !raycaster || !mouseNdc || !mouseWorld) return;
    mouseNdc.set(
      (event.clientX / window.innerWidth) * 2 - 1,
      -(event.clientY / window.innerHeight) * 2 + 1
    );
    raycaster.setFromCamera(mouseNdc, camera);
    raycaster.ray.intersectPlane(pointerPlane, mouseWorld);

    if (terrain) {
      terrain.material.uniforms.uMouse.value.copy(mouseWorld);
      terrain.material.uniforms.uMouseActive.value = 1;
    }

    if (reducedMotion) renderScene(performance.now());
  }

  function createTerrain(THREE) {
    const columns = 128;
    const rows = 118;
    const spacing = 0.45;
    const positions = new Float32Array(columns * rows * 3);
    const offsetX = columns * spacing / 2;
    const offsetY = rows * spacing / 2;

    for (let row = 0; row < rows; row++) {
      for (let column = 0; column < columns; column++) {
        const index = (row * columns + column) * 3;
        positions[index] = column * spacing - offsetX;
        positions[index + 1] = row * spacing - offsetY;
        positions[index + 2] = 0;
      }
    }

    terrainGeometry = new THREE.BufferGeometry();
    terrainGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    terrainGeometry.computeBoundingSphere();

    const material = new THREE.ShaderMaterial({
      uniforms: {
        uTime: { value: 0 },
        uMouse: { value: new THREE.Vector2(-1000, -1000) },
        uMouseActive: { value: 0 },
        uPixelRatio: { value: renderer.getPixelRatio() },
      },
      vertexShader: `
        uniform float uTime;
        uniform vec2 uMouse;
        uniform float uMouseActive;
        uniform float uPixelRatio;
        varying float vBrightness;

        void main() {
          vec3 point = position;
          float distanceToMouse = distance(point.xy, uMouse);
          float ripple = 0.0;

          if (uMouseActive > 0.5 && distanceToMouse < 2.2) {
            float influence = 0.5 * (1.0 + cos((distanceToMouse / 2.2) * 3.14159265));
            ripple = influence * 0.9;
          }

          float wave = sin(point.x * 0.18 + uTime * 0.75)
            * cos(point.y * 0.16 - uTime * 0.55) * 0.12;
          point.z += ripple + wave;
          vBrightness = 0.68 + ripple * 0.3 + wave * 0.45;

          vec4 viewPosition = modelViewMatrix * vec4(point, 1.0);
          gl_Position = projectionMatrix * viewPosition;
          gl_PointSize = clamp(2.5 * uPixelRatio * (7.0 / -viewPosition.z), 1.0, 6.0);
        }
      `,
      fragmentShader: `
        varying float vBrightness;

        void main() {
          vec2 centeredPoint = gl_PointCoord - vec2(0.5);
          float pointDistance = length(centeredPoint);
          float core = 1.0 - smoothstep(0.12, 0.5, pointDistance);
          float halo = 1.0 - smoothstep(0.15, 0.72, pointDistance);
          float alpha = (core * 0.72 + halo * 0.28) * vBrightness;
          if (alpha < 0.025) discard;
          gl_FragColor = vec4(0.22, 0.72, 1.0, alpha);
        }
      `,
      transparent: true,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    });

    terrain = new THREE.Points(terrainGeometry, material);
    terrain.frustumCulled = false;
    scene.add(terrain);
  }

  function createStarField(THREE) {
    const count = 320;
    const positions = new Float32Array(count * 3);
    for (let index = 0; index < count; index++) {
      positions[index * 3] = (Math.random() - 0.5) * 26;
      positions[index * 3 + 1] = (Math.random() - 0.5) * 18;
      positions[index * 3 + 2] = -1.5 - Math.random() * 8;
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const material = new THREE.PointsMaterial({
      color: 0x38bdf8,
      size: 0.035,
      transparent: true,
      opacity: 0.42,
      sizeAttenuation: true,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    });

    starField = new THREE.Points(geometry, material);
    starField.frustumCulled = false;
    scene.add(starField);
  }

  function resizeScene() {
    if (!camera || !renderer) return;
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(1);
    if (terrain) terrain.material.uniforms.uPixelRatio.value = renderer.getPixelRatio();
    renderScene(performance.now());
  }

  function renderScene(time) {
    if (!renderer || !scene || !camera) return;

    if (terrain) terrain.material.uniforms.uTime.value = time * 0.001;
    if (starField && !reducedMotion) starField.rotation.z = Math.sin(time * 0.00008) * 0.025;
    renderer.render(scene, camera);
  }

  function animateScene(time) {
    animationFrame = null;
    if (!renderer || !isVisible || document.hidden) return;

    if (time - lastFrameTime >= 33) {
      lastFrameTime = time;
      renderScene(time);
    }

    if (!reducedMotion) animationFrame = window.requestAnimationFrame(animateScene);
  }

  function startScene() {
    if (animationFrame !== null || !renderer || !isVisible || document.hidden) return;
    if (reducedMotion) {
      renderScene(performance.now());
      return;
    }
    animationFrame = window.requestAnimationFrame(animateScene);
  }

  function stopScene() {
    if (animationFrame === null) return;
    window.cancelAnimationFrame(animationFrame);
    animationFrame = null;
  }

  function initializeThreeScene() {
    const container = document.getElementById('canvas-container');
    const THREE = window.THREE;
    if (!container || !THREE) {
      console.error('Guardian 3D background could not initialize because its canvas or Three.js library is unavailable.');
      return;
    }

    try {
      scene = new THREE.Scene();
      camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 1000);
      camera.position.set(0, 0, 7);
      renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: false,
        powerPreference: 'low-power',
      });
      renderer.setSize(window.innerWidth, window.innerHeight);
      renderer.setPixelRatio(1);
      renderer.setClearColor(0x030712, 0);
      container.replaceChildren(renderer.domElement);

      raycaster = new THREE.Raycaster();
      pointerPlane = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0);
      mouseNdc = new THREE.Vector2(-9999, -9999);
      mouseWorld = new THREE.Vector3(-1000, -1000, 0);

      createTerrain(THREE);
      createStarField(THREE);
      window.addEventListener('resize', resizeScene, { passive: true });
      window.addEventListener('pointermove', updatePointer, { passive: true });
      window.addEventListener('pointerleave', () => {
        if (terrain) terrain.material.uniforms.uMouseActive.value = 0;
      }, { passive: true });
      document.addEventListener('visibilitychange', () => {
        isVisible = !document.hidden;
        if (isVisible) startScene();
        else stopScene();
      });

      renderScene(0);
      startScene();
    } catch (error) {
      console.error('Guardian 3D background could not initialize.', error);
    }
  }

  function loadThreeScene() {
    if (window.THREE) {
      initializeThreeScene();
      return;
    }
    if (threeScriptLoading) return;
    threeScriptLoading = true;

    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
    script.async = true;
    script.onload = initializeThreeScene;
    script.onerror = () => {
      console.error('Guardian 3D background could not load the Three.js library.');
    };
    document.head.appendChild(script);
  }

  document.getElementById('toggle-radial-btn')?.addEventListener('click', toggleRadialMenu);
  document.getElementById('mascot-toggle-menu')?.addEventListener('click', toggleRadialMenu);
  document.getElementById('toggle-quick-tips-btn')?.addEventListener('click', () => toggleQuickTips());
  document.getElementById('close-quick-tips-btn')?.addEventListener('click', () => toggleQuickTips(false));

  setRadialMenu(false);
  window.requestAnimationFrame(() => {
    window.requestAnimationFrame(() => setRadialMenu(true));
  });
  loadThreeScene();

  window.Robot = { setState, pulse, toggleMenu: toggleRadialMenu };
})();
