// JavaScript untuk Sistem Perpustakaan

// Inisialisasi ketika dokumen sudah siap
document.addEventListener('DOMContentLoaded', function() {
    initializeComponents();
});

// Fungsi untuk inisialisasi komponen
function initializeComponents() {
    initializeTooltips();
    initializeModals();
    initializeFormValidation();
    initializeSearchFeatures();
    initializeFileUpload();
    initializeDatePicker();
}

// Inisialisasi Bootstrap tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Inisialisasi modal konfirmasi
function initializeModals() {
    // Modal konfirmasi hapus
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            const itemName = this.getAttribute('data-name') || 'item ini';
            
            showConfirmModal(
                'Konfirmasi Hapus',
                `Apakah Anda yakin ingin menghapus "${itemName}"? Tindakan ini tidak dapat dibatalkan.`,
                function() {
                    window.location.href = href;
                }
            );
        });
    });
}

// Fungsi untuk menampilkan modal konfirmasi
function showConfirmModal(title, message, onConfirm) {
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="confirmButton">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Hapus modal yang ada jika ada
    const existingModal = document.getElementById('confirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Tambahkan modal baru
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    // Event listener untuk tombol konfirmasi
    document.getElementById('confirmButton').addEventListener('click', function() {
        modal.hide();
        if (onConfirm) onConfirm();
    });
    
    modal.show();
    
    // Hapus modal setelah ditutup
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Inisialisasi validasi form
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Inisialisasi fitur pencarian
function initializeSearchFeatures() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = document.getElementById(this.getAttribute('data-target'));
            
            if (targetTable) {
                searchTable(targetTable, searchTerm);
            }
        });
    });
}

// Fungsi pencarian dalam tabel
function searchTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Inisialisasi upload file
function initializeFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                validateFile(file, this);
                previewImage(file, this);
            }
        });
    });
}

// Validasi file upload
function validateFile(file, input) {
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    if (file.size > maxSize) {
        showAlert('File terlalu besar. Maksimal 2MB.', 'danger');
        input.value = '';
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.', 'danger');
        input.value = '';
        return false;
    }
    
    return true;
}
// Preview gambar sebelum upload
function previewImage(file, input) {
    const previewId = input.getAttribute('data-preview');
    if (!previewId) return;

    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById('cover-placeholder');

    if (!preview) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        // Tampilkan gambar
        preview.src = e.target.result;
        preview.style.display = 'block';
        // Sembunyikan placeholder
        if (placeholder) {
            placeholder.style.display = 'none';
        }
    };
    reader.readAsDataURL(file);
}




// Inisialisasi date picker
function initializeDatePicker() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set minimum date to today for future dates
        if (input.classList.contains('future-date')) {
            const today = new Date().toISOString().split('T')[0];
            input.min = today;
        }
    });
}

// Fungsi untuk menampilkan alert
function showAlert(message, type = 'info', duration = 5000) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <i class="bi bi-info-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto hide alert
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, duration);
}

// Fungsi untuk loading state
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner me-2"></span>Loading...';
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Fungsi untuk format currency (Rupiah)
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Fungsi untuk format tanggal Indonesia
function formatDateIndo(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return date.toLocaleDateString('id-ID', options);
}

// Fungsi untuk menghitung selisih hari
function daysDifference(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000;
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);
    
    return Math.round((secondDate - firstDate) / oneDay);
}

// Fungsi untuk auto-update denda
function updateDenda() {
    const dendaInputs = document.querySelectorAll('.denda-input');
    
    dendaInputs.forEach(input => {
        const tglKembali = input.getAttribute('data-tgl-kembali');
        const tglHariIni = new Date().toISOString().split('T')[0];
        
        const selisihHari = daysDifference(tglKembali, tglHariIni);
        
        if (selisihHari > 0) {
            const denda = selisihHari * 1000; // Rp 1000 per hari
            input.value = formatCurrency(denda);
            input.parentElement.classList.add('text-danger');
        }
    });
}

// Fungsi untuk print laporan
function printReport() {
    window.print();
}

// Fungsi untuk export ke Excel (menggunakan SheetJS jika diperlukan)
function exportToExcel(tableId, filename) {
    // Implementasi export Excel bisa ditambahkan di sini
    showAlert('Fitur export Excel akan segera tersedia', 'info');
}

// Event listener untuk tombol kembali
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-back')) {
        e.preventDefault();
        window.history.back();
    }
});

// Auto-refresh untuk halaman tertentu
function autoRefresh(interval = 30000) {
    if (document.querySelector('.auto-refresh')) {
        setTimeout(() => {
            location.reload();
        }, interval);
    }
}

// Inisialisasi auto-refresh jika diperlukan
autoRefresh();

// Fungsi untuk toggle sidebar pada mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

// Event listener untuk responsive sidebar
document.addEventListener('click', function(e) {
    if (e.target.id === 'sidebarToggle') {
        toggleSidebar();
    }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    if (sidebar && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('show');
    }
});

// Fungsi untuk konfirmasi sebelum meninggalkan halaman dengan form yang belum disimpan
let formChanged = false;

document.addEventListener('input', function(e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
        formChanged = true;
    }
});

document.addEventListener('submit', function() {
    formChanged = false;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});
