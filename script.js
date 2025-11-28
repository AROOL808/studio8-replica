// script.js
// Animations: reveal-on-scroll, smooth scrolling for internal links, subtle header shadow on scroll.

document.addEventListener('DOMContentLoaded', function () {
  // IntersectionObserver for reveal animations
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.12
  };

  const revealObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        obs.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.section-animate').forEach(el => revealObserver.observe(el));

  // Make header shadow appear after scroll
  const header = document.querySelector('.site-header .navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      header.classList.add('shadow-sm');
    } else {
      header.classList.remove('shadow-sm');
    }
  });

  // Smooth scroll for internal links (buttons linking to anchors)
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href').slice(1);
      const target = document.getElementById(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});

/* Dynamic showcase gallery (runs only when #masonry exists) */
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const masonry = document.getElementById('masonry');
    if (!masonry) return; // only run on showcase page

    const loadMoreBtn = document.getElementById('loadMore');
    const lightboxModalEl = document.getElementById('lightboxModal');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const bsModal = new bootstrap.Modal(lightboxModalEl, {});

    // Sample images (Unsplash) — adjust or replace with local assets as needed
    const images = [
      { src: 'img/about-1.jpg', title: 'Photo showcase' , author: 'Studio 8' },
      { src: 'img/about-2.jpg', title: 'Photo showcase' , author: 'Studio 8' },
      { src: 'img/hero-3.jpg', title: 'Photo showcase' , author: 'Studio 8' },
      { src: 'img/hero-4.jpg', title: 'Photo showcase' , author: 'Studio 8' },
      { src: 'img/hero-1.jpg', title: 'Photo showcase' , author: 'Studio 8' },
      { src: 'img/hero-2.jpg', title: 'Photo showcase' , author: 'Studio 8' },
      
    ];

    let index = 0;
    const BATCH = 6;

    function escapeHtml(text) {
      return String(text).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
    }

    function createItem(item) {
      const wrapper = document.createElement('figure');
      wrapper.className = 'masonry-item';
      wrapper.tabIndex = 0;
      wrapper.setAttribute('role', 'button');
      wrapper.setAttribute('aria-label', item.title || 'Image');

      const img = document.createElement('img');
      img.dataset.src = item.src;
      img.alt = item.title || 'Studio 8 image';
      img.loading = 'lazy';
      img.className = 'lazy';

      const overlay = document.createElement('figcaption');
      overlay.className = 'item-overlay';
      overlay.innerHTML = `<div class="title">${escapeHtml(item.title || '')}</div><div class="meta">${escapeHtml(item.author || '')}</div>`;

      wrapper.appendChild(img);
      wrapper.appendChild(overlay);

      wrapper.addEventListener('click', () => openLightbox(item));
      wrapper.addEventListener('keypress', (e) => { if (e.key === 'Enter') openLightbox(item); });

      return wrapper;
    }

    function openLightbox(item) {
      lightboxImage.src = item.src + '&dpr=2';
      lightboxImage.alt = item.title || '';
      lightboxCaption.textContent = (item.title ? item.title + ' — ' : '') + (item.author || '');
      bsModal.show();
    }

    // IntersectionObserver for lazy loading
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          img.classList.remove('lazy');
        }
        io.unobserve(img);
      });
    }, { rootMargin: '200px 0px' });

    function renderBatch() {
      const fragment = document.createDocumentFragment();
      for (let i = 0; i < BATCH && index < images.length; i++, index++) {
        const node = createItem(images[index]);
        fragment.appendChild(node);
      }
      masonry.appendChild(fragment);
      masonry.querySelectorAll('img.lazy').forEach(img => io.observe(img));
      if (index >= images.length) loadMoreBtn.style.display = 'none';
    }

    loadMoreBtn.addEventListener('click', function () {
      if (index >= images.length) {
        const cloneBatch = images.map(img => Object.assign({}, img));
        images.push(...cloneBatch);
      }
      renderBatch();
    });

    renderBatch();
  });
})();

/* Clients page logic (renders sample data, search, toggle detail) */
// (function () {
//   document.addEventListener('DOMContentLoaded', function () {
//     if (!document.getElementById('clientsList')) return;

