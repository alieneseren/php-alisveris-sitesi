// Sepete ekle butonu için event
document.addEventListener('DOMContentLoaded', function() {
  // Kategori dropdown açılır menü
  fetch('kategori_listesi.php')
    .then(res => res.json())
    .then(data => {
      const menu = document.getElementById('kategoriDropdownMenu');
      if (menu && Array.isArray(data)) {
        menu.innerHTML = '';
        data.forEach(kat => {
          const li = document.createElement('li');
          li.style.listStyle = 'none';
          li.innerHTML = `<a href="#" class="dropdown-item" data-kategori-id="${kat.id}" style="display:block;padding:0.7rem 1.2rem;color:#222;text-decoration:none;">${kat.kategori_adi}</a>`;
          menu.appendChild(li);
        });
      }
    });

  const dropdownBtn = document.getElementById('kategoriDropdownBtn');
  const dropdownMenu = document.getElementById('kategoriDropdownMenu');
  if (dropdownBtn && dropdownMenu) {
    dropdownBtn.addEventListener('click', function(e) {
      e.preventDefault();
      dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', function(e) {
      if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.style.display = 'none';
      }
    });
    dropdownMenu.addEventListener('click', function(e) {
      if (e.target.classList.contains('dropdown-item')) {
        e.preventDefault();
        const kategoriId = e.target.getAttribute('data-kategori-id');
        // Sadece anasayfada AJAX, diğer sayfalarda yönlendirme
        if (window.location.pathname.endsWith('index.php') || window.location.pathname === '/' || window.location.pathname === '/index.php') {
          window.dispatchEvent(new CustomEvent('kategoriSecildi', { detail: { kategoriId } }));
        } else {
          window.location.href = 'urunler.php?kategori_id=' + encodeURIComponent(kategoriId);
        }
        dropdownMenu.style.display = 'none';
      }
    });
  }
  // Sidebar kategori dropdown
  fetch('kategori_listesi.php')
    .then(res => res.json())
    .then(data => {
      const menu = document.getElementById('sidebarKategoriDropdownMenu');
      if (menu && Array.isArray(data)) {
        menu.innerHTML = '';
        data.forEach(kat => {
          const li = document.createElement('li');
          li.style.listStyle = 'none';
          li.innerHTML = `<a href="#" class="dropdown-item" data-kategori-id="${kat.id}" style="display:block;padding:0.7rem 1.2rem;color:#222;text-decoration:none;">${kat.kategori_adi}</a>`;
          menu.appendChild(li);
        });
      }
    });
  const sidebarDropdownBtn = document.getElementById('sidebarKategoriDropdownBtn');
  const sidebarDropdownMenu = document.getElementById('sidebarKategoriDropdownMenu');
  if (sidebarDropdownBtn && sidebarDropdownMenu) {
    sidebarDropdownBtn.addEventListener('click', function(e) {
      e.preventDefault();
      sidebarDropdownMenu.style.display = sidebarDropdownMenu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', function(e) {
      if (!sidebarDropdownBtn.contains(e.target) && !sidebarDropdownMenu.contains(e.target)) {
        sidebarDropdownMenu.style.display = 'none';
      }
    });
    sidebarDropdownMenu.addEventListener('click', function(e) {
      if (e.target.classList.contains('dropdown-item')) {
        e.preventDefault();
        const kategoriId = e.target.getAttribute('data-kategori-id');
        window.dispatchEvent(new CustomEvent('kategoriSecildi', { detail: { kategoriId } }));
        sidebarDropdownMenu.style.display = 'none';
      }
    });
  }

  // Kategori seçildiğinde ürünleri AJAX ile getir
  window.addEventListener('kategoriSecildi', function(e) {
    const kategoriId = e.detail.kategoriId;
    fetch('kategori_urunler.php?kategori_id=' + encodeURIComponent(kategoriId))
      .then(res => res.text())
      .then(html => {
        const urunlerListesi = document.getElementById('urunler-listesi');
        if (urunlerListesi) urunlerListesi.innerHTML = html;
      });
  });
  document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const urunId = this.getAttribute('data-urun-id');
      fetch('sepet_ekle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'urun_id=' + encodeURIComponent(urunId)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const cartCount = document.getElementById('cart-count');
          if (cartCount) {
            cartCount.textContent = data.count;
            cartCount.style.display = data.count > 0 ? 'inline-block' : 'none';
          }
          alert('Ürün sepete eklendi!');
        } else {
          alert('Sepete eklenirken hata oluştu!');
        }
      });
    });
  });

  // Sayfa yüklendiğinde sepet sayısını güncelle
  fetch('sepet_ekle.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'get_count=1' })
    .then(res => res.json())
    .then(data => {
      const cartCount = document.getElementById('cart-count');
      if (cartCount) {
        cartCount.textContent = data.count;
        cartCount.style.display = data.count > 0 ? 'inline-block' : 'none';
      }
    });
});
// Scroll ile Header Efekti
window.addEventListener('scroll', () => {
  const header = document.querySelector('.header');
  if (header) {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }
});

// Ürün Kartlarına Animasyon Ekleme
const productCards = document.querySelectorAll('.product-card');
productCards.forEach((card, index) => {
  card.style.opacity = '0';
  card.style.transform = 'translateY(20px)';
  card.style.animationDelay = `${index * 0.1}s`;
  card.classList.add('animate');
});

// Smooth Scrolling
if (document.querySelectorAll('a[href^="#"]').length > 0) {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
}

// Parallax Efekti
window.addEventListener('scroll', () => {
  const hero = document.querySelector('.hero');
  if (hero) {
    const scrollPosition = window.pageYOffset;
    hero.style.backgroundPositionY = `${scrollPosition * 0.5}px`;
  }
});

// Modal Açma/Kapama
const productModals = document.querySelectorAll('[data-modal]');
productModals.forEach(modal => {
  modal.addEventListener('click', () => {
    const modalId = modal.getAttribute('data-modal');
    const targetModal = document.getElementById(modalId);
    if (targetModal) {
      targetModal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  });
});

document.querySelectorAll('.modal-close').forEach(closeBtn => {
  closeBtn.addEventListener('click', () => {
    document.querySelectorAll('.modal').forEach(modal => {
      modal.classList.remove('active');
    });
    document.body.style.overflow = 'auto';
  });
});

// Dinamik Yükleniyor Efekti
function simulateLoading() {
  const loadingElements = document.querySelectorAll('.loading');
  loadingElements.forEach(element => {
    let dots = 0;
    const interval = setInterval(() => {
      dots = (dots + 1) % 4;
      element.textContent = 'Yükleniyor' + '.'.repeat(dots);
    }, 500);
    setTimeout(() => {
      clearInterval(interval);
      element.textContent = 'Yüklendi!';
    }, 3000);
  });
}

// Sayfa yüklendiğinde çalıştır
window.addEventListener('DOMContentLoaded', () => {
  simulateLoading();
});
