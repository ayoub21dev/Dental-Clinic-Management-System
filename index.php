<?php
// Include database configuration file
require_once 'config.php';


// If user is already logged in, redirect to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // 1. Input Validation
    // Check if username field is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password field is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    // 2. Database Authentication
    // Proceed if there are no validation errors
    if(empty($username_err) && empty($password_err)){
        
        $sql = "SELECT user_id, username, password_hash, role FROM users WHERE username = ? OR email = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_email);

            //
            $param_username = $username;
            $param_email = $username; 

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store the result
                mysqli_stmt_store_result($stmt);

                // Check if username (or email) exists
                if(mysqli_stmt_num_rows($stmt) == 1){
                    // Bind result variables to the retrieved data from the database
                    mysqli_stmt_bind_result($stmt, $user_id, $db_username, $hashed_password, $role);
                    if(mysqli_stmt_fetch($stmt)){
                        // *** Compare the entered password with the stored hash ***
                        // Since we stored MD5 for 'test123', we use md5($password) for comparison.
                        // In a real application, you should use password_verify($password, $hashed_password)
                        // with secure hashes like bcrypt or argon2.
                        if($hashed_password === md5($password)){
                             // Password is correct, start a new session and store user data
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["username"] = $db_username; // Use the username from the database
                            $_SESSION["role"] = $role; // Store the user's role

                            // Redirect user to the dashboard page
                            header("location: dashboard.php");
                            exit(); // It's crucial to stop script execution after redirection
                        } else{
                            // Display an error message if password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Display an error message if username (or email) doesn't exist
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close the database connection (should be done after all database operations)
    mysqli_close($conn);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dental Clinic</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" xintegrity="sha512-YWzhKL2whUzgiheMoBFwW8CKV4qpHQAEuvilg9FAn5VJUDwKZZxkJNuGM4XkWuk94WCrrxssKjUraGaYhKPrfQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f5f7; /* Light gray background */
        }
        /* Custom scrollbar for consistency */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full text-center">
        <div class="flex justify-center mb-6">
            <div class="bg-emerald-500 text-white w-16 h-16 flex items-center justify-center rounded-full text-2xl font-bold shadow-md">
                LS
            </div>
        </div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Welcome Back!</h2>
        <p class="text-gray-600 mb-6">Sign in to your Dental Clinic account</p>

        <?php
        // This section will display error messages
        if(!empty($login_err)){
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4 text-left">
                <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Username / Email</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 transition duration-200 <?php echo (!empty($username_err)) ? 'border-red-500' : ''; ?>"
                        placeholder="admin.user or admin@example.com"
                        value="<?php echo $username; ?>"
                        required
                    />
                </div>
                <span class="text-red-500 text-sm mt-1 block"><?php echo $username_err; ?></span>
            </div>
            <div class="mb-6 text-left">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 transition duration-200 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>"
                        placeholder="••••••••"
                        required
                    />
                </div>
                <span class="text-red-500 text-sm mt-1 block"><?php echo $password_err; ?></span>
            </div>

            <div class="flex items-center justify-between mb-6 text-sm">
                <label class="flex items-center text-gray-700">
                    <input type="checkbox" class="form-checkbox text-emerald-500 rounded border-gray-300 focus:ring-emerald-500" />
                    <span class="ml-2">Remember me</span>
                </label>
                <a href="#" class="text-blue-600 hover:underline">Forgot password?</a>
            </div>

            <button
                type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 rounded-lg flex items-center justify-center transition duration-300 ease-in-out transform hover:scale-105"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>

        <p class="text-gray-600 mt-6 text-sm">
            Don't have an account? <a href="#" class="text-blue-600 hover:underline">Sign Up</a>
        </p>
    </div>
</body>
</html>
