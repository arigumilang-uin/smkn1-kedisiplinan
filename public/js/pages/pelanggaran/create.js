/**
 * Pelanggaran Create Page - Record Violation
 * Manages student selection, violation selection, and form submission
 */

let currentFilterTopic = 'all';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    initStudentFilters();
    initViolationSearch();
    initFilterPills();
    initFileInput();
    setLocalTimeDefault();
});

/**
 * Set default time input to browser local time if there is no old value provided by server
 */
function setLocalTimeDefault() {
    const timeInput = document.getElementById('jamKejadian') || document.querySelector('input[name="jam_kejadian"]');
    if (!timeInput) return;

    // If server provided old value (validation), do not override
    const hasOld = timeInput.dataset.hasOld === '1';
    if (hasOld) return;

    // Set browser local time in HH:mm
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    timeInput.value = `${hh}:${mm}`;
}

/**
 * Initialize student filters
 */
function initStudentFilters() {
    const filterTingkat = document.getElementById('filterTingkat');
    const filterJurusan = document.getElementById('filterJurusan');
    const filterKelas = document.getElementById('filterKelas');
    const searchInput = document.getElementById('searchSiswa');
    
    if (filterTingkat) filterTingkat.addEventListener('change', applyStudentFilters);
    if (filterJurusan) filterJurusan.addEventListener('change', applyStudentFilters);
    if (filterKelas) filterKelas.addEventListener('change', applyStudentFilters);
    if (searchInput) searchInput.addEventListener('keyup', applyStudentFilters);
}

/**
 * Apply student filters
 */
function applyStudentFilters() {
    const container = document.getElementById('studentListContainer');
    const students = container.querySelectorAll('.student-item');
    
    const tingkat = document.getElementById('filterTingkat').value;
    const jurusan = document.getElementById('filterJurusan').value;
    const kelas = document.getElementById('filterKelas').value;
    const search = document.getElementById('searchSiswa').value.toLowerCase();
    
    let visibleCount = 0;
    
    students.forEach(student => {
        let show = true;
        
        if (tingkat && student.dataset.tingkat !== tingkat) show = false;
        if (jurusan && student.dataset.jurusan !== jurusan) show = false;
        if (kelas && student.dataset.kelas !== kelas) show = false;
        if (search && !student.dataset.search.includes(search)) show = false;
        
        student.style.display = show ? 'flex' : 'none';
        if (show) visibleCount++;
    });
    
    const noMsg = container.querySelector('#noResultMsg');
    if (noMsg) noMsg.style.display = visibleCount === 0 ? 'block' : 'none';
}

/**
 * Select student
 */
function selectStudent(element) {
    // Toggle selection for multi-select
    element.classList.toggle('selected');
    const checkbox = element.querySelector('input[type="checkbox"]');
    if (checkbox) checkbox.checked = !checkbox.checked;
    updateSelectedStudentCount();
}

/**
 * Reset student filters
 */
function resetFilters() {
    document.getElementById('filterTingkat').value = '';
    document.getElementById('filterJurusan').value = '';
    document.getElementById('filterKelas').value = '';
    document.getElementById('searchSiswa').value = '';
    applyStudentFilters();
}

/**
 * Initialize violation search
 */
function initViolationSearch() {
    const searchInput = document.getElementById('searchPelanggaran');
    if (searchInput) {
        searchInput.addEventListener('keyup', applyViolationSearch);
    }
}

/**
 * Apply violation search
 */
function applyViolationSearch() {
    const violations = document.querySelectorAll('.violation-item');
    if (violations.length === 0) return;
    
    const search = document.getElementById('searchPelanggaran').value.toLowerCase();
    
    let visibleCount = 0;
    
    violations.forEach(violation => {
        const nama = violation.dataset.nama || '';
        const kategori = violation.dataset.kategori || '';
        
        let show = !search || nama.includes(search) || kategori.includes(search);
        
        // Apply category filter if active
        if (currentFilterTopic !== 'all' && currentFilterTopic !== 'berat') {
            show = show && kategori === currentFilterTopic;
        } else if (currentFilterTopic === 'berat') {
            show = show && kategori === 'berat';
        }
        
        violation.style.display = show ? 'flex' : 'none';
        if (show) visibleCount++;
    });
    
    const noMsg = document.getElementById('noViolationMsg');
    if (noMsg) noMsg.style.display = visibleCount === 0 ? 'block' : 'none';
}

/**
 * Select violation
 */
