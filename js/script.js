document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.stat-item .count');
    const speed = 200; // Lower number = faster animation

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-target'); // Get target value
        const count = +counter.innerText; // Current value shown

        // Calculate increment
        const inc = Math.ceil(target / speed);

        // If current count is less than target, increment
        if (count < target) {
            counter.innerText = Math.min(count + inc, target); // Prevent overshooting
            setTimeout(() => animateCounter(counter), 10); // Call recursively
        } else {
            counter.innerText = target.toLocaleString(); // Ensure final value is formatted if needed
        }
    };

    // --- Intersection Observer Option (Recommended) ---
    // This starts the animation only when the stats section is visible

    const statsSection = document.querySelector('.stats');
    let animationStarted = false; // Flag to ensure animation runs only once

    if (statsSection) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                // Check if the stats section is intersecting and animation hasn't started
                if (entry.isIntersecting && !animationStarted) {
                    counters.forEach(counter => {
                        counter.innerText = '0'; // Start from 0
                        animateCounter(counter);
                    });
                    animationStarted = true; // Set flag
                    observer.unobserve(statsSection); // Stop observing once animated
                }
            });
        }, {
            threshold: 0.5 // Trigger when 50% of the element is visible
        });

        observer.observe(statsSection);
    } else {
        // Fallback if Intersection Observer isn't supported or section not found
        // (Or just run animation immediately without observer)
        // counters.forEach(counter => {
        //     counter.innerText = '0';
        //     animateCounter(counter);
        // });
    }

});