    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('img/logo-small.png')}}" alt="Daphas POS" height="60" class="me-2">
                <span class="fw-bold fs-4 text-dark">Daphas POS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark px-3" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="{{ route('login') }}" target="_blank" class="btn btn-light border text-primary px-4 me-2">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Live Demo
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-primary px-4">
                            Sign In
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
