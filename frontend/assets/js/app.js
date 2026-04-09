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

  const currentPath = window.location.pathname.split('/').pop() || 'index.html';

  // Define All Links and their visibility
  const links = [
    { href: 'index.html', text: 'Home', guest: true, private: true },
    { href: 'dashboard.html', text: 'Dashboard', guest: false, private: true },
    { href: 'need_help.html', text: 'Need Help', guest: true, private: true },
    { href: 'impact.html', text: 'Impact', guest: true, private: true },
    { href: 'feedback.html', text: 'Feedback', guest: true, private: true },
    { href: 'contact.html', text: 'Contact Us', guest: true, private: true }
  ];

  // Add Auth specific links
  if (user) {
    links.push({ href: 'account.html', text: 'My Account', guest: false, private: true });
  } else {
    links.push({ href: 'login.html', text: 'Login', guest: true, private: false, class: 'btn-nav' });
    links.push({ href: 'signup.html', text: 'Sign Up', guest: true, private: false });
  }

  // Build the HTML
  navLinks.innerHTML = links
    .filter(link => (user && link.private) || (!user && link.guest))
    .map(link => {
      const isActive = currentPath === link.href ? 'active' : '';
      const extraClass = link.class || '';
      return `<li><a href="${link.href}" class="${isActive} ${extraClass}">${link.text}</a></li>`;
    })
    .join('');
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

window.handleLogout = async function() {
  const confirmed = await customConfirm('Are you sure you want to logout?', 'Logout', '🚪');
  if (confirmed) {
    localStorage.removeItem('hh_user');
    sessionStorage.clear(); // Clear any pending OTP data or session flags
    window.location.href = 'login.html';
  }
};

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
