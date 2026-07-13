import * as THREE from 'three';

// A small animated 3D node network behind the hero — nodes/lines standing in for
// an MLM distributor network. No-ops entirely if the canvas isn't on the page,
// WebGL isn't available, or the user prefers reduced motion (renders one static frame).
export function initNetworkScene() {
    const canvas = document.getElementById('hero-3d-canvas');
    if (!canvas) return;

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    } catch {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 100);
    camera.position.z = 18;

    const NODE_COUNT = 50;
    const positions = new Float32Array(NODE_COUNT * 3);
    for (let i = 0; i < NODE_COUNT; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 26;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 16;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 10;
    }

    const pointsGeometry = new THREE.BufferGeometry();
    pointsGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const pointsMaterial = new THREE.PointsMaterial({ color: 0x2c704c, size: 0.18, transparent: true, opacity: 0.7 });
    const points = new THREE.Points(pointsGeometry, pointsMaterial);
    scene.add(points);

    // Connect each node to its nearest few neighbours so it reads as a "network".
    const linePositions = [];
    const maxDistance = 6.5;
    for (let i = 0; i < NODE_COUNT; i++) {
        let connections = 0;
        for (let j = i + 1; j < NODE_COUNT && connections < 3; j++) {
            const dx = positions[i * 3] - positions[j * 3];
            const dy = positions[i * 3 + 1] - positions[j * 3 + 1];
            const dz = positions[i * 3 + 2] - positions[j * 3 + 2];
            const distance = Math.sqrt(dx * dx + dy * dy + dz * dz);

            if (distance < maxDistance) {
                linePositions.push(
                    positions[i * 3], positions[i * 3 + 1], positions[i * 3 + 2],
                    positions[j * 3], positions[j * 3 + 1], positions[j * 3 + 2],
                );
                connections++;
            }
        }
    }

    const lineGeometry = new THREE.BufferGeometry();
    lineGeometry.setAttribute('position', new THREE.BufferAttribute(new Float32Array(linePositions), 3));
    const lineMaterial = new THREE.LineBasicMaterial({ color: 0xd4af37, transparent: true, opacity: 0.15 });
    const lines = new THREE.LineSegments(lineGeometry, lineMaterial);
    scene.add(lines);

    const group = new THREE.Group();
    group.add(points, lines);
    scene.add(group);

    let targetTiltX = 0;
    let targetTiltY = 0;
    canvas.parentElement.addEventListener('pointermove', (event) => {
        const rect = canvas.parentElement.getBoundingClientRect();
        targetTiltY = ((event.clientX - rect.left) / rect.width - 0.5) * 0.3;
        targetTiltX = ((event.clientY - rect.top) / rect.height - 0.5) * -0.2;
    });

    function resize() {
        const { clientWidth, clientHeight } = canvas.parentElement;
        renderer.setSize(clientWidth, clientHeight, false);
        camera.aspect = clientWidth / (clientHeight || 1);
        camera.updateProjectionMatrix();
    }

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(canvas.parentElement);
    resize();

    if (prefersReducedMotion) {
        renderer.render(scene, camera);
        return;
    }

    let autoRotation = 0;
    let currentTiltX = 0;
    let currentTiltY = 0;

    function animate() {
        autoRotation += 0.0009;
        currentTiltX += (targetTiltX - currentTiltX) * 0.02;
        currentTiltY += (targetTiltY - currentTiltY) * 0.02;

        group.rotation.x = currentTiltX;
        group.rotation.y = autoRotation + currentTiltY;

        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    }

    animate();
}
