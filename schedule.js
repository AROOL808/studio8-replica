// Pastikan bookingsFromPHP sudah ada dari PHP
console.log('📊 Bookings dari PHP:', bookingsFromPHP);
console.log('📋 Total bookings:', bookingsFromPHP ? bookingsFromPHP.length : 0);

// Initialize calendar
const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const grid = document.getElementById('calendarGrid');
const monthLabel = document.getElementById('monthLabel');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

// Cek apakah elemen DOM ada
if (!grid || !monthLabel || !prevBtn || !nextBtn) {
  console.error('❌ DOM elements tidak ditemukan!');
  alert('Error: Elemen kalender tidak ditemukan. Periksa HTML.');
}

let current = new Date();

function getBookingsForMonth(year, month) {
  const bookingsByDate = {};
  
  // Cek apakah bookingsFromPHP ada dan array
  if (!bookingsFromPHP || !Array.isArray(bookingsFromPHP)) {
    console.warn('⚠️ bookingsFromPHP tidak valid atau kosong');
    return bookingsByDate;
  }
  
  bookingsFromPHP.forEach(booking => {
    if (!booking.tanggal) return; // Skip jika tidak ada tanggal
    
    const bookingDate = new Date(booking.tanggal);
    
    if (bookingDate.getFullYear() === year && bookingDate.getMonth() === month) {
      const day = bookingDate.getDate();
      
      if (!bookingsByDate[day]) {
        bookingsByDate[day] = [];
      }
      
      bookingsByDate[day].push(booking);
    }
  });
  
  console.log(`📅 Bookings for ${monthNames[month]} ${year}:`, bookingsByDate);
  return bookingsByDate;
}

function renderCalendar(date) {
  const year = date.getFullYear();
  const month = date.getMonth();
  monthLabel.textContent = `${monthNames[month]} ${year}`;

  const bookingsData = getBookingsForMonth(year, month);
  grid.innerHTML = '';

  const firstDay = new Date(year, month, 1);
  const totalDays = new Date(year, month + 1, 0).getDate();
  const startWeekday = firstDay.getDay();
  const today = new Date();

  // Empty cells
  for (let i = 0; i < startWeekday; i++) {
    const blank = document.createElement('div');
    blank.className = 'day-cell empty';
    grid.appendChild(blank);
  }

  // Days
  for (let d = 1; d <= totalDays; d++) {
    const cell = document.createElement('button');
    cell.type = 'button';
    cell.className = 'day-cell';
    cell.textContent = d;

    // Tandai hari ini
    if (today.getFullYear() === year && today.getMonth() === month && today.getDate() === d) {
      cell.classList.add('today');
    }

    // Cek apakah ada booking
    const hasBooking = bookingsData[d] && bookingsData[d].length > 0;
    if (hasBooking) {
      cell.classList.add('has-event');
      
      // Tambah badge jika lebih dari 1 booking
      if (bookingsData[d].length > 1) {
        const badge = document.createElement('span');
        badge.style.cssText = 'position:absolute;top:2px;left:2px;background:#000;color:#fff;border-radius:50%;width:18px;height:18px;font-size:0.7rem;display:flex;align-items:center;justify-content:center;font-weight:bold;';
        badge.textContent = bookingsData[d].length;
        cell.appendChild(badge);
      }
    }

    // Event click
    cell.addEventListener('click', () => {
      const dateStr = `${d} ${monthNames[month]} ${year}`;
      
      if (hasBooking) {
        const bookings = bookingsData[d];
        let msg = `📸 ${dateStr}\n\n📋 ${bookings.length} Booking Terdaftar:\n\n`;
        
        bookings.forEach((b, i) => {
          msg += `━━━━━━━━━━━━━━━━━━━━\n`;
          msg += `${i+1}. ${b.nama || 'N/A'}\n`;
          msg += `   📧 ${b.email || 'N/A'}\n`;
          msg += `   📱 ${b.nomor_hp || 'N/A'}\n`;
          msg += `   📅 ${b.tanggal || 'N/A'}\n`;
          msg += `   ⏰ ${b.waktu || 'N/A'}\n`;
          msg += `   📌 Status: ${b.status || 'N/A'}\n`;
          msg += `   🆔 Order ID: ${b.order_id || 'N/A'}\n`;
        });
        
        msg += `\n━━━━━━━━━━━━━━━━━━━━\n`;
        msg += `❌ Slot pada tanggal ini sudah terisi.\nSilakan pilih tanggal lain.`;
        
        alert(msg);
      } else {
        alert(`📸 ${dateStr}\n\n✅ Slot tersedia!\n\n⏰ Jam Sesi:\n• 09:00 - 11:00\n• 13:00 - 15:00\n• 16:00 - 18:00\n\nKlik "Booking" untuk reservasi!`);
      }
    });

    grid.appendChild(cell);
  }
  
  console.log('✅ Calendar rendered successfully');
}

