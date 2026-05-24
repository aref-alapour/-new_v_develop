export function initScaryParticles() {
  const canvas = document.getElementById('scary-particles-canvas');
  const section = document.getElementById('scary-proposal-section');
  if (!canvas || !section) {
    return;
  }

  const context = canvas.getContext('2d');
  if (!context) {
    return;
  }

  const particles = [];
  const count = window.innerWidth < 768 ? 50 : 100;

  const resize = () => {
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
  };

  resize();
  window.addEventListener('resize', resize);

  for (let index = 0; index < count; index += 1) {
    particles.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      radius: Math.random() * 12 + 4,
      vx: (Math.random() - 0.5) * 0.6,
      vy: (Math.random() - 0.5) * 0.6,
      alpha: Math.random() * 0.4 + 0.1,
    });
  }

  const draw = () => {
    context.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach((particle) => {
      particle.x += particle.vx;
      particle.y += particle.vy;

      if (particle.x < 0 || particle.x > canvas.width) {
        particle.vx *= -1;
      }
      if (particle.y < 0 || particle.y > canvas.height) {
        particle.vy *= -1;
      }

      const gradient = context.createRadialGradient(
        particle.x,
        particle.y,
        0,
        particle.x,
        particle.y,
        particle.radius
      );
      gradient.addColorStop(0, `rgba(251, 146, 60, ${particle.alpha})`);
      gradient.addColorStop(1, 'rgba(251, 146, 60, 0)');

      context.fillStyle = gradient;
      context.beginPath();
      context.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
      context.fill();
    });

    requestAnimationFrame(draw);
  };

  draw();
}
