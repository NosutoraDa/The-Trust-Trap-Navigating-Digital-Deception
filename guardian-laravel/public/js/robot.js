/*
 * Guardian robot - shared 3D avatar for every page.
 * Requires three.js to be loaded before this file.
 *
 * Each page sets its animation with <body data-robot="...">:
 *   scan   - head sweeps side to side (Cek)
 *   study  - slow nod while reading (Belajar)
 *   alert  - pulsing ring, fast halo (Bertindak)
 *   chat   - flickering eyes when Robot.pulse() is called (Tanya AI)
 *
 * Page API:
 *   Robot.setState('idle' | 'scanning' | 'danger' | 'safe', 'message')
 *   Robot.pulse()   // short "typing" burst, used by chat
 */
(function () {
  if (typeof THREE === 'undefined') return;

  const COLORS = { idle: 0x3b82f6, scanning: 0xf59e0b, danger: 0xef4444, safe: 0x10b981 };
  const GLOW = { idle: 'glow-blue', scanning: 'glow-amber', danger: 'glow-red', safe: 'glow-green' };
  const STATUS = { idle: 'Mode siaga', scanning: 'Sedang menganalisis...', danger: 'Awas, bahaya terdeteksi!', safe: 'Tampak aman' };
  const mode = (document.body && document.body.dataset.robot) || 'idle';

  const clock = new THREE.Clock();
  let state = 'idle';
  let mouseX = 0, mouseY = 0;
  let blinkT = 0;
  let pulseUntil = 0;

  let camera, renderer, robot, eyeL, eyeR, ring, halo, box, eyeMat, ringMat;

  function build() {
    box = document.getElementById('avatar');
    if (!box) return;
    const w = box.clientWidth, h = box.clientHeight;

    const scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(40, w / h, 0.1, 1000);
    camera.position.set(0, 0.2, 5.2);

    renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(w, h);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    box.appendChild(renderer.domElement);

    scene.add(new THREE.AmbientLight(0xffffff, 0.9));
    const key = new THREE.DirectionalLight(0x3b82f6, 1.4); key.position.set(3, 4, 5); scene.add(key);
    const rim = new THREE.DirectionalLight(0x6366f1, 1.0); rim.position.set(-3, 2, -2); scene.add(rim);

    robot = new THREE.Group();
    const bodyMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3, metalness: 0.8 });
    const chromeMat = new THREE.MeshStandardMaterial({ color: 0x64748b, roughness: 0.2, metalness: 0.9 });
    const visorMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.1, metalness: 0.9 });
    eyeMat = new THREE.MeshBasicMaterial({ color: COLORS[state] });
    ringMat = new THREE.MeshBasicMaterial({ color: COLORS[state], transparent: true, opacity: 0.6 });

    // Head
    const headGeo = new THREE.SphereGeometry(1.0, 32, 32);
    headGeo.scale(1.0, 1.05, 0.95);
    robot.add(new THREE.Mesh(headGeo, bodyMat));

    // Visor
    const visorGeo = new THREE.SphereGeometry(0.85, 32, 32, 0, Math.PI * 2, 0, Math.PI * 0.45);
    const visor = new THREE.Mesh(visorGeo, visorMat);
    visor.rotation.x = Math.PI / 2.2;
    visor.position.set(0, 0.05, 0.22);
    robot.add(visor);

    // Eyes
    const eyeGeo = new THREE.SphereGeometry(0.12, 32, 32);
    eyeGeo.scale(1, 1, 0.4);
    eyeL = new THREE.Mesh(eyeGeo, eyeMat); eyeL.position.set(-0.35, 0.08, 0.92);
    eyeR = new THREE.Mesh(eyeGeo, eyeMat); eyeR.position.set(0.35, 0.08, 0.92);
    robot.add(eyeL, eyeR);

    // Ear knobs
    const earGeo = new THREE.CylinderGeometry(0.22, 0.22, 0.3, 32);
    earGeo.rotateZ(Math.PI / 2);
    const earL = new THREE.Mesh(earGeo, chromeMat); earL.position.set(-1.05, 0.05, 0);
    const earR = new THREE.Mesh(earGeo, chromeMat); earR.position.set(1.05, 0.05, 0);
    robot.add(earL, earR);

    // Rings
    ring = new THREE.Mesh(new THREE.TorusGeometry(1.25, 0.03, 16, 64), ringMat);
    ring.rotation.x = Math.PI / 2.5;
    ring.position.y = -0.5;
    robot.add(ring);

    halo = new THREE.Mesh(new THREE.TorusGeometry(0.45, 0.02, 16, 32), ringMat);
    halo.rotation.x = Math.PI / 2;
    halo.position.y = 1.35;
    robot.add(halo);

    scene.add(robot);

    window.addEventListener('mousemove', e => {
      mouseX = (e.clientX - window.innerWidth / 2) / (window.innerWidth / 2);
      mouseY = (e.clientY - window.innerHeight / 2) / (window.innerHeight / 2);
    });
    window.addEventListener('resize', () => {
      const nw = box.clientWidth, nh = box.clientHeight;
      camera.aspect = nw / nh;
      camera.updateProjectionMatrix();
      renderer.setSize(nw, nh);
    });

    loop(scene);
  }

  function loop(scene) {
    requestAnimationFrame(() => loop(scene));
    const t = clock.getElapsedTime();

    // Base: follow mouse + gentle float
    robot.rotation.y += (mouseX * 0.4 - robot.rotation.y) * 0.06;
    robot.rotation.x += (mouseY * 0.25 - robot.rotation.x) * 0.06;
    robot.position.set(0, Math.sin(t * 2.2) * 0.08, 0);
    robot.rotation.z = 0;
    ring.rotation.z = t * 0.5;
    ring.scale.setScalar(1);
    halo.rotation.z = -t * 0.8;
    eyeL.visible = true;
    eyeR.visible = true;

    // Page-specific animation
    switch (mode) {
      case 'scan': // head sweeps side to side
        robot.rotation.y = Math.sin(t * 3) * 0.35;
        ring.rotation.z = t * 3;
        break;
      case 'study': // slow nod while reading
        robot.rotation.x = Math.sin(t * 1.6) * 0.12;
        halo.rotation.z = -t * 2;
        break;
      case 'alert': // ring pulses, halo spins fast
        ring.scale.setScalar(1 + 0.06 * Math.sin(t * 6));
        halo.rotation.z = -t * 4;
        break;
      case 'chat': // eyes flicker while "typing"
        if (t < pulseUntil) {
          eyeL.visible = eyeR.visible = Math.sin(t * 20) > 0;
          robot.rotation.z = Math.sin(t * 10) * 0.04;
        }
        break;
    }

    // Result-state motion
    if (state === 'safe') robot.rotation.z += Math.sin(t * 4) * 0.05;
    if (state === 'danger') robot.position.x = Math.sin(t * 25) * 0.025;

    // Blink
    blinkT += 0.016;
    const blinking = blinkT > 3.5 && blinkT < 3.65 && mode !== 'scan';
    if (blinkT >= 3.65) blinkT = 0;
    eyeL.scale.y = eyeR.scale.y = blinking ? 0.05 : 1;

    renderer.render(scene, camera);
  }

  function setState(s, message) {
    state = s;
    const speech = document.getElementById('speech');
    if (speech) speech.className = 'speech ' + GLOW[s];
    const txt = document.getElementById('speech-text');
    if (txt && message) txt.textContent = message;
    const st = document.getElementById('status-text');
    if (st) st.textContent = STATUS[s];
    if (eyeMat) {
      eyeMat.color.setHex(COLORS[s]);
      ringMat.color.setHex(COLORS[s]);
    }
  }

  function pulse() {
    pulseUntil = clock.getElapsedTime() + 1.5;
  }

  window.Robot = { setState, pulse };
  window.addEventListener('load', build);
})();
