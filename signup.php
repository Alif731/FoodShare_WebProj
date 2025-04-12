<link rel="stylesheet" href="css/signup.css">



<?php include 'includes/header.php'; ?>

<div class="signup-container">
    <h1>Join Us</h1>
    <p>Choose your role and help us make a difference!</p>
    <form action="" method="GET" id="role-selection-form">
        <select id="role-selection" name="role" onchange="redirectToPage()" class="signup-dropdown">
            <option value="" disabled selected>Select an option</option>
            <option value="donor_register.php">I want to donate food</option>
            <option value="volunteer_register.php">I want to volunteer</option>
        </select>
    </form>
</div>

<script>
    function redirectToPage() {
        const selectedValue = document.getElementById('role-selection').value;
        if (selectedValue) {
            window.location.href = selectedValue;
        }
    }
</script>

<?php include 'includes/footer.php'; ?>