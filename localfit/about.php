<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="text-center mb-5">About LocalFit</h1>
            
            <div class="card mb-5">
                <div class="card-body">
                    <h2 class="card-title">Our Mission</h2>
                    <p class="card-text lead">
                        LocalFit creates an accessible online marketplace dedicated to local clothing brands, connecting independent fashion 
                        creators with consumers who value unique, locally-produced products.
                    </p>
                    
                    <p>
                        In a market dominated by large fashion retailers, LocalFit increases visibility and sales for small-scale local clothing 
                        brands, helping them reach new customers while providing shoppers with a convenient way to discover and purchase unique, 
                        locally-produced fashion items in one place.
                    </p>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">For Shoppers</h3>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Discover unique, locally-made clothing</li>
                                <li class="list-group-item">Support your local economy and community</li>
                                <li class="list-group-item">Access multiple local brands in one convenient place</li>
                                <li class="list-group-item">Find fashion items that stand out from mass-produced alternatives</li>
                                <li class="list-group-item">Connect directly with the creators behind the brands</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">For Brands</h3>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Increase your brand's visibility</li>
                                <li class="list-group-item">Reach new customers without high marketing costs</li>
                                <li class="list-group-item">Join a community of like-minded local fashion creators</li>
                                <li class="list-group-item">Focus on creating while we handle the e-commerce platform</li>
                                <li class="list-group-item">Tell your brand's unique story to a targeted audience</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Our Team</h2>
                    <p>
                        LocalFit was developed by a team of passionate developers committed to supporting local businesses and 
                        creating technology solutions that foster community growth:
                    </p>
                    
                    <div class="row text-center mt-4">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <img src="assets/images/placeholder.jpg" class="img-fluid rounded-circle" style="width: 120px; height: 120px;">
                            </div>
                            <h5>Mohamed Ashraf Hamdy</h5>
                            <p class="text-muted">Developer</p>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <img src="assets/images/placeholder.jpg" class="img-fluid rounded-circle" style="width: 120px; height: 120px;">
                            </div>
                            <h5>Mohamed Wael</h5>
                            <p class="text-muted">Developer</p>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <img src="assets/images/placeholder.jpg" class="img-fluid rounded-circle" style="width: 120px; height: 120px;">
                            </div>
                            <h5>Omar Emad</h5>
                            <p class="text-muted">Developer</p>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <img src="assets/images/placeholder.jpg" class="img-fluid rounded-circle" style="width: 120px; height: 120px;">
                            </div>
                            <h5>Mohamed Mahmoud</h5>
                            <p class="text-muted">Developer</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-primary btn-lg">Start Shopping</a>
                <a href="brands.php" class="btn btn-outline-primary btn-lg ms-3">Explore Our Brands</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>