<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSS Olongapo Branch Queueing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/First.css') }}">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 home-hero">
    <!-- Quick access to the Queue Display Board -->
    <a href="{{ url('/queue_display_board') }}" class="btn btn-warning btn-sm position-fixed top-50 start-0 translate-middle-y ms-2 z-3 shadow"
       style="border-radius: 9999px; padding: 6px 10px;">
        Display Board
    </a>
    <div class="container text-center">
        <header class="mb-5 hero-header d-flex flex-column align-items-center">
            <img src="{{ asset('images/sss.svg') }}" alt="SSS Logo" class="mb-3 sss-logo" />
            <h1 class="main-title">Welcome to SSS Olongapo Branch Queueing System</h1>
            <p class="subtitle">Select your role to continue</p>
        </header>
        
        <div class="row justify-content-center">
            <div class="col-md-4 mb-4">
                <a href="{{ route('printer') }}" class="card role-card shadow-sm text-decoration-none">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-primary-custom mb-3">
                            <!-- Printer icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M7 5h10v4H7z"/>
                                <path d="M5 9h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2z"/>
                                <path d="M7 17h10v4H7z"/>
                                <path d="M17 13h.01"/>
                            </svg>
                        </div>
                        <h5 class="card-title">With Thermal Printer</h5>
                        <p class="card-text">Generate queue tickets with automatic thermal printing</p>
                    </div>
                </a>
            </div>
        
            <div class="col-md-4 mb-4">
                <a href="{{ route('screen') }}" class="card role-card shadow-sm text-decoration-none">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-success-custom mb-3">
                            <!-- Document icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                <path d="M14 2v6h6"/>
                                <path d="M8 9h4"/>
                                <path d="M8 13h8"/>
                                <path d="M8 17h8"/>
                            </svg>
                        </div>
                        <h5 class="card-title">Without Thermal Printer</h5>
                        <p class="card-text">Generate queue tickets with on-screen display only</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <a href="{{ route('caller.index') }}" class="card role-card shadow-sm text-decoration-none">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-purple-custom mb-3">
                            <!-- User icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                <path d="M4 20a8 8 0 0 1 16 0"/>
                            </svg>
                        </div>
                        <h5 class="card-title">Caller</h5>
                        <p class="card-text">Call and manage customer queue</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>