// Event listeners untuk navigasi
if (prevBtn) {
  prevBtn.addEventListener('click', () => {
    current = new Date(current.getFullYear(), current.getMonth() - 1);
    renderCalendar(current);
  });
}

if (nextBtn) {
  nextBtn.addEventListener('click', () => {
    current = new Date(current.getFullYear(), current.getMonth() + 1);
    renderCalendar(current);
  });
}

// Initial render setelah DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM loaded, rendering calendar...');
    renderCalendar(current);
  });
} else {
  console.log('🚀 DOM already loaded, rendering calendar...');
  renderCalendar(current);
}

/* ========================================================================
   JAVASCRIPT KHUSUS UNTUK HALAMAN EXTRA EDIT (extra-edit.php)
   ======================================================================== */

// Cek apakah ini halaman extra-edit
if (document.body.classList.contains('extra-edit-page')) {
  let extras = [];
  
  // Show alert message
  function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertHTML = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
    alertContainer.innerHTML = alertHTML;
    
    setTimeout(() => {
      const alert = alertContainer.querySelector('.alert');
      if (alert) {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
      }
    }, 5000);
  }
  
  // Toggle loading state on button
  function toggleButtonLoading(button, isLoading) {
    const spinner = button.querySelector('.spinner-border');
    const text = button.querySelector('.btn-text');
    
    if (isLoading) {
      button.disabled = true;
      spinner.classList.remove('d-none');
      text.textContent = 'Memproses...';
    } else {
      button.disabled = false;
      spinner.classList.add('d-none');
      text.textContent = button.id === 'submitBtn' ? 'Tambah Extra' : 'Simpan Perubahan';
    }
  }
  
  // AJAX Helper
  async function ajax(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    for (let key in data) {
      formData.append(key, data[key]);
    }
    
    const response = await fetch('', {
      method: 'POST',
      body: formData
    });
    
    return await response.json();
  }
  
  // Load extras
  async function loadExtras() {
    try {
      const result = await ajax('load');
      
      if (result.success) {
        extras = result.data.map(item => ({
          id: item.extra_id,
          name: item.nama,
          price: item.harga,
          description: item.deskripsi || ''
        }));
        
        renderExtras();
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error loading extras:', error);
      showAlert('Gagal memuat data: ' + error.message, 'danger');
      
      document.getElementById('extraList').innerHTML = `
        <div class="empty-state">
          <p class="empty-state-text text-danger">⚠️ Gagal memuat data.</p>
        </div>
      `;
    }
  }
  
  // Render extra list
  function renderExtras() {
    const container = document.getElementById('extraList');
    const countBadge = document.getElementById('extraCount');
    
    countBadge.textContent = `${extras.length} Item`;
    
    if (extras.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <p class="empty-state-text">📦 Belum ada extra. Tambahkan extra pertama Anda menggunakan form di atas.</p>
        </div>
      `;
      return;
    }
    
    container.innerHTML = extras.map(extra => `
      <div class="extra-card" data-id="${extra.id}">
        <div class="extra-header">
          <div class="extra-title">
            <h6>${extra.name}</h6>
          </div>
        </div>
        
        <div class="extra-price">
          Rp ${extra.price.toLocaleString('id-ID')}
        </div>
        
        <div class="extra-description">
          ${extra.description || 'Tidak ada deskripsi'}
        </div>
        
        <div class="extra-actions">
          <button class="btn btn-edit" onclick="editExtra(${extra.id})">
            ✏️ Edit
          </button>
          <button class="btn btn-delete" onclick="deleteExtra(${extra.id})">
            🗑️ Hapus
          </button>
        </div>
      </div>
    `).join('');
  }
  
  // Add new extra form submit
  const addExtraForm = document.getElementById('addExtraForm');
  if (addExtraForm) {
    addExtraForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const submitBtn = document.getElementById('submitBtn');
      toggleButtonLoading(submitBtn, true);
      
      try {
        const result = await ajax('add', {
          name: document.getElementById('extraName').value,
          price: document.getElementById('extraPrice').value,
          description: document.getElementById('extraDescription').value || ''
        });
        
        if (result.success) {
          e.target.reset();
          await loadExtras();
          showAlert('✅ Extra berhasil ditambahkan!', 'success');
        } else {
          throw new Error(result.error);
        }
      } catch (error) {
        console.error('Error adding extra:', error);
        showAlert('❌ Gagal menambahkan extra: ' + error.message, 'danger');
      } finally {
        toggleButtonLoading(submitBtn, false);
      }
    });
  }
  
  // Edit extra
  window.editExtra = function(id) {
    const extra = extras.find(e => e.id === id);
    if (!extra) return;
    
    document.getElementById('editExtraId').value = extra.id;
    document.getElementById('editExtraName').value = extra.name;
    document.getElementById('editExtraPrice').value = extra.price;
    document.getElementById('editExtraDescription').value = extra.description;
    
    const modal = new bootstrap.Modal(document.getElementById('editExtraModal'));
    modal.show();
  }
  
  // Save edited extra
  const editExtraForm = document.getElementById('editExtraForm');
  if (editExtraForm) {
    editExtraForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const updateBtn = document.getElementById('updateBtn');
      toggleButtonLoading(updateBtn, true);
      
      try {
        const result = await ajax('update', {
          id: document.getElementById('editExtraId').value,
          name: document.getElementById('editExtraName').value,
          price: document.getElementById('editExtraPrice').value,
          description: document.getElementById('editExtraDescription').value || ''
        });
        
        if (result.success) {
          await loadExtras();
          
          const modal = bootstrap.Modal.getInstance(document.getElementById('editExtraModal'));
          modal.hide();
          
          showAlert('✅ Extra berhasil diupdate!', 'success');
        } else {
          throw new Error(result.error);
        }
      } catch (error) {
        console.error('Error updating extra:', error);
        showAlert('❌ Gagal mengupdate extra: ' + error.message, 'danger');
      } finally {
        toggleButtonLoading(updateBtn, false);
      }
    });
  }
  
  // Delete extra
  window.deleteExtra = async function(id) {
    if (!confirm('⚠️ Yakin ingin menghapus extra ini?')) return;
    
    try {
      const result = await ajax('delete', { id });
      
      if (result.success) {
        await loadExtras();
        showAlert('✅ Extra berhasil dihapus!', 'success');
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error deleting extra:', error);
      showAlert('❌ Gagal menghapus extra: ' + error.message, 'danger');
    }
  }
  
  // Logout
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      if (confirm('Yakin ingin logout?')) {
        showAlert('👋 Logout berhasil!', 'info');
        setTimeout(() => {
          window.location.href = 'index.html';
        }, 1000);
      }
    });
  }

  // Load extras on page load
  loadExtras();
}