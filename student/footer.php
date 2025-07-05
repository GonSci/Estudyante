<?php
// footer.php - Student Portal Footer
?>

<footer class="mt-5 py-4" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5>Summit Crest Academy</h5>
                <p class="mb-0">123 Rizal Avenue</p>
                <p class="mb-0">Barangay Batingan, Binangonan Rizal</p>
                <p class="mb-0">Phone: (123) 456-7890</p>
                <p class="mb-0">Email: info@summitcrest.edu</p>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="dashboard.php" class="text-white">Home</a></li>
                    <li><a href="profile.php" class="text-white">My Profile</a></li>
                    <li><a href="view-courses.php" class="text-white">My Courses</a></li>
                    <li><a href="register-course.php" class="text-white">Course Registration</a></li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h5>Connect With Us</h5>
                <div class="d-flex mt-2">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
                <div class="mt-3">
                    <img src="../assets/login_logo_white.png" alt="School Logo" style="height: 50px;">
                </div>
            </div>
        </div>
        
        <hr class="my-3 bg-light">
        
        <div class="row">
            <div class="col-md-6 mb-2 mb-md-0">
                <p class="mb-0">&copy; <?= date('Y') ?> Summit Crest Academy. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="#" class="text-white me-3">Privacy Policy</a>
                <a href="#" class="text-white me-3">Terms of Service</a>
                <a href="#" class="text-white">Contact Us</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<!-- Navbar JS -->
<script src="css/navbar.js"></script>

<!-- Custom Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    
    var backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.onscroll = function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                backToTopBtn.style.display = "block";
            } else {
                backToTopBtn.style.display = "none";
            }
        };
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>


<button onclick="scrollToTop()" id="backToTopBtn" title="Go to top" 
        style="display: none; position: fixed; bottom: 20px; right: 30px; z-index: 99; border: none; outline: none; 
               background-color: hsl(217, 65.90%, 25.30%); color: white; cursor: pointer; padding: 10px 15px; 
               border-radius: 5px; font-size: 16px;">
    <i class="fas fa-arrow-up"></i>
</button>

</body>
</html>