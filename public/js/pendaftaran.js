/**
 * pendaftaran.js - File JavaScript untuk Sistem Pendaftaran Magang
 * Enhanced version with improved functionality
 */

// Fungsi untuk validasi tanggal magang dengan durasi minimal 30 hari
function validateDates() {
    const startDate = document.querySelector('input[name="tanggal_mulai"]');
    const endDate = document.querySelector('input[name="tanggal_selesai"]');
    const formElement = document.getElementById('form-pendaftaran');
    
    if(startDate && endDate) {
        // Set minimal tanggal mulai ke hari ini
        const today = new Date();
        const todayFormatted = formatDate(today);
        startDate.min = todayFormatted;
        endDate.min = todayFormatted;
        
        // Fungsi untuk format tanggal YYYY-MM-DD
        function formatDate(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }
        
        // Fungsi untuk menambah hari ke tanggal
        function addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        }
        
        // Fungsi untuk validasi
        const validateDateRange = () => {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                
                // Hitung selisih hari
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                // Validasi minimal 30 hari dan maksimal 180 hari (6 bulan)
                if (diffDays < 30) {
                    Swal.fire({
                        title: "Perhatian!",
                        text: "Durasi magang minimal 30 hari (1 bulan)",
                        icon: "warning",
                        confirmButtonColor: "#4361ee",
                        confirmButtonText: "Mengerti"
                    });
                    endDate.value = formatDate(addDays(start, 30));
                    return false;
                } else if (diffDays > 180) {
                    Swal.fire({
                        title: "Perhatian!",
                        text: "Durasi magang maksimal 180 hari (6 bulan)",
                        icon: "warning",
                        confirmButtonColor: "#4361ee",
                        confirmButtonText: "Mengerti"
                    });
                    endDate.value = formatDate(addDays(start, 180));
                    return false;
                }
                
                // Tampilkan durasi
                const durationInfo = document.createElement('div');
                durationInfo.className = 'duration-info';
                durationInfo.innerHTML = `<i class="fas fa-info-circle"></i> Durasi magang: <strong>${diffDays} hari</strong>`;
                
                // Hapus info lama jika ada
                const oldInfo = document.querySelector('.duration-info');
                if (oldInfo) oldInfo.remove();
                
                // Tambahkan info baru
                document.querySelector('.form-row').appendChild(durationInfo);
                
                return true;
            }
            return true;
        };

        // Event listener untuk validasi
        endDate.addEventListener('change', function() {
            if (startDate.value) {
                if (new Date(startDate.value) > new Date(endDate.value)) {
                    Swal.fire({
                        title: "Perhatian!",
                        text: "Tanggal selesai magang harus setelah tanggal mulai",
                        icon: "warning",
                        confirmButtonColor: "#4361ee",
                        confirmButtonText: "Mengerti"
                    });
                    endDate.value = '';
                } else {
                    validateDateRange();
                }
            }
        });
        
        startDate.addEventListener('change', function() {
            if (this.value) {
                // Set minimum end date to start date + 30 days
                const startDateObj = new Date(this.value);
                const minEndDate = addDays(startDateObj, 30);
                endDate.min = formatDate(minEndDate);
                
                // If current end date is less than minimum, update it
                if (endDate.value) {
                    const currentEndDate = new Date(endDate.value);
                    if (currentEndDate < minEndDate) {
                        endDate.value = formatDate(minEndDate);
                    }
                } else {
                    // If no end date is set, default to minimum (start date + 30 days)
                    endDate.value = formatDate(minEndDate);
                }
                
                validateDateRange();
            }
        });
        
        // Validasi pada form submit
        if (formElement) {
            formElement.addEventListener('submit', function(event) {
                if (startDate.value && endDate.value) {
                    const start = new Date(startDate.value);
                    const end = new Date(endDate.value);
                    const daysDiff = Math.floor((end - start) / (1000 * 60 * 60 * 24));
                    
                    // Check if the duration is less than 30 days
                    if (daysDiff < 30) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Durasi Magang Kurang',
                            text: 'Durasi magang minimal adalah 1 bulan (30 hari)',
                            icon: 'error',
                            confirmButtonColor: '#4361ee',
                        });
                    }
                }
            });
        }
    }
}

