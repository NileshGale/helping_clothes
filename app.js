
function toggleNav() {
  document.getElementById('navLinks').classList.toggle('open');
}

document.addEventListener('click', (e) => {
  const nav = document.getElementById('navLinks');
  const hamburger = document.getElementById('hamburger');
  if (nav && hamburger && !nav.contains(e.target) && !hamburger.contains(e.target)) {
    nav.classList.remove('open');
  }
});

function animateOnScroll() {
  const elements = document.querySelectorAll('.cat-card, .step, .dash-card, .testi-card, .stat-item');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  elements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(22px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });
}

document.addEventListener('DOMContentLoaded', animateOnScroll);

document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', (e) => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    if (window.scrollY > 30) {
      navbar.style.boxShadow = '0 4px 24px rgba(0,0,0,0.12)';
    } else {
      navbar.style.boxShadow = '0 2px 16px rgba(0,0,0,0.06)';
    }
  }
});

function animateCounters() {
  const counters = document.querySelectorAll('.num');
  counters.forEach(counter => {
    const target = counter.textContent.replace(/[^0-9]/g, '');
    if (!target) return;
    const suffix = counter.textContent.replace(/[0-9,]/g, '');
    let start = 0;
    const duration = 1500;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
      start += step;
      if (start >= target) {
        counter.textContent = Number(target).toLocaleString() + suffix;
        clearInterval(timer);
      } else {
        counter.textContent = Math.floor(start).toLocaleString() + suffix;
      }
    }, 16);
  });
}

const statsBar = document.querySelector('.stats-bar');
if (statsBar) {
  const statsObserver = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      animateCounters();
      statsObserver.disconnect();
    }
  });
  statsObserver.observe(statsBar);
}
