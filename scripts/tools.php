<?php
/**
 * Logs out the admin by removing cookies and killing the session
 *
 * @return true.
 */
function logout() {
    $_SESSION = array();
    session_destroy();
    return true;
}

/**
* Check if selected email has valid email format
*
* @param string $user_email Email address
* @return boolean
*/
function is_valid_email($user_email) {
    $chars = EMAIL_FORMAT;
    if (strstr($user_email, '@') && strstr($user_email, '.')) {
        return (boolean) preg_match($chars, $user_email);
    }
    return false;
}

/**
* Strip all potentially malicious characters from user input
*
* @param string $data user input
* @return string
*/
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
* For printing array data
*
* @param string $arr array
* @param string $returnAsString if true, return the string
* @return string
*/
function prePrintArray($arr, $returnAsString=false ) {
    $ret  = '<pre>' . print_r($arr, true) . '</pre>';
    if ($returnAsString)
        return $ret;
    else
        echo $ret;
}

// login auth variables
$error_message = "";
$invalid_login = false;

// signup error variables
$first_name_valid = $last_name_valid = $email_valid = $phone_valid = $username_valid = $password_valid = false;
$nameError = $emailError = $phoneError = $usernameError = $passwordError = "";

// add admin error variables
$flag_valid = false;
$flagError = "";

// update user error variables
$currentpassword_valid = $newpassword_valid = $renewpassword_valid = false;

// update user success variables
$editEmailSuccess = $editPhoneSuccess = $editPasswordSuccess = true;
$emailSuccess = $phoneSuccess = $passwordSuccess = "";

// admin update accounts error variables
$editFirstNameSuccess = $editLastNameSuccess = true;
$nameSuccess = "";

