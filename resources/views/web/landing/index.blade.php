<!-- Header -->
@extends('layouts.web.header')

    <!-- Navigation -->
    @include('layouts.web.navbar')

    <!-- Main Wrapper -->
    <div class="min-vh-100 d-flex flex-column" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        
        <!-- Hero Section -->
        <section class="flex-grow-1 d-flex align-items-center">
            <div class="container py-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-white mb-5 mb-lg-0">
                        <h1 class="display-4 fw-bold mb-4">
                            Modern Point of Sale<br>
                            <span class="text-warning">For Modern Business</span>
                        </h1>
                        <p class="lead mb-4 text-white-50">
                            Streamline your retail operations with our powerful, intuitive POS system. 
                            Manage inventory, process sales, and grow your business.
                        </p>
                        
                        <div class="d-flex gap-3 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-warning me-2"></i>
                                <span>Easy to use</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-warning me-2"></i>
                                <span>Cloud-based</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-warning me-2"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <a href="#contact" class="btn btn-warning btn-lg px-5 py-3 fw-semibold">
                                Start Free Trial <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                            <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3">
                                Learn More
                            </a>
                        </div>
                        
                        <!-- Stats -->
                        <div class="row mt-5 g-4">
                            <div class="col-4">
                                <h3 class="text-white fw-bold mb-0">500+</h3>
                                <p class="text-white-50">Active Businesses</p>
                            </div>
                            <div class="col-4">
                                <h3 class="text-white fw-bold mb-0">50K+</h3>
                                <p class="text-white-50">Daily Transactions</p>
                            </div>
                            <div class="col-4">
                                <h3 class="text-white fw-bold mb-0">99.9%</h3>
                                <p class="text-white-50">Uptime</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card bg-white bg-opacity-10 border-0">
                            <div class="card-body p-5">
                                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" 
                                    alt="POS Dashboard Preview" 
                                    class="img-fluid rounded-4 shadow-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-5 bg-white">
            <div class="container py-5">
                <div class="text-center mb-5">
                    <h6 class="text-primary text-uppercase fw-bold mb-3">Features</h6>
                    <h2 class="display-5 fw-bold mb-3">Everything You Need To Run Your Business</h2>
                    <p class="text-secondary mx-auto" style="max-width: 600px;">Powerful features that make selling easy and efficient</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-cart3 fs-1 text-primary"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Quick Sales</h4>
                                <p class="text-secondary">Process sales quickly with an intuitive interface. Support for multiple payment methods.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-box-seam fs-1 text-success"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Inventory Control</h4>
                                <p class="text-secondary">Track stock levels in real-time. Get alerts when products are running low.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-people fs-1 text-info"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Customer Management</h4>
                                <p class="text-secondary">Build customer loyalty with rewards programs and purchase history tracking.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-graph-up fs-1 text-warning"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Sales Reports</h4>
                                <p class="text-secondary">Detailed analytics and reports to help you make informed business decisions.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-printer fs-1 text-danger"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Receipt Printing</h4>
                                <p class="text-secondary">Compatible with thermal printers. Customizable receipt templates.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-cloud fs-1 text-secondary"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Cloud Sync</h4>
                                <p class="text-secondary">Access your data anywhere, anytime. Real-time sync across all devices.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="container py-5">
                <div class="row">
                    <div class="col-lg-6 text-white mb-4 mb-lg-0">
                        <h6 class="text-warning text-uppercase fw-bold mb-3">Contact Us</h6>
                        <h2 class="display-5 fw-bold mb-4">Ready to transform your business?</h2>
                        <p class="mb-4 text-white-50">Get in touch with our team and we'll help you get started with Daphas POS.</p>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-envelope-fill text-warning me-3 fs-4"></i>
                            <span>info@daphascomp.com</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-telephone-fill text-warning me-3 fs-4"></i>
                            <span>+254 (722) 33 - 6262</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-warning me-3 fs-4"></i>
                            <span>Nairobi, Kenya</span>
                        </div>
                        
                        <div class="mt-4">
                            <h5 class="text-white mb-3">Follow Us</h5>
                            <div class="d-flex gap-3">
                                <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="text-white fs-4"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="text-white fs-4"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card border-0 shadow">
                            <div class="card-body p-5">
                                <form>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control form-control-lg" placeholder="Full Name">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="email" class="form-control form-control-lg" placeholder="Email Address">
                                        </div>
                                        <div class="col-12">
                                            <input type="text" class="form-control form-control-lg" placeholder="Business Name">
                                        </div>
                                        <div class="col-12">
                                            <select class="form-select form-select-lg">
                                                <option selected>Interested In</option>
                                                <option value="demo">Request Demo</option>
                                                <option value="trial">Free Trial</option>
                                                <option value="support">Support</option>
                                                <option value="other">Other Inquiry</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <textarea class="form-control form-control-lg" rows="4" placeholder="Message"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" disabled class="btn btn-primary btn-lg w-100 py-3">
                                                Send Message <i class="bi bi-send ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div> 

    <!-- Footer -->
    @include('layouts.web.footer')

<!-- Closure -->
@extends('layouts.web.closure')