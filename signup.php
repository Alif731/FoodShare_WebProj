<?php require_once 'includes/header.php'; // Include Bootstrap header ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card shadow-sm text-center">
                 <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Join Us!</h4>
                </div>
                <div class="card-body p-4 p-md-5"> <!-- More padding -->
                    <h5 class="card-title mb-3">Become a Part of FoodShare Connect</h5>
                    <p class="card-text mb-4">Choose how you'd like to contribute and help us make a difference in the community.</p>

                    <!-- Form is mainly for the select appearance, action handled by JS -->
                    <form action="" method="GET" id="role-selection-form">
                        <div class="mb-3">
                            <label for="role-selection" class="form-label fw-bold">I want to...</label>
                             <!-- Use Bootstrap's form-select -->
                            <select id="role-selection" name="role" class="form-select form-select-lg text-center" onchange="redirectToPage()">
                                <option value="" disabled selected>-- Select an Option --</option>
                                <option value="donor_register.php">Donate Food</option>
                                <option value="volunteer_register.php">Volunteer My Time</option>
                            </select>
                        </div>
                        <!-- No submit button needed as JS handles change -->
                    </form>

                     <p class="mt-4 mb-0">Already have an account? <a href="login.php">Login Here</a></p>

                </div> <!-- /.card-body -->
            </div> <!-- /.card -->

        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<script>
    // Simple redirect function
    function redirectToPage() {
        const selectElement = document.getElementById('role-selection');
        const selectedValue = selectElement.value;
        if (selectedValue) {
            // Prevent navigating if the placeholder is re-selected (though it's disabled)
            if (selectElement.selectedIndex > 0) {
                 window.location.href = selectedValue;
            }
        }
    }
</script>

<?php require_once 'includes/footer.php'; // Include Bootstrap footer ?>