<?php
session_start();
require_once('DBConnection.php');

/**
 * Login Registration Class
 */
class LoginRegistration extends DBConnection
{
    function __construct()
    {
        parent::__construct();
    }

    function __destruct()
    {
        parent::__destruct();
    }

    function login()
    {
        // Start session (if not already started)
        session_start();

        // Extracting POST array to variables.
        extract($_POST);

        var_dump($_POST);

        // Retrieving Allowed Token
        $allowedToken = $_SESSION['formToken']['login'];

        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        } else {
            // SQL query to retrieve user information based on username
            $sql = 'SELECT * FROM user_list WHERE username = ?';

            // Prepare the SQL statement
            $stmt = $this->prepare($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            var_dump($data);
            // Check if the user exists
            if (!empty($data)) {
                // Verify the password
                if (password_verify($password, $data['password'])) {
                    if ($data['status'] == 1) {
                        // Login Success
                        $resp['status'] = 'success';
                        $resp['msg'] = 'Login successfully.';
                        foreach ($data as $k => $v) {
                            if (!is_numeric($k) && !in_array($k, ['password'])) {
                                $_SESSION[$k] = $v;
                            }
                        }
                    } elseif ($data['status'] == 0) {
                        // Pending
                        $resp['status'] = 'failed';
                        $resp['msg'] = 'Your account is still pending approval.';
                    } elseif ($data['status'] == 2) {
                        // Denied
                        $resp['status'] = 'failed';
                        $resp['msg'] = 'Your account has been denied. Please contact the management.';
                    } elseif ($data['status'] == 3) {
                        // Blocked
                        $resp['status'] = 'failed';
                        $resp['msg'] = 'Your account has been blocked. Please contact the management.';
                    } else {
                        $resp['status'] = 'failed';
                        $resp['msg'] = 'Invalid status. Please contact the management.';
                    }
                } else {
                    // Invalid Password
                    $resp['status'] = 'failed';
                    $resp['msg'] = 'Invalid username or password.';
                }
            } else {
                // Invalid Username
                $resp['status'] = 'failed';
                $resp['msg'] = 'Invalid username or password.';
            }

            $stmt->close();
        }

        return json_encode($resp);
    }

    // ... other methods
    function logout()
    {
        // Start the session
        session_start();

        // Unset all of the session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect the user to the login page or any other desired page
        header("Location: login.php"); // Replace 'login.php' with the page you want to redirect to
        exit;
    }


    function register_user()
    {
        /**
         * User Registration Form Action
         */

        // Escape input values
        foreach ($_POST as $k => $v) {
            if (!in_array($k, ['user_id', 'formToken']) && !is_numeric($v) && !is_array($_POST[$k])) {
                $_POST[$k] = $this->escape_string($v);
            }
        }
        // Extract POST array values
        extract($_POST);

        $allowedToken = $_SESSION['formToken']['registration'];
        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        } else {
            // Table column
            $dbColumn = "(`fullname`, `username`, `password`, `status`, `type`)";

            // Encrypt Password
            $password = password_hash($password, PASSWORD_DEFAULT);

            // Table Values
            $values = "('{$fullname}', '{$username}', '{$password}', 0, 2)";

            // Insertion Query Statement
            $sql = "INSERT INTO `user_list` {$dbColumn} VALUES {$values}";

            // Executing Insertion Query
            $insert = $this->query($sql);

            if ($insert) {
                // Successful insertion
                $resp['status'] = 'success';
                $resp['msg'] = "Your Account has been created successfully but it is subject for approval.";
            } else {
                // Insertion Failed
                $resp['status'] = 'failed';
                $resp['msg'] = "Error: " . $this->error;
            }
        }

        echo json_encode($resp);
    }

    function update_user()
    {
        /**
         * Update User Form Action
         */

        // Extract POST array values
        extract($_POST);

        $allowedToken = $_SESSION['formToken']['manage_user'];
        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        } else {
            // Update data
            $data = "`status` = '{$status}'";
            $data .= ",`type` = '{$type}'";

            // UPDATE Query Statement
            $sql = "UPDATE `user_list` set {$data} where `user_id` = '{$user_id}";

            // Executing update Query
            $update = $this->query($sql);

            if ($update) {
                // Successful Update
                $resp['status'] = 'success';
                $resp['msg'] = "User Account has been updated successfully.";
            } else {
                // Update Failed
                $resp['status'] = 'failed';
                $resp['msg'] = "Error: " . $this->error;
            }
        }

        echo json_encode($resp);
    }

    function update_password()
    {
        /**
         * Update Account Password Form Action
         */

        // Extract POST array values
        extract($_POST);

        $allowedToken = $_SESSION['formToken']['account-form'];
        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        } else {
            $password = $this->query("SELECT `password` FROM `user_list` where `user_id`='{$_SESSION['user_id']}'");
            $is_verify = password_verify($current_password, $password);

            if (!$is_verify) {
                $resp['status'] = 'failed';
                $resp['msg'] = "Current Password is incorrect.";
            } else {
                if ($new_password != $confirm_new_password) {
                    $resp['status'] = 'failed';
                    $resp['msg'] = "New Password does not match.";
                } else {
                    $new_password = password_hash($new_password, PASSWORD_DEFAULT);

                    $update = $this->query("UPDATE `user_list` set `password` = '{$new_password}' where `user_id` = '{$_SESSION['user_id']}'");

                    if ($update) {
                        $resp['status'] = 'success';
                        $resp['msg'] = "Password has been updated successfully.";
                        $_SESSION['message']['success'] = $resp['msg'];
                    } else {
                        $resp['status'] = 'failed';
                        $resp['msg'] = $this->error;
                    }
                }
            }
        }

        echo json_encode($resp);
    }
}

$a = isset($_GET['a']) ? $_GET['a'] : '';
$LG = new LoginRegistration();

switch ($a) {
    case 'login':
        echo $LG->login();
        break;
    case 'logout':
        echo $LG->logout();
        break;
    case 'register_user':
        echo $LG->register_user();
        break;
    case 'update_user':
        echo $LG->update_user();
        break;
    case 'update_password':
        echo $LG->update_password();
        break;
    default:
        // default action here
        break;
}
