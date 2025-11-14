<!DOCTYPE html>
<html>
<head>
    <title>Login & Register - Manajemen Proyek</title>
    <style>
        /* Warna dasar dan font */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5e8d0; /* coklat muda lembut */
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }

        /* Container utama */
        .auth-container {
            background-color: #fffaf3; /* putih krem */
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(150, 100, 50, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        h1 {
            color: #5b3920; /* coklat tua elegan */
            margin-bottom: 25px;
            font-size: 26px;
        }

        .form-tabs {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 2px solid #d1bfa3;
        }

        .tab-button {
            flex: 1;
            background: none;
            border: none;
            padding: 12px;
            cursor: pointer;
            font-size: 16px;
            color: #5b3920;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-button.active {
            border-bottom-color: #a47449;
            font-weight: bold;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: 500;
            color: #5b3920;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1bfa3;
            border-radius: 8px;
            outline: none;
            background-color: #fffdf9;
            margin-bottom: 18px;
            font-size: 14px;
            transition: 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            border-color: #a07a4c;
            box-shadow: 0 0 5px rgba(160, 122, 76, 0.4);
        }

        button {
            width: 100%;
            background-color: #a47449;
            color: white;
            border: none;
            padding: 12px;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #8a6238;
        }

        p {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
        }

        /* Animasi halus saat masuk */
        .auth-container {
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Manajemen Proyek</h1>

        <div class="form-tabs">
            <button class="tab-button active" onclick="showTab('login')">Login</button>
            <button class="tab-button" onclick="showTab('register')">Register</button>
        </div>

        @if(session('error'))
            <p>{{ session('error') }}</p>
        @endif

        @if(session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        <!-- Login Form -->
        <div id="login" class="tab-content active">
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <label>Username:</label>
                <input type="text" name="username" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <button type="submit">Login</button>
            </form>
        </div>

        <!-- Register Form -->
        <div id="register" class="tab-content">
            <form method="POST" action="{{ route('register.post') }}">
                @csrf
                <label>Username:</label>
                <input type="text" name="username" required>

                <label>Full Name:</label>
                <input type="text" name="full_name" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label>Role:</label>
                <select name="role" style="width: 100%; padding: 10px 12px; border: 1px solid #d1bfa3; border-radius: 8px; margin-bottom: 18px; background-color: #fffdf9;">
                    <option value="developer">Developer</option>
                    <option value="designer">Designer</option>
                    <option value="team_lead">Team Lead</option>
                </select>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
