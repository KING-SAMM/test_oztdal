
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    const hamburger = document.querySelector('.hamburger');

    // Toggle the visibility of the navigation menu
    navLinks.classList.toggle('show');

    // Toggle the hamburger to "X" animation
    hamburger.classList.toggle('active');
}