// Fungsi untuk animasi elemen saat scroll
function initScrollAnimations() {
    const elements = document.querySelectorAll('.card, .dashboard-header, .info-section');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-element');
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(element => {
        observer.observe(element);
        element.classList.add('pre-animate');
    });
}

// Toggle navbar scrolled class
function initNavbarScroll() {
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Fungsi untuk animasi dropdown menu
function initDropdownToggle() {
    const dropdownButton = document.querySelector('.dropdown-button');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Menutup dropdown jika mengklik di luar
        document.addEventListener('click', function() {
            if (dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
}

// Fungsi untuk validasi nomor telepon
function validatePhoneNumber() {
    const phoneInputs = document.querySelectorAll('input[name="no_hp_anggota[]"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Hapus karakter non-digit
            this.value = this.value.replace(/\D/g, '');
            
            // Tambahkan format otomatis
            if (this.value.length > 0) {
                // Pastikan dimulai dengan 0 atau 62
                if (!this.value.startsWith('0') && !this.value.startsWith('62')) {
                    if (this.value.startsWith('8')) {
                        this.value = '0' + this.value;
                    } else if (!isNaN(this.value)) {
                        this.value = '0' + this.value;
                    }
                }
                
                // Konversi 62 ke 0 jika terlalu panjang
                if (this.value.startsWith('62') && this.value.length > 10) {
                    this.value = '0' + this.value.substring(2);
                }
            }
            
            // Batasi panjang nomor maksimal 12 digit
            if (this.value.length > 12) {
                this.value = this.value.slice(0, 12);
                
                // Notifikasi jika melebihi batas
                showPhoneNotification(this, 'warning', 'Nomor telepon maksimal 12 digit');
            }
            
            // Periksa panjang nomor minimum
            if (this.value.length > 0 && this.value.length < 10) {
                this.classList.add('invalid-input');
                showPhoneNotification(this, 'error', 'Nomor telepon minimal 10 digit');
            } else {
                this.classList.remove('invalid-input');
                
                // Hapus pesan error jika ada
                const errorMsg = this.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('phone-error')) {
                    errorMsg.remove();
                }
            }
        });
        
        // Handle paste event
        input.addEventListener('paste', function(e) {
            // Get clipboard data
            let pasteData = (e.clipboardData || window.clipboardData).getData('text');
            
            // Clean the data - remove non-digit characters
            pasteData = pasteData.replace(/\D/g, '');
            
            // Format and limit length
            if (pasteData.length > 0) {
                if (pasteData.startsWith('62')) {
                    pasteData = '0' + pasteData.substring(2);
                } else if (!pasteData.startsWith('0')) {
                    pasteData = '0' + pasteData;
                }
                
                if (pasteData.length > 12) {
                    pasteData = pasteData.slice(0, 12);
                }
            }
            
            // Set the value and prevent default paste
            this.value = pasteData;
            e.preventDefault();
            
            // Trigger validation
            this.dispatchEvent(new Event('input'));
        });
    });
}

