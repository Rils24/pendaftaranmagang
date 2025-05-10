/**
 * dropdown-kampus.js - Script untuk dropdown kampus yang lebih bagus
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elemen-elemen yang diperlukan
    const selectContainer = document.querySelector('.select-container');
    const selectBox = document.getElementById('kampus-dropdown');
    const searchInput = document.getElementById('search-kampus-input');
    const optionsList = document.getElementById('kampus-options');
    const manualInput = document.getElementById('asal-kampus-manual');
    const selectedTextElement = document.querySelector('.selected-text');
    
    if (!selectContainer || !selectBox || !searchInput || !optionsList || !manualInput) {
        console.error('Beberapa elemen tidak ditemukan');
        return;
    }

    // Universitas populer (bisa disesuaikan)
    const popularUniversities = [
        'Universitas Indonesia',
        'Institut Teknologi Bandung',
        'Universitas Gadjah Mada',
        'Institut Teknologi Sepuluh Nopember',
        'Universitas Airlangga',
        'Universitas Diponegoro',
        'Universitas Brawijaya',
        'Universitas Padjadjaran'
    ];
    
    // Ambil semua opsi universitas dari select asli dan bersihkan dari karakter r/n
    const universities = [];
    Array.from(selectBox.options).forEach(option => {
        if (option.value !== '' && option.value !== 'other') {
            const cleanText = option.textContent.trim().replace(/[\r\n\t]+/g, '');
            universities.push({
                value: option.value.trim().replace(/[\r\n\t]+/g, ''),
                text: cleanText,
                isPopular: popularUniversities.includes(cleanText)
            });
        }
    });
    
    // Fungsi untuk menampilkan opsi yang cocok dengan pencarian
    function filterOptions() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        // Bersihkan opsi yang ada
        optionsList.innerHTML = '';
        
        // Opsi default (-- Pilih Kampus --)
        const defaultOption = document.createElement('div');
        defaultOption.className = 'custom-option';
        defaultOption.dataset.value = '';
        defaultOption.textContent = '-- Pilih Kampus --';
        defaultOption.addEventListener('click', () => selectOption('', '-- Pilih Kampus --'));
        optionsList.appendChild(defaultOption);
        
        // Filter dan tambahkan opsi universitas
        let matchCount = 0;
        let filteredUniversities = universities
            .filter(uni => searchTerm === '' || uni.text.toLowerCase().includes(searchTerm))
            .sort((a, b) => {
                // Urutkan populer dulu, lalu alphabetis
                if (a.isPopular && !b.isPopular) return -1;
                if (!a.isPopular && b.isPopular) return 1;
                return a.text.localeCompare(b.text);
            });
        
        // Tambahkan counter hasil pencarian jika mencari
        if (searchTerm !== '') {
            const countElement = document.createElement('div');
            countElement.className = 'search-count';
            countElement.textContent = `${filteredUniversities.length} hasil ditemukan`;
            optionsList.appendChild(countElement);
        }
        
        // Tambahkan opsi yang cocok
        filteredUniversities.forEach(uni => {
            const option = document.createElement('div');
            option.className = 'custom-option';
            option.dataset.value = uni.value;
            
            // Highlight teks yang cocok dengan pencarian dan tambahkan badge jika populer
            let optionContent = uni.text;
            if (searchTerm !== '') {
                const regex = new RegExp(`(${searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi');
                optionContent = uni.text.replace(regex, '<span class="highlight">$1</span>');
            }
            
            // Tambahkan badge jika kampus populer
            if (uni.isPopular) {
                optionContent += ' <span class="popular-badge">Populer</span>';
            }
            
            option.innerHTML = optionContent;
            option.addEventListener('click', () => selectOption(uni.value, uni.text));
            optionsList.appendChild(option);
            matchCount++;
        });
        
        // Opsi "Lainnya (Ketik Manual)"
        const otherOption = document.createElement('div');
        otherOption.className = 'custom-option';
        otherOption.dataset.value = 'other';
        otherOption.innerHTML = '<i class="fas fa-plus-circle"></i> Lainnya (Ketik Manual)';
        otherOption.addEventListener('click', () => selectOption('other', 'Lainnya (Ketik Manual)'));
        optionsList.appendChild(otherOption);
        
        // Tambahkan pesan jika tidak ada hasil
        if (matchCount === 0 && searchTerm !== '') {
            const noResult = document.createElement('div');
            noResult.className = 'no-results';
            noResult.innerHTML = '<i class="fas fa-search"></i> Tidak ada kampus yang cocok';
            optionsList.appendChild(noResult);
        }
    }
    
    // Fungsi untuk memilih opsi
    function selectOption(value, text) {
        // Update tampilan select box
        selectedTextElement.textContent = text === 'Lainnya (Ketik Manual)' ? 'Kampus Lainnya' : text;
        selectBox.value = value;
        
        // Simulasi event change
        const event = new Event('change');
        selectBox.dispatchEvent(event);
        
        // Tutup dropdown
        selectContainer.classList.remove('open');
        
        // Reset input pencarian
        searchInput.value = '';
        
        // Handle opsi "other"
        if (value === 'other') {
            manualInput.style.display = 'block';
            manualInput.value = searchInput.value !== '' ? searchInput.value : '';
            manualInput.focus();
            
            // Efek visual
            manualInput.classList.add('highlight-animation');
            setTimeout(() => {
                manualInput.classList.remove('highlight-animation');
            }, 1000);
        } else {
            manualInput.style.display = 'none';
            manualInput.value = value;
        }
    }
    
    // Buka dropdown saat mengklik select box
    selectBox.addEventListener('change', function() {
        if (this.value === '') {
            selectedTextElement.textContent = '-- Pilih Kampus --';
        } else if (this.value === 'other') {
            selectedTextElement.textContent = 'Kampus Lainnya';
        } else {
            const selectedOption = this.options[this.selectedIndex];
            selectedTextElement.textContent = selectedOption.textContent.trim().replace(/[\r\n\t]+/g, '');
        }
    });
    
    // Buka dropdown saat mengklik select box
    selectContainer.addEventListener('click', function(e) {
        // Jika yang diklik bukan search input, toggle dropdown
        if (e.target !== searchInput) {
            selectContainer.classList.toggle('open');
            if (selectContainer.classList.contains('open')) {
                searchInput.focus();
                filterOptions();
            }
        }
    });
    
    // Filter opsi saat mengetik di search input
    searchInput.addEventListener('input', filterOptions);
    
    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const options = optionsList.querySelectorAll('.custom-option');
        const focusedOption = optionsList.querySelector('.custom-option.focused');
        const focusIndex = focusedOption ? Array.from(options).indexOf(focusedOption) : -1;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (focusIndex < options.length - 1) {
                    if (focusedOption) focusedOption.classList.remove('focused');
                    options[focusIndex + 1].classList.add('focused');
                    options[focusIndex + 1].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                if (focusIndex > 0) {
                    if (focusedOption) focusedOption.classList.remove('focused');
                    options[focusIndex - 1].classList.add('focused');
                    options[focusIndex - 1].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                break;
                
            case 'Enter':
                e.preventDefault();
                if (focusedOption) {
                    focusedOption.click();
                } else if (this.value.trim() !== '') {
                    // Jika tidak ada yang terfokus tapi ada input, pilih "Lainnya"
                    const otherOption = optionsList.querySelector('.custom-option[data-value="other"]');
                    if (otherOption) otherOption.click();
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                selectContainer.classList.remove('open');
                break;
        }
    });
    
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(e) {
        if (!selectContainer.contains(e.target)) {
            selectContainer.classList.remove('open');
            
            // Jika dropdown ditutup dan tidak ada yang dipilih tapi ada pencarian
            if (selectBox.value === '' && searchInput.value.trim() !== '') {
                selectOption('other', 'Lainnya (Ketik Manual)');
                manualInput.value = searchInput.value.trim();
            }
            
            // Reset input pencarian
            searchInput.value = '';
            
            // Hapus fokus dari semua opsi
            const options = optionsList.querySelectorAll('.custom-option.focused');
            options.forEach(option => option.classList.remove('focused'));
        }
    });
    
    // Mencegah menutup dropdown saat klik di dalam search input
    searchInput.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Validasi input manual untuk mencegah duplikasi
    manualInput.addEventListener('blur', function() {
        const currentValue = this.value.trim();
        if (currentValue === '') return;
        
        let isDuplicate = false;
        universities.forEach(uni => {
            if (uni.value.toLowerCase() === currentValue.toLowerCase()) {
                isDuplicate = true;
                selectBox.value = uni.value;
                selectedTextElement.textContent = uni.text;
                
                // Simulasi event change
                const event = new Event('change');
                selectBox.dispatchEvent(event);
                
                this.style.display = 'none';
                
                // Tampilkan pesan bahwa data ditemukan di database
                showNotification('Kampus ditemukan di database', 'info');
            }
        });
    });
    
    // Inicjalizacja - załaduj opcje
    filterOptions();
});

/**
 * Menampilkan notifikasi menggunakan SweetAlert2
 */
function showNotification(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            text: message,
            icon: type,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}