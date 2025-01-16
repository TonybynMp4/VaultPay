document.addEventListener('DOMContentLoaded', function() {
    const openButton = document.getElementById('nav_open');
    const asideNav = document.getElementById('aside_nav');

    console.log(openButton);
    console.log(asideNav);

    openButton.addEventListener('click', function() {
        asideNav.style.display = asideNav.style.display === 'block' ? 'none' : 'block';
    });

    const closeButton = document.getElementById('nav_close');
    closeButton.addEventListener('click', function() {
        asideNav.style.display = 'none';
    });
});