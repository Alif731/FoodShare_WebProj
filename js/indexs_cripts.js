function scrollToHeader() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Hide/Show button on scroll
const scrollBtn = document.getElementById('scrollToTopBtn');

window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        scrollBtn.classList.remove('hidden');
    } else {
        scrollBtn.classList.add('hidden');
    }
});