function selectViolation(element) {
    // Toggle selection for multi-select
    element.classList.toggle('selected');
    const checkbox = element.querySelector('input[type="checkbox"]');
    if (checkbox) checkbox.checked = !checkbox.checked;
}

function updateSelectedStudentCount() {
    const countEl = document.getElementById('countSiswa');
    const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
    if (countEl) countEl.textContent = `${checked} dipilih`;
}

// Ensure clicking the checkbox itself does not immediately re-toggle twice
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.student-item').forEach(item => {
        const cb = item.querySelector('input[type="checkbox"]');
        if (cb) cb.addEventListener('click', function(e){
            e.stopPropagation();
            item.classList.toggle('selected');
            updateSelectedStudentCount();
        });
    });

    document.querySelectorAll('.violation-item').forEach(item => {
        const cb = item.querySelector('input[type="checkbox"]');
        if (cb) cb.addEventListener('click', function(e){ e.stopPropagation(); });
    });
});

/**
 * Initialize filter pills
 */
function initFilterPills() {
    // Filter pills will be handled by setFilterTopic function
    // which is called directly from onclick in the blade template
}

/**
 * Set filter topic
 */
function setFilterTopic(topic, element) {
    const container = document.querySelector('.filter-pills');
    const buttons = container.querySelectorAll('.btn');
    
    buttons.forEach(b => b.classList.remove('active'));
    element.classList.add('active');
    
    currentFilterTopic = topic;
    applyViolationSearch();
}

/**
 * Initialize file input handler
 */
function initFileInput() {
    const fileInput = document.getElementById('customFile');
    if (fileInput && typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
    }
}

// ----- Confirmation + Submit handling -----
// Intercept form submit (covers Enter key and button)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPelanggaran');
    const confirmBtn = document.getElementById('btnConfirmSubmit');

    // guard flag to allow actual submission after confirmation
    window.__pelanggaranConfirmed = false;

    if (form) {
        form.addEventListener('submit', function(e) {
            if (window.__pelanggaranConfirmed) {
                // allow submission to proceed once confirmed
                return;
            }

            e.preventDefault();

            // collect selected students and violations
            const students = Array.from(document.querySelectorAll('.siswa-checkbox:checked')).map(cb => {
                const card = cb.closest('.student-item');
                return card ? card.querySelector('.font-weight-bold').innerText.trim() : cb.value;
            });
            const violations = Array.from(document.querySelectorAll('.pelanggaran-checkbox:checked')).map(cb => {
                const card = cb.closest('.violation-item');
                return card ? card.querySelector('.font-weight-bold').innerText.trim() : cb.value;
            });

            if (students.length === 0 || violations.length === 0) {
                alert('Pilih minimal satu siswa dan satu jenis pelanggaran sebelum melanjutkan.');
                return;
            }

            // populate modal lists
            const studentsList = document.getElementById('confirmStudents');
            const violationsList = document.getElementById('confirmViolations');
            const confirmTime = document.getElementById('confirmTime');
            const confirmKeterangan = document.getElementById('confirmKeterangan');

            if (studentsList) {
                studentsList.innerHTML = '';
                students.forEach(s => { const li = document.createElement('li'); li.textContent = s; studentsList.appendChild(li); });
            }

            if (violationsList) {
                violationsList.innerHTML = '';
                violations.forEach(v => { const li = document.createElement('li'); li.textContent = v; violationsList.appendChild(li); });
            }

            const date = (document.querySelector('input[name="tanggal_kejadian"]') || {}).value || '';
            const time = (document.querySelector('input[name="jam_kejadian"]') || {}).value || '';
            if (confirmTime) confirmTime.textContent = `${date} ${time}`;

            if (confirmKeterangan) confirmKeterangan.textContent = document.querySelector('textarea[name="keterangan"]')?.value || '-';

            // show modal or fallback alert
            if (typeof $ === 'function' && typeof $.fn.modal === 'function') {
                $('#confirmModal').modal('show');
            } else {
                if (!confirm('Konfirmasi:\nSiswa: ' + students.join(', ') + '\nPelanggaran: ' + violations.join(', ') + '\nWaktu: ' + date + ' ' + time + '\n\nTekan OK untuk menyimpan.')) {
                    return; // user cancelled
                }

                // user confirmed via native confirm
                window.__pelanggaranConfirmed = true;
                form.submit();
            }
        });
    }

    if (confirmBtn && form) {
        confirmBtn.addEventListener('click', function() {
            // disable to prevent double submit
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // set flag then submit form; submit handler will allow it
            window.__pelanggaranConfirmed = true;
            form.submit();
        });
    }
});
