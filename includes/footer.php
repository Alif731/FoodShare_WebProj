</main> <!-- Main content ends here -->

<!-- Footer using Bootstrap Grid -->
<footer class="footer mt-auto"> <!-- Styles defined in header <style> or custom CSS -->
    <div class="container">
        <div class="row gy-4"> <!-- gy-4 adds vertical gutter spacing on smaller screens -->

            <!-- Left Section -->
            <div class="col-lg-4 col-md-6 footer-brand">
                <p>FoodShare Connect</p>
                <img src="./images/Footer_favi.svg" alt="FoodShare Connect Logo" width="70" height="70">
            </div>

            <!-- Center Links -->
            <div class="col-lg-4 col-md-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="index.php#faqs">FAQs</a></li>
                    <li><a href="#">Contact us</a></li>
                    <li><a href="#">Terms of Use</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Right Section Placeholder (Optional) -->
            <div class="col-lg-4 d-none d-lg-block">
                 <!-- Could add newsletter signup or other info here -->
            </div>

        </div> <!-- /.row -->

        <!-- Social Media & Copyright -->
        <div class="footer-bottom">
            <div class="social-icons mb-3">
                <!-- Using Font Awesome icons included in header -->
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="#" aria-label="Email"><i class="fas fa-envelope"></i></a>
            </div>
            <p>FoodShare Connect © <?php echo date("Y"); ?></p> <!-- Updated Copyright -->
        </div>

    </div> <!-- /.container -->
</footer>

<!-- Bootstrap JS Bundle with Popper (Load Once) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Your Custom Frontend JS (for counter etc.) -->
<script src="js/script.js"></script>

</body>
</html>