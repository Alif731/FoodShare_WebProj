document.addEventListener("DOMContentLoaded", () => {
    const onceBtn = document.getElementById("onceBtn");
    const monthlyBtn = document.getElementById("monthlyBtn");
    const donationType = document.getElementById("donationType");

    // Toggle between "Once" and "Monthly"
    monthlyBtn.addEventListener("click", () => {
        onceBtn.classList.remove("active");
        monthlyBtn.classList.add("active");
        donationType.textContent = "monthly";
        updateCard(1500, "This can provide food to <strong>16 people</strong> in need every month");
    });

    onceBtn.addEventListener("click", () => {
        monthlyBtn.classList.remove("active");
        onceBtn.classList.add("active");
        donationType.textContent = "once";
        updateCard(1500, "This can provide food to <strong>50 people</strong> in need");
    });
});

// Function to update the center card and button highlighting
function updateCard(amount, impact) {
    const donationAmount = document.getElementById("donationAmount");
    const impactText = document.getElementById("impactText");

    // Update center card content
    donationAmount.textContent = amount;
    impactText.innerHTML = impact;

    // Remove 'selected' class from all buttons
    const buttons = document.querySelectorAll('.donation-option');
    buttons.forEach(btn => btn.classList.remove('selected'));

    // Add 'selected' class to the clicked button
    event.target.classList.add('selected');
}

// Handle custom amount input
function updateCustomCard() {
    const customAmount = document.getElementById("customAmount").value;
    const donationAmount = document.getElementById("donationAmount");
    const impactText = document.getElementById("impactText");

    if (customAmount && customAmount > 0) {
        donationAmount.textContent = customAmount;
        impactText.innerHTML = `You can make a significant impact with AED <strong>${customAmount}</strong>`;

        // Remove 'selected' class from preset buttons
        document.querySelectorAll('.donation-option').forEach(btn => {
            btn.classList.remove('selected');
        });
    }
}
// Function to update donation amount and meals dynamically
function updateDonation(amount) {
    const donationAmount = document.getElementById('donationAmount');
    const mealsText = document.getElementById('mealsText');

    // Update amount
    donationAmount.textContent = `₹ ${amount}`;

    // Calculate meals based on amount
    let meals = Math.floor(amount / 30);  // Assuming ₹30 provides 1 meal
    mealsText.innerHTML = `This can provide food to <strong>${meals} people</strong> in need`;

    // Highlight the selected button
    document.querySelectorAll('.donation-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

// Function for custom amount
function updateCustomDonation() {
    const customInput = document.getElementById('customAmount');
    const donationAmount = document.getElementById('donationAmount');
    const mealsText = document.getElementById('mealsText');

    let amount = parseFloat(customInput.value) || 0;
    donationAmount.textContent = `₹ ${amount}`;

    let meals = Math.floor(amount / 30);
    mealsText.innerHTML = `This can provide food to <strong>${meals} people</strong> in need`;
}

// Function to switch between Once and Monthly tabs
function switchTab(tab) {
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(btn => btn.classList.remove('active'));

    if (tab === 'once') {
        tabs[0].classList.add('active');
    } else {
        tabs[1].classList.add('active');
    }
}
