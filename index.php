<?php
// Initialize user data and admin data
$userDataFile = 'user_data.json';
$adminDataFile = 'admin.json';
$userData = [];
$loggedInUser = null; // Track the currently logged-in user
$adminData = [];

// Check if the JSON files exist and load data
if (file_exists($userDataFile)) {
    $userData = json_decode(file_get_contents($userDataFile), true);
}

if (file_exists($adminDataFile)) {
    $adminData = json_decode(file_get_contents($adminDataFile), true);
}

// Function to check if a user is logged in
function isLoggedIn() {
    global $loggedInUser;
    return !empty($loggedInUser);
}

// Function to check if the logged-in user is an admin
function isAdmin() {
    global $loggedInUser, $adminData;
    return isset($adminData['adminUsername']) && $loggedInUser === $adminData['adminUsername'];
}

// Function to register a new user
function registerUser() {
    global $userData;
    
    echo "Enter a username: ";
    $username = trim(fgets(STDIN));
    
    // Check if the username already exists
    if (isset($userData[$username])) {
        echo "Username already exists. Please choose a different one.\n";
        return;
    }
    
    echo "Enter a password: ";
    $password = trim(fgets(STDIN));
    
    // Store the user data
    $userData[$username] = [
        'password' => password_hash($password, PASSWORD_DEFAULT), // Hash the password
        'balance' => 0,
    ];
    
    echo "User registered successfully.\n";
}

// Function to log in as a user
function loginUser() {
    global $userData, $loggedInUser;
    
    echo "Enter your username: ";
    $username = trim(fgets(STDIN));
    
    // Check if the username exists
    if (!isset($userData[$username])) {
        echo "Username not found. Please register or enter a valid username.\n";
        return;
    }
    
    echo "Enter your password: ";
    $password = trim(fgets(STDIN));
    
    // Verify the password
    if (password_verify($password, $userData[$username]['password'])) {
        $loggedInUser = $username;
        echo "Logged in as $username.\n";
    } else {
        echo "Incorrect password. Please try again.\n";
    }
}

// Function to log in as an admin
// Function to log in as an admin
function loginAsAdmin() {
    global $adminData, $loggedInUser;
    
    echo "Enter admin username: ";
    $adminUsername = trim(fgets(STDIN));
    
    // Check if $adminData is an array and if the admin username exists
    if (is_array($adminData) && isset($adminData['adminUsername'])) {
        if ($adminUsername !== $adminData['adminUsername']) {
            echo "Admin username not found.\n";
            return;
        }
        
        echo "Enter admin password: ";
        $adminPassword = trim(fgets(STDIN));
        
        // Verify the admin password
        if ($adminPassword === $adminData['adminPassword']) {
            $loggedInUser = $adminUsername;
            echo "Logged in as admin: $adminUsername.\n";
        } else {
            echo "Incorrect admin credentials. Please try again.\n";
        }
    } else {
        echo "Admin data is missing or incorrectly formatted.\n";
    }
}


// Function to list user accounts
function listUserAccounts() {
    global $userData;
    
    echo "User Accounts:\n";
    foreach ($userData as $username => $data) {
        echo "Username: $username, Balance: {$data['balance']}\n";
    }
}

// Function to list all users (admin only)
function listAllUsers() {
    global $userData;
    
    echo "All Users:\n";
    foreach (array_keys($userData) as $username) {
        echo "Username: $username\n";
    }
}

// Function to see user balances (admin only)
function seeUserBalances() {
    global $userData;
    
    echo "User Balances:\n";
    foreach ($userData as $username => $data) {
        echo "Username: $username, Balance: {$data['balance']}\n";
    }
}

// Main menu and user interaction
while (true) {
    echo "Welcome to the CLI Banking App\n";
    echo "1. Register\n";
    echo "2. Log In\n";
    echo "3. Log in as Admin\n";
    
    if (isLoggedIn()) {
        echo "4. Deposit\n";
        echo "5. Withdraw\n";
        echo "6. Check Balance\n";
        echo "7. Log Out\n";
        if (isAdmin()) {
            echo "8. List User Accounts\n";
            echo "9. List All Users\n";
            echo "10. See User Balances\n";
        }
    } else {
        echo "4. Exit\n";
    }
    
    echo "Enter your choice: ";

    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case 1:
            registerUser();
            break;

        case 2:
            loginUser();
            break;

        case 3:
            loginAsAdmin();
            break;

        case 4:
            if (isLoggedIn()) {
                echo "Enter the amount to deposit: ";
                $amount = floatval(trim(fgets(STDIN)));
                if ($amount <= 0) {
                    echo "Invalid amount. Please enter a positive number.\n";
                    break;
                }
                $userData[$loggedInUser]['balance'] += $amount;
                echo "Deposited $amount successfully.\n";
            } else {
                exit; // Exit the app
            }
            break;

        case 5:
            if (isLoggedIn()) {
                echo "Enter the amount to withdraw: ";
                $amount = floatval(trim(fgets(STDIN)));
                if ($amount <= 0) {
                    echo "Invalid amount. Please enter a positive number.\n";
                    break;
                }
                if ($userData[$loggedInUser]['balance'] < $amount) {
                    echo "Insufficient balance.\n";
                    break;
                }
                $userData[$loggedInUser]['balance'] -= $amount;
                echo "Withdrawn $amount successfully.\n";
            } else {
                exit; // Exit the app
            }
            break;

        case 6:
            if (isLoggedIn()) {
                $balance = $userData[$loggedInUser]['balance'];
                echo "Your balance: $balance\n";
            } else {
                exit; // Exit the app
            }
            break;

        case 7:
            if (isLoggedIn()) {
                // Log Out logic
                $loggedInUser = null;
                echo "Logged out.\n";
            } else {
                exit; // Exit the app
            }
            break;

        case 8:
            if (isAdmin()) {
                listUserAccounts();
            }
            break;

        case 9:
            if (isAdmin()) {
                listAllUsers();
            }
            break;

        case 10:
            if (isAdmin()) {
                seeUserBalances();
            }
            break;

        default:
            echo "Invalid choice. Please enter a valid option.\n";
    }
    
    // Save user data to JSON file after each operation
    file_put_contents($userDataFile, json_encode($userData, JSON_PRETTY_PRINT));
}
?>
