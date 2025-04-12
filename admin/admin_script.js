document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const body = document.querySelector('body.admin-body'); // Target body directly
    const sidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && body) {
        sidebarToggle.addEventListener('click', function() {
            // Toggle a class on the body to control sidebar visibility/state
            body.classList.toggle('sidebar-collapsed');
        });
    }

    // Optional: Add hover effect to expand collapsed sidebar temporarily
    if (sidebar) {
        sidebar.addEventListener('mouseenter', () => {
            if (body.classList.contains('sidebar-collapsed')) {
                body.classList.add('sidebar-hover-expand');
            }
        });
         sidebar.addEventListener('mouseleave', () => {
            if (body.classList.contains('sidebar-collapsed')) {
                 body.classList.remove('sidebar-hover-expand');
            }
        });
    }

});