//     const clients = [
//       {
//         id: 1, name: 'Siti Rahma', avatar: 'https://i.pravatar.cc/100?img=32',
//         package: 'Graduation Shoot', variant: 'Premium', date: '2025-11-01', time: '10:00',
//         payment: 'Transfer Bank', status: 'Confirmed', email: 'siti@example.com', phone: '+62812345', note: 'Bawa toga'
//       },
//       {
//         id: 2, name: 'Andi Saputra', avatar: 'https://i.pravatar.cc/100?img=12',
//         package: 'Self Portrait', variant: 'Basic', date: '2025-10-12', time: '14:30',
//         payment: 'Kartu Kredit', status: 'Pending', email: 'andi@example.com', phone: '+62898765', note: ''
//       },
//       {
//         id: 3, name: 'Maya Indri', avatar: 'https://i.pravatar.cc/100?img=45',
//         package: 'Graduation Shoot', variant: 'Basic', date: '2025-09-22', time: '09:00',
//         payment: 'Transfer Bank', status: 'Completed', email: 'maya@example.com', phone: '+62833445', note: 'Butuh retouch natural'
//       }
//     ];

//     const listEl = document.getElementById('clientsList');
//     const emptyState = document.getElementById('emptyState');
//     const searchInput = document.getElementById('clientSearch');

//     // function render(items) {
//     //   listEl.innerHTML = '';
//     //   if (!items.length) {
//     //     emptyState.style.display = 'block';
//     //     return;
//     //   }
//     //   emptyState.style.display = 'none';

//     //   items.forEach(c => {
//     //     const col = document.createElement('div');
//     //     col.className = 'col-12';

//     //     const card = document.createElement('div');
//     //     card.className = 'client-card d-flex gap-3 align-items-start';

//     //     card.innerHTML = `
//     //       <img src="${c.avatar}" alt="${c.name}" class="client-avatar" />
//     //       <div class="flex-grow-1">
//     //         <div class="d-flex justify-content-between align-items-start">
//     //           <div>
//     //             <h5 class="mb-1">${c.name}</h5>
//     //             <div class="client-meta">${c.package} • ${c.variant} • <strong>${c.status}</strong></div>
//     //           </div>
//     //           <div class="text-end">
//     //             <div class="client-meta">${c.date} • ${c.time}</div>
//     //             <div class="mt-2">
//     //               <button class="btn btn-sm btn-outline-dark me-1 btn-detail" data-id="${c.id}">Detail</button>
//     //               <button class="btn btn-sm btn-dark btn-action" data-id="${c.id}">Action</button>
//     //             </div>
//     //           </div>
//     //         </div>

//     //         <div id="detail-${c.id}" class="client-detail collapse">
//     //           <div><strong>Email:</strong> ${c.email}</div>
//     //           <div><strong>Telepon:</strong> ${c.phone}</div>
//     //           <div><strong>Pembayaran:</strong> ${c.payment}</div>
//     //           <div><strong>Catatan:</strong> ${c.note || '-'}</div>
//     //         </div>
//     //       </div>
//     //     `;
//     //     col.appendChild(card);
//     //     listEl.appendChild(col);
//     //   });

//       // attach handlers
//       listEl.querySelectorAll('.btn-detail').forEach(btn => {
//         btn.addEventListener('click', function () {
//           const id = this.dataset.id;
//           const target = document.getElementById('detail-' + id);
//           if (target) {
//             new bootstrap.Collapse(target, { toggle: true });
//           }
//         });
//       });

//       listEl.querySelectorAll('.btn-action').forEach(btn => {
//         btn.addEventListener('click', function () {
//           const id = this.dataset.id;
//           const client = clients.find(x => String(x.id) === id);
//           if (client) {
//             alert(`Action demo untuk ${client.name}\nStatus: ${client.status}`);
//           }
//         });
//       });
//     }

//     // initial render
//     // render(clients);

//     // search filter
//     // searchInput.addEventListener('input', function () {
//     //   const q = this.value.trim().toLowerCase();
//     //   const filtered = clients.filter(c =>
//     //     c.name.toLowerCase().includes(q) ||
//     //     c.package.toLowerCase().includes(q) ||
//     //     c.status.toLowerCase().includes(q)
//     //   );
//     //   render(filtered);
//     // });

//     // logout (demo)
// //     const logoutBtn = document.getElementById('logoutBtn');
// //     if (logoutBtn) {
// //       logoutBtn.addEventListener('click', function () {
// //         if (confirm('Logout dari panel admin?')) {
// //           // placeholder: redirect to index or perform logout
// //           window.location.href = 'index.html';
// //         }
// //       });
// //     }
// //   });
// // })();

// // // Package selection highlight on booking page
// document.addEventListener('DOMContentLoaded', function () {
//   // package selection highlight
//   const packageCards = document.querySelectorAll('.package-card');
//   packageCards.forEach(card => {
//     card.addEventListener('click', function (e) {
//       // simulate radio click when clicking the whole label
//       const radio = this.querySelector('input[type="radio"]');
//       if (radio) radio.checked = true;
//       packageCards.forEach(c => c.classList.remove('selected'));
//       this.classList.add('selected');
//     });
//   });
// });