// Helper untuk menampilkan notifikasi pada nomor telepon
function showPhoneNotification(element, type, message, autoRemove = true) {
    // Hapus notifikasi yang ada
    const existingMsg = element.nextElementSibling;
    if (existingMsg && (existingMsg.classList.contains('phone-error') || existingMsg.classList.contains('phone-info'))) {
        existingMsg.remove();
    }
    
    // Buat pesan baru
    const msgElement = document.createElement('div');
    msgElement.classList.add(type === 'error' ? 'phone-error' : 'phone-info');
    msgElement.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}`;
    msgElement.style.color = type === 'error' ? 'red' : 'orange';
    msgElement.style.fontSize = '12px';
    msgElement.style.marginTop = '5px';
    
    // Tambahkan ke DOM
    element.parentNode.insertBefore(msgElement, element.nextSibling);
    
    // Auto remove untuk warning/info
    if (autoRemove && type !== 'error') {
        setTimeout(() => {
            if (msgElement.parentNode) {
                msgElement.remove();
            }
        }, 3000);
    }
}

// Fungsi untuk update nama file saat file dipilih
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : 'Belum ada file dipilih';
    const fileNameElement = document.getElementById('file-name');
    if (fileNameElement) {
        fileNameElement.textContent = fileName;
        fileNameElement.classList.add('file-selected');
        
        // Tambahkan ukuran file jika ada
        if (input.files[0]) {
            const fileSize = (input.files[0].size / 1024).toFixed(2) + ' KB';
            const sizeElement = document.createElement('span');
            sizeElement.className = 'file-size';
            sizeElement.textContent = fileSize;
            
            // Hapus size sebelumnya jika ada
            const oldSize = fileNameElement.querySelector('.file-size');
            if (oldSize) oldSize.remove();
            
            fileNameElement.appendChild(sizeElement);
        }
    }
}

// Validasi file PDF saat upload
function validateFileUpload() {
    const fileInput = document.querySelector('input[name="surat_pengantar"]');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // Validasi ukuran file (maksimal 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({
                        title: "File Terlalu Besar",
                        text: "Ukuran file maksimal adalah 5MB",
                        icon: "error",
                        confirmButtonColor: "#4361ee"
                    });
                    this.value = '';
                    updateFileName(this);
                    return;
                }
                
                // Validasi tipe file PDF
                if (file.type !== 'application/pdf') {
                    Swal.fire({
                        title: "Format File Salah",
                        text: "Silakan upload file dalam format PDF",
                        icon: "error",
                        confirmButtonColor: "#4361ee"
                    });
                    this.value = '';
                    updateFileName(this);
                    return;
                }
                
                // File valid, update nama file
                updateFileName(this);
            }
        });
    }
}

// Fungsi untuk menambah anggota kelompok
function tambahAnggota() {
    let container = document.getElementById('member-container');
    let anggotaRows = document.querySelectorAll('.member-row');
    
    if (container) {
        if (anggotaRows.length < 6) {
            let newRow = document.createElement('div');
            newRow.classList.add('member-row', 'fade-in');
            newRow.innerHTML = `
                <button type="button" class="remove-member" onclick="hapusAnggota(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="member-fields">
                    <div class="member-field">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_anggota[]" required>
                    </div>
                    <div class="member-field">
                        <label class="form-label">NIM/NIS</label>
                        <input type="text" class="form-control" name="nim_anggota[]" required>
                    </div>
                    <div class="member-field">
                        <label class="form-label">Jurusan</label>
                        <input type="text" class="form-control" name="jurusan[]" required>
                    </div>
                    <div class="member-field">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email_anggota[]" required>
                    </div>
                    <div class="member-field">
                        <label class="form-label">No HP (10-12 digit)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" class="form-control" name="no_hp_anggota[]" placeholder="Contoh: 08123456789">
                        </div>
                    </div>
                </div>`;
            container.appendChild(newRow);
            
            // Inisialisasi validasi untuk nomor telepon yang baru ditambahkan
            validatePhoneNumber();
            
            // Scroll ke baris baru
            setTimeout(() => {
                newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
            
            // Notif jika sudah hampir maksimum
            if (anggotaRows.length === 5) {
                Swal.fire({
                    title: "Perhatian",
                    text: "Ini adalah anggota terakhir yang dapat ditambahkan",
                    icon: "info",
                    confirmButtonColor: "#4361ee"
                });
            }
        } else {
            Swal.fire({
                title: "Batas Maksimum",
                text: "Maksimum anggota kelompok adalah 6 orang",
                icon: "error",
                confirmButtonColor: "#4361ee"
            });
        }
    }
}

// Fungsi untuk menghapus anggota kelompok
function hapusAnggota(button) {
    const row = button.parentElement;
    
    // Konfirmasi hapus
    Swal.fire({
        title: "Konfirmasi",
        text: "Apakah Anda yakin ingin menghapus anggota ini?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#4361ee",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            row.classList.add('fade-out');
            
            setTimeout(() => {
                row.remove();
            }, 300);
        }
    });
}

// Validasi form sebelum submit
function validateForm() {
    const form = document.getElementById('form-pendaftaran');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Cek minimal 1 anggota
            const anggotaRows = document.querySelectorAll('.member-row');
            if (anggotaRows.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: "Data Tidak Lengkap",
                    text: "Mohon tambahkan minimal 1 anggota kelompok",
                    icon: "error",
                    confirmButtonColor: "#4361ee"
                });
                return false;
            }
            
            // Cek validasi nomor telepon
            const phoneInputs = document.querySelectorAll('input[name="no_hp_anggota[]"]');
            let phoneValid = true;
            
            phoneInputs.forEach(input => {
                if (input.value && input.value.length < 10) {
                    phoneValid = false;
                    input.classList.add('invalid-input');
                    showPhoneNotification(input, 'error', 'Nomor telepon minimal 10 digit', false);
                }
            });
            
            if (!phoneValid) {
                e.preventDefault();
                Swal.fire({
                    title: "Validasi Gagal",
                    text: "Nomor telepon harus minimal 10 digit",
                    icon: "error",
                    confirmButtonColor: "#4361ee"
                });
                return false;
            }
            
            // Tampilkan loading saat submit
            Swal.fire({
                title: 'Memproses Pendaftaran',
                html: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            return true;
        });
    }
}

// Fungsi untuk mengelola countdown timer dengan penjelasan
function initPendaftaranTertutup() {
    const pendaftaranTertutup = document.querySelector('.pendaftaran-tertutup');
    if (pendaftaranTertutup) {
        // Activate countdown if available
        const countdownElement = document.getElementById('countdown-timer');
        if (countdownElement) {
            const targetDateStr = countdownElement.getAttribute('data-target');
            if (!targetDateStr) {
                console.error('Countdown timer tidak memiliki data-target');
                return;
            }
            
            const targetDate = new Date(targetDateStr);
            
            function updateCountdown() {
                const now = new Date();
                const distance = targetDate - now;
                
                // Time calculations
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Display countdown
                countdownElement.innerHTML = `
                    <div class="countdown-item"><span>${days}</span>Hari</div>
                    <div class="countdown-item"><span>${hours}</span>Jam</div>
                    <div class="countdown-item"><span>${minutes}</span>Menit</div>
                    <div class="countdown-item"><span>${seconds}</span>Detik</div>
                `;
                
                // Tambahkan penjelasan jika belum ada
                if (!document.getElementById('countdown-explanation')) {
                    const explanationElement = document.createElement('div');
                    explanationElement.id = 'countdown-explanation';
                    explanationElement.className = 'countdown-explanation';
                    explanationElement.innerHTML = `
                        <p>Pendaftaran magang periode selanjutnya akan dibuka pada 
                        <strong>${targetDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</strong>.
                        Waktu di atas menunjukkan sisa waktu hingga pendaftaran dibuka.</p>
                    `;
                    countdownElement.parentNode.insertBefore(explanationElement, countdownElement.nextSibling);
                }
                
                // Check if countdown is finished
                if (distance < 0) {
                    clearInterval(countdownInterval);
                    countdownElement.innerHTML = `<div class="countdown-finished">Pendaftaran Telah Dibuka!</div>`;
                    
                    // Update penjelasan
                    const explanation = document.getElementById('countdown-explanation');
                    if (explanation) {
                        explanation.innerHTML = '<p>Pendaftaran magang periode selanjutnya telah dibuka. Silakan muat ulang halaman untuk mulai mendaftar.</p>';
                    }
                    
                    // Refresh page after 3 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                }
            }
            
            // Initial call
            updateCountdown();
            
            // Update countdown every second
            const countdownInterval = setInterval(updateCountdown, 1000);
        }
        
        // Activate slick carousel if available
        const carousel = document.querySelector('.carousel-container');
        if (carousel && typeof $.fn.slick !== 'undefined') {
            $('.carousel-container').slick({
                dots: true,
                infinite: true,
                speed: 500,
                fade: true,
                cssEase: 'linear',
                autoplay: true,
                autoplaySpeed: 3000
            });
        }
    }
}

// Initialize all functions when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    validateDates();
    initScrollAnimations();
    initNavbarScroll();
    initDropdownToggle();
    validatePhoneNumber();
    validateFileUpload();
    validateForm();
    initPendaftaranTertutup();
    
    // Initialize form with animation
    const form = document.querySelector('form');
    if(form) {
        form.classList.add('fade-in');
    }
});