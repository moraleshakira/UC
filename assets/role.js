document.getElementById('role').addEventListener('change', function() {
    const selectedRole = this.value;
    const rows = document.querySelectorAll('#userTableBody tr');

    rows.forEach(row => {
        if (selectedRole === '' || row.getAttribute('data-role') === selectedRole) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
