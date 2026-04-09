function toggleNav() {
  document.getElementById('navLinks').classList.toggle('open');
}

/**
 * Global Auth Guard
 * Redirects guests away from private pages.
 */
function protectPage() {
  const user = localStorage.getItem('hh_user');
  const path = window.location.pathname;
  const isPrivatePage = path.includes('account.html');
  
  if (!user && isPrivatePage) {
    window.location.href = 'login.html';
  }
}

/**
 * Initalizes the navbar based on login status
 */
function initNavbar() {
  const user = JSON.parse(localStorage.getItem('hh_user') || 'null');
  const navLinks = document.getElementById('navLinks');
  if (!navLinks) return;

  if (user) {
    // Remove guest links
    const guestLinks = navLinks.querySelectorAll('a[href="login.html"], a[href="signup.html"]');
    guestLinks.forEach(link => link.parentElement.remove());

    // Add private links if they don't already exist in the flow
    if (!navLinks.querySelector('a[href="dashboard.html"]')) {
      const dashLi = document.createElement('li');
      dashLi.innerHTML = `<a href="dashboard.html">Dashboard</a>`;
      navLinks.appendChild(dashLi);
    }

    // Add Logout Button
    const logoutLi = document.createElement('li');
    logoutLi.innerHTML = `<a href="#" onclick="handleLogout()" class="btn-nav" style="background:#e84a5f;">Logout</a>`;
    navLinks.appendChild(logoutLi);
  }
}

/**
 * Custom Dialog System
 */
let modalResolve;

function initCustomDialog() {
  if (document.getElementById('customModal')) return;
  
  const modalHTML = `
    <div class="custom-modal-overlay" id="customModal">
      <div class="custom-dialog-box">
        <span class="dialog-icon" id="dialogIcon">❓</span>
        <h3 class="dialog-title" id="dialogTitle">Confirm</h3>
        <p class="dialog-msg" id="dialogMsg">Are you sure?</p>
        <div class="dialog-actions">
          <button class="dialog-btn btn-cancel" id="dialogCancelBtn">Cancel</button>
          <button class="dialog-btn btn-confirm" id="dialogOkBtn">OK</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  document.getElementById('dialogOkBtn').onclick = () => finishCustomDialog(true);
  document.getElementById('dialogCancelBtn').onclick = () => finishCustomDialog(false);
}

function showCustomModal(config) {
  initCustomDialog();
  const modal = document.getElementById('customModal');
  document.getElementById('dialogTitle').textContent = config.title || 'Message';
  document.getElementById('dialogMsg').textContent = config.message || '';
  document.getElementById('dialogIcon').textContent = config.icon || '💬';
  
  const cancelBtn = document.getElementById('dialogCancelBtn');
  cancelBtn.style.display = config.showCancel ? 'block' : 'none';
  
  modal.classList.add('active');
  
  return new Promise(resolve => {
    modalResolve = resolve;
  });
}

function finishCustomDialog(result) {
  document.getElementById('customModal').classList.remove('active');
  if (modalResolve) {
    modalResolve(result);
    modalResolve = null;
  }
}

window.customAlert = (message, title = 'Alert', icon = '🔔') => {
  return showCustomModal({ title, message, icon, showCancel: false });
};

window.customConfirm = (message, title = 'Confirm', icon = '❓') => {
  return showCustomModal({ title, message, icon, showCancel: true });
};

async function handleLogout() {
  const confirmed = await customConfirm('Are you sure you want to logout?');
  if (confirmed) {
    localStorage.removeItem('hh_user');
    window.location.href = 'index.html';
  }
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

document.addEventListener('DOMContentLoaded', () => {
  initCustomDialog();
  protectPage();
  initNavbar();
  animateOnScroll();
});

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
