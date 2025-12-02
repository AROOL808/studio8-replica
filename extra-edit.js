// JavaScript untuk halaman Extra Edit
console.log('🚀 Extra Edit JS loaded');

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
  console.log('📥 Loading extras...');
  try {
    const result = await ajax('load');
    console.log('📦 Result:', result);
    
    if (result.success) {
      extras = result.data.map(item => ({
        id: item.extra_id,
        name: item.nama,
        price: item.harga,
        description: item.deskripsi || ''
      }));
      
      console.log('✅ Extras loaded:', extras);
      renderExtras();
    } else {
      throw new Error(result.error);
    }
  } catch (error) {
    console.error('❌ Error loading extras:', error);
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
  console.log('🎨 Rendering extras...');
  const container = document.getElementById('extraList');
  const countBadge = document.getElementById('extraCount');
  
  if (!container) {
    console.error('❌ extraList container not found!');
    return;
  }
  
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
  
  console.log('✅ Extras rendered');
}

// Add new extra form submit
const addExtraForm = document.getElementById('addExtraForm');
if (addExtraForm) {
  addExtraForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    console.log('📤 Adding extra...');
    
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
  console.log('✏️ Editing extra:', id);
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
    console.log('💾 Updating extra...');
    
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
  console.log('🗑️ Deleting extra:', id);
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
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM loaded, loading extras...');
    loadExtras();
  });
} else {
  console.log('🚀 DOM already loaded, loading extras...');
  loadExtras();
}