// when a POST form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ------------------------------------ //
    // --- LOGIN AUTHENTICATION SECTION --- //
    // ------------------------------------ //
    // reset login or log out button is pressed
    if (isset($_POST['reset-login']) || isset($_POST['logout'])) {
        // remove user from session
        if (isset($_SESSION['Customer'])) { unset($_SESSION['Customer']); }
        // remove admin from session
        if (isset($_SESSION['Admin'])){ unset($_SESSION['Admin']); }
    }

    // 'login' is in $_POST (after user presses Log In button)
    else if (isset($_POST['login'])) {
        // none of the fields are empty and have value other than NULL
        if ((!empty($_POST['username']) && isset($_POST['username'])) && (!empty($_POST['password']) && isset($_POST['password']))) {
            // remove malicious characters then assign those values to the variable
            $username = test_input($_POST['username']);
            $password = test_input($_POST['password']);
            
            // GET username and password from user's inputs from admin table
            $sql = "SELECT `username`, `password` FROM `admin` WHERE username='$username' AND password=PASSWORD('$password')";
            
            // run mysql query and store results to $result
            if ($result = mysqli_query($conn, $sql)) {
                // has at least 1 result
                if (mysqli_num_rows($result) > 0) {
                    // add admin to session
                    $_SESSION['Admin'] = $username;

                    // Free result set (free up memory)
                    mysqli_free_result($result);

                    // redirects to index.php upon successful login
                    header("Location: index.php?p=home");
                    exit;
                }
                else {
                    $invalid_login = true;
                    $error_message = "<span style='color: red'> Username or password is incorrect! </span>";
                }
            }
            else {
                echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
            }
            
            // GET username and password from user's inputs customer table
            $sql = "SELECT `username`, `password` FROM `customer` WHERE username='$username' AND password=PASSWORD('$password')";
            
            // run mysql query and store results to $result
            if ($result = mysqli_query($conn, $sql)) {
                // has at least 1 result
                if (mysqli_num_rows($result) > 0) {
                    // add customer to session
                    $_SESSION['Customer'] = $username;

                    // Free result set (free up memory)
                    mysqli_free_result($result);

                    // redirects to index.php upon successful login
                    header("Location: index.php?p=home");
                    exit;
                }
                else {
                    $invalid_login = true;
                    $error_message = "<span style='color: red'> Username or password is incorrect! </span>";
                }
            }
            else {
                echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
            }
        }
        // fields empty
        else if (empty($_POST['username']) && empty($_POST['password'])) {
            $invalid_login = true;
            $error_message = "<span style='color: red'> Fields are empty! </span>";
        }
        // username field is empty
        else if (empty($_POST['username'])) {
            $invalid_login = true;
            $error_message = "<span style='color: red'> Please enter the username! </span>";
        }
        // password field is empty
        else if (empty($_POST['password'])) {
            $invalid_login = true;
            $error_message = "<span style='color: red'> Please enter the password! </span>";
        }
    }
    // ------------------------------------------- //
    // --- END OF LOGIN AUTHENTICATION SECTION --- //
    // ------------------------------------------- //


    // --------------------------------- //
    // --- SIGNUP VALIDATION SECTION --- //
    // --------------------------------- //
    // sign up or add customer from admin management
    else if (isset($_POST['signup']) || isset($_POST['add-customer']) ) {
        // Validate first name
        if (empty($_POST["first_name"])) {
            $nameError = "First Name is required!";
        } else {
            if (isset($_POST["first_name"])) {
                $first_name = test_input($_POST["first_name"]);
                // not matching regex, format error message
                if (!preg_match("/^[A-Za-z]+$/", $first_name)) {
                    $nameError = "Please enter an appropriate name!";
                } else {  
                    $first_name_valid = true;
                }
            }
        }

        // Validate last name
        if (empty($_POST["last_name"])) {
            $nameError = "Last Name is required!";
        } else {
            if (isset($_POST["last_name"])) {
                $last_name = test_input($_POST["last_name"]);
                // not matching regex, format error message
                if (!preg_match("/^[A-Za-z]+$/", $last_name)) {
                    $nameError = "Please enter an appropriate name!";
                } else {  
                    $last_name_valid = true;
                }
            }
        }

        // Validate email
        if (empty($_POST["email"])) {
            $emailError = "Email is required!";
        } else {
            if (isset($_POST["email"])) {
                $email = test_input($_POST["email"]);
                // check if e-mail address is well-formed
                if (!is_valid_email($email)) {
                    $emailError = "Invalid email format!";
                } else {
                    $sql = "SELECT `email` FROM `customer` WHERE username='$username'";
                
                    // run mysql query and store results to $result
                    if ($result = mysqli_query($conn, $sql)) {
                        // has at least 1 result
                        if (mysqli_num_rows($result) > 0) {
                            $emailError = "This email is already registered!";
                        } else {
                            $email_valid = true;
                        }
                        // Free result set (free up memory)
                        mysqli_free_result($result);
                    }
                    else {
                        echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                    }
                }
            }
        }

        // Validate phone number
        if (empty($_POST["phone"])) {
            $phoneError = "Phone number is required!";
        } else {
        // field not empty, check if data is not null 
        if (isset($_POST["phone"])) {
                $phone = test_input($_POST["phone"]);
                // not matching regex, format error message
                if (!preg_match("/^\+?\d{0,13}/", $phone)) {
                    $phoneError = "Invalid phone number";
                } else {
                    $phone_valid = true;
                }
            }
        }

        // Validate username
        if (empty($_POST["username"])) {
            $usernameError = "Username is required!";
        } else {
            // field not empty, check if data is not null 
            if (isset($_POST["username"])) {
                $username = test_input($_POST["username"]);
                
                $sql = "SELECT `username` FROM `customer` WHERE username='$username'";
                
                // run mysql query and store results to $result
                if ($result = mysqli_query($conn, $sql)) {
                    // has at least 1 result
                    if (mysqli_num_rows($result) > 0) {
                        $usernameError = "Username has been taken!";
                        
                    } else {
                        $username_valid = true;
                    }
                    // Free result set (free up memory)
                    mysqli_free_result($result);
                }
                else {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
            }
        }

        // Validate password
        if (empty($_POST["password"])) {
            $passwordError = "Password is required!";
        } else {
            // field not empty, check if data is not null
            if (isset($_POST["password"])) {
                $password = test_input($_POST["password"]);
                $password_valid = true;
            }
        }

        if ($first_name_valid && $last_name_valid && $email_valid && $phone_valid && $username_valid && $password_valid) {
            $sql = "INSERT INTO `customer`(`last_name`, `first_name`, `email`, `phone_number`, `username`, `password`) VALUES ('$last_name', '$first_name', '$email', '$phone','$username', PASSWORD('$password'))";
            $conn->query($sql);
            if (isset($_SESSION['Admin'])) {
                Header("Location: index.php?p=admin&c=users");
                exit();
            }
            else {
                Header("Location: index.php?=home");
                exit();
            }
        }
    }

    // add admin from admin management
    else if (isset($_POST['add-admin'])) {
        // Validate first name
        if (empty($_POST["first_name"])) {
            $nameError = "First Name is required!";
        } else {
            if (isset($_POST["first_name"])) {
                $first_name = test_input($_POST["first_name"]);
                // not matching regex, format error message
                if (!preg_match("/^[A-Za-z]+$/", $first_name)) {
                    $nameError = "Please enter an appropriate name!";
                } else {  
                    $first_name_valid = true;
                }
            }
        }

        // Validate last name
        if (empty($_POST["last_name"])) {
            $nameError = "Last Name is required!";
        } else {
            if (isset($_POST["last_name"])) {
                $last_name = test_input($_POST["last_name"]);
                // not matching regex, format error message
                if (!preg_match("/^[A-Za-z]+$/", $last_name)) {
                    $nameError = "Please enter an appropriate name!";
                } else {  
                    $last_name_valid = true;
                }
            }
        }

        // Validate email
        if (empty($_POST["email"])) {
            $emailError = "Email is required!";
        } else {
            if (isset($_POST["email"])) {
                $email = test_input($_POST["email"]);
                // check if e-mail address is well-formed
                if (!is_valid_email($email)) {
                    $emailError = "Invalid email format!";
                } else {
                    $email_valid = true;
                }
            }
        }

        // Validate phone number
        if (empty($_POST["phone"])) {
            $phoneError = "Phone number is required!";
        } else {
        // field not empty, check if data is not null 
        if (isset($_POST["phone"])) {
                $phone = test_input($_POST["phone"]);
                // not matching regex, format error message
                if (!preg_match("/^\+?\d{0,13}/", $phone)) {
                    $phoneError = "Invalid phone number";
                } else {
                    $phone_valid = true;
                }
            }
        }

        // Validate username
        if (empty($_POST["username"])) {
            $usernameError = "Username is required!";
        } else {
            // field not empty, check if data is not null 
            if (isset($_POST["username"])) {
                $username = test_input($_POST["username"]);
                
                $sql = "SELECT `username` FROM `admin` WHERE username='$username'";
                
                // run mysql query and store results to $result
                if ($result = mysqli_query($conn, $sql)) {
                    // has at least 1 result
                    if (mysqli_num_rows($result) > 0) {
                        $usernameError = "Username has been taken!";
                    } else {
                        $username_valid = true;
                    }
                    // Free result set (free up memory)
                    mysqli_free_result($result);
                }
                else {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
            }
        }

        // Validate password
        if (empty($_POST["password"])) {
            $passwordError = "Password is required!";
        } else {
            // field not empty, check if data is not null
            if (isset($_POST["password"])) {
                $password = test_input($_POST["password"]);
                $password_valid = true;
            }
        }

        $flags = [LIST_CUSTOMERS, ADD_CUSTOMERS, EDIT_CUSTOMERS, DELETE_CUSTOMERS, LIST_ADMINS, ADD_ADMINS, EDIT_ADMINS, DELETE_ADMINS];
        $flag = array();
        $flag_string = "";
        
        // Validate flag
        if (empty($_POST["flag"])) {
            $flagError = "Flag is required!";
        } else {
            // field not empty, check if data is not null
            if (isset($_POST["flag"])) {
                // remove malicious characters and add each data to $flag array
                for ($i = 0; $i < sizeof($_POST["flag"]); $i++) {
                    $flag[$i] = test_input($_POST["flag"][$i]);
                    $flag_string .= $flag[$i];
                }
                // all flags selected, set flag string to root admin flag
                if ($flag_string == "abcdefgh") {
                    $flag_string = ROOT_ADMIN;
                    $flag_valid = true;
                }
                // custom set of flags
                else {
                    // check if each flag is in the flag list
                    for ($i = 0; $i < sizeof($flag); $i++) {
                        if (in_array($flag[$i], $flags))
                            $flag_valid = true;
                        else {
                            $flagError = "Invalid flag!";
                            break;
                        }
                    }
                }
            }
        }

        if ($first_name_valid && $last_name_valid && $email_valid && $phone_valid && $username_valid && $password_valid && $flag_valid) {
            $sql = "INSERT INTO `admin`(`last_name`, `first_name`, `email`, `phone_number`, `username`, `password`, `flag`) VALUES ('$last_name', '$first_name', '$email', '$phone','$username', PASSWORD('$password'), '$flag_string')";
            if (mysqli_query($conn, $sql)) {
                Header("Location: index.php?p=admin&c=admins");
                exit();
            }
            else {
                echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
            }
        }
    }
    // ---------------------------------------- //
    // --- END OF SIGNUP VALIDATION SECTION --- //
    // ---------------------------------------- //

    
    // ---------------------------------------------- //
    // --- USER UPDATE DETAILS VALIDATION SECTION --- //
    // ---------------------------------------------- //
    // user update details from user management page
    else if (isset($_POST['update-user']) || isset($_POST['update-admin'])) {
        if (isset($_POST['update-user']))
            $username = $_SESSION['Customer'];
        else if (isset($_POST['update-admin']))
            $username = $_SESSION['Admin'];

        $email = $phone = $password = "";
        if (isset($_POST['update-user']))
            $sql = "SELECT `email`, `phone_number`, `password` FROM `customer` WHERE username='$username'";
        if (isset($_POST['update-admin']))
            $sql = "SELECT `email`, `phone_number`, `password` FROM `admin` WHERE username='$username'";
        // execute query and store results in $result
        if ($result = mysqli_query($conn, $sql)) {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    $email = $row['email'];
                    $phone = $row['phone_number'];
                    $password = $row['password'];
                }
                // Free result set (free up memory)
                mysqli_free_result($result);
            } else {
                echo "<p><em>No records were found.</em></p>";
            }
        } else {
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
        }

        // Validate email
        if (!empty($_POST["email"]) && isset($_POST["email"])) {
            $email_new = test_input($_POST["email"]);
            if ($email_new != $email) {
                // check if e-mail address is well-formed
                if (!is_valid_email($email)) {
                    $emailError = "Invalid email format!";
                    $editEmailSuccess = false;
                } else {
                    $emailSuccess = "Email updated.";
                    $email_valid = true;
                }
            }
        }

        // Validate phone number
        if (!empty($_POST["phone"]) && isset($_POST["phone"])) {
            $phone_new = test_input($_POST["phone"]);
            if ($phone_new != $phone) {
                // not matching regex, format error message
                if (!preg_match("/^\+?\d{0,13}/", $phone)) {
                    $phoneError = "Invalid phone number";
                    $editPhoneSuccess = false;
                } else {
                    $phoneSuccess = "Phone number updated.";
                    $phone_valid = true;
                }
            }
        }

        // Validate password
        // if 'current password' field has value
        if (!empty($_POST["oldpassword"]) && isset($_POST["oldpassword"])) {
            $currentpassword = test_input($_POST["oldpassword"]);
            if ($currentpassword == $password) {
                $passwordError = "Current password is incorrect!";
                $editPasswordSuccess = false;
            }
            else {
                $currentpassword_valid = true;
            }

            if ($currentpassword_valid) {
                // 'new password' field has value
                if (!empty($_POST["newpassword"]) && isset($_POST["newpassword"])) {
                    $newpassword = test_input($_POST["newpassword"]);
                    $newpassword_valid = true;
                    // field not empty, check if data is not null
                    if (!empty($_POST["renewpassword"]) && isset($_POST["renewpassword"])) {
                        $renewpassword = test_input($_POST["renewpassword"]);
                        if ($renewpassword == $newpassword) {
                            // run sql to check if new password is same with old password
                            if (isset($_POST['update-user']))
                                $sql = "SELECT `id` FROM `customer` WHERE username='$username' AND password=PASSWORD('$renewpassword')";
                            if (isset($_POST['update-admin']))
                                $sql = "SELECT `id` FROM `admin` WHERE username='$username' AND password=PASSWORD('$renewpassword')";

                                // execute query and store results in $result
                            if ($result = mysqli_query($conn, $sql)) {
                                // no results in query
                                if (mysqli_num_rows($result) > 0) {
                                    $passwordError = "New password is the same as old password";
                                    $editPasswordSuccess = false;
                                    // Free result set (free up memory)
                                    mysqli_free_result($result);
                                } else {
                                    $passwordSuccess = "Password updated.";
                                    $renewpassword_valid = true;
                                }
                            } else {
                                echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                            }
                        } else {
                            $passwordError = "New password in fields don't match!";
                            $editPasswordSuccess = false;
                        }
                    } else {
                        $passwordError = "Please re-type your new password!";
                        $editPasswordSuccess = false;
                    }
                }
                // no value in new password field, store error message
                else {
                    $passwordError = "New password is required!";
                    $editPasswordSuccess = false;
                }
            }
        }
        else if (!empty($_POST["newpassword"]) && isset($_POST["newpassword"])) {
            $passwordError = "Please enter your current password!";
            $editPasswordSuccess = false;
        }
        else if (!empty($_POST["renewpassword"]) && isset($_POST["renewpassword"])) {
            $passwordError = "Please enter your current and new password!";
            $editPasswordSuccess = false;
        }
        
        if ($email_valid) {
            if (isset($_POST['update-user']))
                $sql = "UPDATE `customer` SET `email`=? WHERE username=?;";
            if (isset($_POST['update-admin']))
                $sql = "UPDATE `admin` SET `email`=? WHERE username=?;";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, 'ss', $param_email, $param_username);

                // Set parameters
                $param_email = $email_new;
                $param_username = $username;

                // Attempt to execute the prepared statement
                if (!mysqli_stmt_execute($stmt)) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        if ($phone_valid) {
            if (isset($_POST['update-user']))
                $sql = "UPDATE `customer` SET `phone_number`=? WHERE username=?";
            if (isset($_POST['update-admin']))
                $sql = "UPDATE `admin` SET `phone_number`=? WHERE username=?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, 'ss', $param_phone, $param_username);

                // Set parameters
                $param_phone = $phone_new;
                $param_username = $username;

                // Attempt to execute the prepared statement
                if (!mysqli_stmt_execute($stmt)) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }

        if ($currentpassword_valid && $newpassword_valid && $renewpassword_valid) {
            if (isset($_POST['update-user']))
                $sql = "UPDATE `customer` SET `password`=PASSWORD(?) WHERE username=?;";
            if (isset($_POST['update-admin']))
                $sql = "UPDATE `admin` SET `password`=PASSWORD(?) WHERE username=?;";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, 'ss', $param_password, $param_username);

                // Set parameters
                $param_password = $renewpassword;
                $param_username = $username;

                // Attempt to execute the prepared statement
                if (!mysqli_stmt_execute($stmt)) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
    // ----------------------------------------------------- //
    // --- END OF USER UPDATE DETAILS VALIDATION SECTION --- //
    // ----------------------------------------------------- //

    // ------------------------------------------------------------------ //
    // --- ADMIN UPDATE CUSTOMER AND ADMIN DETAILS VALIDATION SECTION --- //
    // ------------------------------------------------------------------ //
    // user/admin update details from user/admin edit details page
    else if (isset($_POST['admin-update-customer-details']) || isset($_POST['admin-update-admin-details'])) {
        // Get id
        if (!empty($_GET["id"]) && isset($_GET["id"])) {
            $id =  test_input($_GET["id"]);
            $first_name = $last_name = $email = $phone_number = $username = $password = "";
            if (isset($_POST['admin-update-customer-details']))
                $sql = "SELECT `last_name`, `first_name`, `email`, `phone_number`, `password`, `username` FROM `customer` WHERE id=?";
            if (isset($_POST['admin-update-admin-details']))
                $sql = "SELECT `last_name`, `first_name`, `email`, `phone_number`, `password`, `username` FROM `admin` WHERE id=?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param('i', $id);
                // execute query
                $stmt->execute();
                // store result in $result
                if ($result = $stmt->get_result()) {
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $first_name = $row['first_name'];
                            $last_name = $row['last_name'];
                            $email = $row['email'];
                            $phone_number = $row['phone_number'];
                            $password = $row['password'];
                        }
                        // Free memory
                        $result->free_result();
                        // Close statement
                        $stmt->close();
                    } else {
                        echo "<p><em>$sql: No records were found.</em></p>";
                    }
                } else {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
            }
        }
        
        // Validate first name
        if (!empty($_POST["first_name"]) && isset($_POST["first_name"])) {
            $first_name_new = test_input($_POST["first_name"]);
            if ($first_name_new != $first_name) {
                // not matching regex, format error message
                if (!preg_match("/^[A-Za-z]+$/", $first_name_new)) {
                    $nameError = "Please enter an appropriate name!";
                    $nameSuccess = false;
                } else {
                    $editFirstNameSuccess = "Last name updated.";
                    $first_name_valid = true;
                }
            }
        }

        // Validate last name
        if (!empty($_POST["last_name"]) && isset($_POST["last_name"])) {
            $last_name_new = test_input($_POST["last_name"]);
            if ($last_name_new != $last_name) {
                // not matching regex, format error message
                if (!preg_match("/^[A-Za-z]+$/", $last_name_new)) {
                    $nameError = "Please enter an appropriate name!";
                    $nameSuccess = false;
                } else {
                    $editLastNameSuccess = "Last name updated.";
                    $last_name_valid = true;
                }
            }
        }

        // Validate email
        if (!empty($_POST["email"]) && isset($_POST["email"])) {
            $email_new = test_input($_POST["email"]);
            if ($email_new != $email) {
                // check if e-mail address is well-formed
                if (!is_valid_email($email_new)) {
                    $emailError = "Invalid email format!";
                    $editEmailSuccess = false;
                } else {
                    $emailSuccess = "Email updated.";
                    $email_valid = true;
                }
            }
        }

        // Validate phone number
        if (!empty($_POST["phone"]) && isset($_POST["phone"])) {
            $phone_number_new = test_input($_POST["phone"]);
            if ($phone_number_new != $phone_number) {
                // not matching regex, format error message
                if (!preg_match("/^\+?\d{0,13}/", $phone_number_new)) {
                    $phoneError = "Invalid phone number";
                    $editPhoneSuccess = false;
                } else {
                    $phoneSuccess = "Phone number updated.";
                    $phone_valid = true;
                }
            }
        }

        // Validate password
        // if 'current password' field has value
        if (!empty($_POST["oldpassword"]) && isset($_POST["oldpassword"])) {
            $currentpassword = test_input($_POST["oldpassword"]);
            if ($currentpassword == $password) {
                $passwordError = "Current password is incorrect!";
                $editPasswordSuccess = false;
            }
            else {
                $currentpassword_valid = true;
            }

            if ($currentpassword_valid) {
                // 'new password' field has value
                if (!empty($_POST["newpassword"]) && isset($_POST["newpassword"])) {
                    $newpassword = test_input($_POST["newpassword"]);
                    $newpassword_valid = true;
                    // field not empty, check if data is not null
                    if (!empty($_POST["renewpassword"]) && isset($_POST["renewpassword"])) {
                        $renewpassword = test_input($_POST["renewpassword"]);
                        if ($renewpassword == $newpassword) {
                            // run sql to check if new password is same with old password
                            if (isset($_POST['admin-update-customer-details']))
                                $sql = "SELECT `id` FROM `customer` WHERE username='$username' AND password=PASSWORD('$renewpassword')";
                            if (isset($_POST['admin-update-admin-details']))
                                $sql = "SELECT `id` FROM `admin` WHERE username='$username' AND password=PASSWORD('$renewpassword')";

                                // execute query and store results in $result
                            if ($result = mysqli_query($conn, $sql)) {
                                // no results in query
                                if (mysqli_num_rows($result) > 0) {
                                    $passwordError = "New password is the same as old password";
                                    $editPasswordSuccess = false;
                                    // Free result set (free up memory)
                                    mysqli_free_result($result);
                                } else {
                                    $passwordSuccess = "Password updated.";
                                    $renewpassword_valid = true;
                                }
                            } else {
                                echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                            }
                        } else {
                            $passwordError = "New password in fields don't match!";
                            $editPasswordSuccess = false;
                        }
                    } else {
                        $passwordError = "Please re-type your new password!";
                        $editPasswordSuccess = false;
                    }
                }
                // no value in new password field, store error message
                else {
                    $passwordError = "New password is required!";
                    $editPasswordSuccess = false;
                }
            }
        }
        else if (!empty($_POST["newpassword"]) && isset($_POST["newpassword"])) {
            $passwordError = "Please enter your current password!";
            $editPasswordSuccess = false;
        }
        else if (!empty($_POST["renewpassword"]) && isset($_POST["renewpassword"])) {
            $passwordError = "Please enter your current and new password!";
            $editPasswordSuccess = false;
        }
        
        if ($first_name_valid) {
            if (isset($_POST['admin-update-customer-details']))
                $sql = "UPDATE `customer` SET `first_name`=? WHERE id=?;";
            else if (isset($_POST['admin-update-admin-details']))
                $sql = "UPDATE `admin` SET `first_name`=? WHERE id=?;";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param('si', $first_name_new, $id);

                // execute query
                $status = $stmt->execute();
                if (!$status) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
                // Close statement
                $stmt->close();
            }
        }
        
        if ($last_name_valid) {
            if (isset($_POST['admin-update-customer-details']))
                $sql = "UPDATE `customer` SET `last_name`=? WHERE id=?;";
            else if (isset($_POST['admin-update-admin-details']))
                $sql = "UPDATE `admin` SET `last_name`=? WHERE id=?;";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param('si', $last_name_new, $id);

                // execute query
                $status = $stmt->execute();
                if (!$status) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
                // Close statement
                $stmt->close();
            }
        }
        
        if ($email_valid) {
            if (isset($_POST['admin-update-customer-details']))
                $sql = "UPDATE `customer` SET `email`=? WHERE id=?;";
            else if (isset($_POST['admin-update-admin-details']))
                $sql = "UPDATE `admin` SET `email`=? WHERE id=?;";
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param('si', $email_new, $id);

                // execute query
                $status = $stmt->execute();
                if (!$status) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
                // Close statement
                $stmt->close();
            }
        }
        if ($phone_valid) {
            if (isset($_POST['admin-update-customer-details']))
                $sql = "UPDATE `customer` SET `phone_number`=? WHERE id=?";
            else if (isset($_POST['admin-update-admin-details']))
                $sql = "UPDATE `admin` SET `phone_number`=? WHERE id=?";
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param('si', $phone_number_new, $id);

                // execute query
                $status = $stmt->execute();
                if (!$status) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
                // Close statement
                $stmt->close();
            }
        }

        if ($currentpassword_valid && $newpassword_valid && $renewpassword_valid) {
            if (isset($_POST['admin-update-customer-details']))
                $sql = "UPDATE `customer` SET `password`=PASSWORD(?) WHERE id=?;";
            else if (isset($_POST['admin-update-admin-details']))
                $sql = "UPDATE `admin` SET `password`=PASSWORD(?) WHERE id=?;";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param('si', $renewpassword, $id);

                // execute query
                $status = $stmt->execute();
                if (!$status) {
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
                }
                // Close statement
                $stmt->close();
            }
        }
    }
    // ------------------------------------------------------------------------- //
    // --- END OF ADMIN UPDATE CUSTOMER AND ADMIN DETAILS VALIDATION SECTION --- //
    // ------------------------------------------------------------------------- //
}

// unset($_SESSION);
// unset($_SESSION['cart']);

// debug print outs
// echo '$_FILES array';
// prePrintArray($_FILES);
// echo '$_GET array';
// prePrintArray($_GET);
echo '$_POST array';
prePrintArray($_POST);
echo '$_SESSION array';
prePrintArray($_SESSION);

?>