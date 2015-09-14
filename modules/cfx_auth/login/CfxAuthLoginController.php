<?php
/**
 * A Controller which validates a user and logs her in. This controller allows
 * first time users to create a new password when they log in.
 *
 * @author Dkanda & Ekowabaka
 *
 */
class CfxAuthLoginController extends Controller
{
    /**
     * A method which allows the user to change their password if they are
     * logging in for the forst time.
     * @return unknown_type
     */
    public function change_password()
    {
        Application::addStylesheet("css/login.css");
        Application::$template = "login.tpl";
        Application::setTitle("Change Password");
        $text = null;

        $form = new Form();
        $form->setRenderer("default");
        $password = new PasswordField("Password", "password");
        $password->setEncrypted(false);
        $form->add($password);

        $passwordRetype = new PasswordField("Retype Password", "password2");
        $passwordRetype->setEncrypted(false);
        $form->add($passwordRetype);
        $form->setCallback($this->getClassName() . "::change_password_callback", null);
        $form->setShowClear(false);
        $form = $form->render();

        if($_SESSION['user_status'] == "2")
        {
            $text = "<h2>Change Password</h2>"
            . "<p>It appears that this is the first time you are logging in. "
            . "Please change your password.</p> $form";
        }
        else
        {
            $text = "<h2>Change Password</h2>"
            . "<p>Your current password has expired. Please enter a new password. Please note that your password must be 6 characters or more and must contain at least one lowecase letter and a number.<br/>&nbsp</p>"
            . "$form";
        }

        return $text;
    }

    //CALLBACK FUNCTION FOR THE CHANGE PASSWORD FORM
    public static function change_password_callback($data, $form, $callback)
    {
        $checkErrors = self::checkPasswords($data["password"],$data["password2"]);

        if(empty($checkErrors))
        {
            //if errors are empty, login user
            self::loginUser($data);
        }
        else
        {
            //Display errors
            foreach($checkErrors as $error)
            {
                $form->addError($error);
            }
        }
        return true;
    }

    //log user into software after validation of password checks out
    private static function loginUser($data)
    {
        $users = Model::load("auth.users");
        $userData = $users->getWithField("user_id", $_SESSION["user_id"]);

        Configuration::set('attempt_counter_'.$userData[0]['user_name'], 0);
        $userData[0]["password"] = md5($data["password"]);
        $userData[0]["user_status"] = 1;
        $userData[0]["last_login_time"] = time();
        $users->setData($userData[0]);
        $users->update("user_id", $_SESSION["user_id"]);
        
        unset($_SESSION["user_mode"]);
        User::log("Password changed after first log in");
        self::storePassword($data["password"]);
        Application::redirect(self::getHomeRedirect());
    }

    //function to store password into the password history table
    private static function storePassword($password)
    {
        $passwordHistory = Model::load('auth.password_history');
        $passwordHistory ->setData(array(
            'password' => md5($password),
            'user_id' => $_SESSION['user_id'],
            'time' => time()
        ));

        $passwordHistory->save();
    }

    public static function checkPasswords($password,$confirmPassword)
    {
        $prevPasswordsLimit = 12;
        //$pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[-+_!@#$%^&*.,?~]).+$/";
        $pattern = "/^(?=.*[a-z])(?=.*\d).+$/";
        $errors = array();

        if(strlen($password) < 6)
        {
            $errors[] = "Password must be 6 characters or more";
        }

        if(! preg_match($pattern,$password))
        {
            $errors[] = "Password must contain at least 1 lowercase letter and 1 number";
        }

        if($password !== $confirmPassword)
        {
            $errors[] = "Passwords do not match";
        }

        if(!self::checkPreviousPasswords($password,$prevPasswordsLimit))
        {
            $errors[] = "You cannot use any of your last " . $prevPasswordsLimit . " passwords";
        }

        return $errors;
    }

    //function to check last 'x' passwords to prevent user from changing password to any previous 'x' passwords
    //User is not allowed to use any of his/her last "$limit" passwords
    private static function checkPreviousPasswords($password,$limit)
    {
        $passwordHistory = Model::load('auth.password_history');
        $passwordCheck = $passwordHistory->get(
            array(
                "filter" => "user_id=? AND password=?",
                "bind" => array($_SESSION['user_id'],md5($password)),
                "sort_field"=> "time DESC",
                "limit" => $limit
            ), Model::MODE_ASSOC, false, false);

        if(count($passwordCheck) == 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    //THE MAIN LOGIN CONTENTS
    public function getContents()
    {
//          if(Configuration::get('attempt_counter') == null || Configuration::get('attempt_counter') === '0')
//        {
//            Configuration::set('attempt_counter',0);
//        }

        Application::addStylesheet("css/login.css");
        Application::$template = "login.tpl";
        Application::setTitle("Login");

        if ($_SESSION["logged_in"])
        {
            Application::redirect("/");
        }

        $form = new Form();
        $form->setRenderer("default");
        $username = new TextField("Username","username");
        $form->add($username);
        $password = new PasswordField("Password","password");
        $password->setEncrypted(false);
        $form->add($password);
        $form->setSubmitValue("Login");
        $form->setValidatorCallback("{$this->getClassName()}::callback");
        $form->setShowClear(false);

        return $form->render();
    }

    //WHERE THE MAIN LOGIN AUTHENTICATION OCCURS
    public static function callback($data, $form, $callback_pass = null)
    {
        self::performLoginAuth($data, $form);
    }

    //Authenticate the user login
    private static function performLoginAuth($data,$form)
    {
        $user = Model::load("auth.users");
        $userData = $user->get(
            array(
                "filter" => "user_name=?",
                "bind" => array($data["username"])
            ), Model::MODE_ASSOC, false, false);
        
        if(count($userData) == 0)
        {
            $form->addError("Please check your username or password");
            self::handleNumAttempts($userData);
            return true;
        }
        // Called Auth::getPermission instead of User::getPermission
        else if(Auth::getPermission("can_log_in_to_web", $userData[0]["role_id"],$userData[0]['user_id']))
        {
            /* Verify the password of the user or check if the user is logging in
             * for the first time.
             */

            if($userData[0]["password"] === md5($data["password"]) || $userData[0]["user_status"] == 2 || $userData[0]["user_status"] == 4)
            {
                self::performUserStatusCheckAction($form, $userData);
            }
            else
            {
                if($userData[0]["user_status"] == 3)
                {
                    $form->addError("Your account has been disabled as a result "
                          . "of too many login attempts. Please contact the system administrator");
                    return true;
                }
                else
                {
                    $form->addError("Please check your username or password");
                    self::handleNumAttempts($userData);
                    return true;
                }
            }
        }
        else
        {
            $form->addError("You are not allowed to log in from this terminal");
            self::handleNumAttempts($userData);
            return true;
        }

    }

    //Function to check the user_status and perform appropriate action
    private static function performUserStatusCheckAction($form,$data)
    {
        switch ($data[0]["user_status"])
        {
            case "0":
                $form->addError("Your account is currently inactive"
                          . "please contact the system administrator.");
                return true;
                break;

            case "1":
//                if(self::checkConstraints($data))
//                {
                    self::authSuccess($data);
//                }
//                else
//                {
//                    $form->addError("You are not authorized to login at this time of this day");
//                    self::handleNumAttempts($data);
//                    return true;
//                }
                break;

            case "2":// user_status = 2 means the user is new and they should change their password
                self::redirectToChangePassword($data);
                break;

            case "3":
                $form->addError("Your account has been disabled as a result "
                          . "of too many login attempts. Please contact the system administrator");
                    return true;
                break;

            case "4":// user_status = 4 means the user should change their password
                self::redirectToChangePassword($data);
                break;
        }
    }

    private static function checkConstraints($data)
    {
        $currentDayOfWeek = strtolower( jddayofweek ( cal_to_jd(CAL_GREGORIAN, date("m"),date("d"), date("Y")) , 1 ) );
        $currentTime = date('H:i');

        $constraintsModel = Model::load('auth.constraints');

        $constraints = $constraintsModel->get(
            array(
                "filter" => "role_id=? AND mode=?",
                "bind" => array($data[0]['role_id'],'allow')
            ), Model::MODE_ASSOC, false, false);


        if(count($constraints) == 0)
        {
            return false;
        }
        else
        {
            foreach($constraints as $constraint)
            {
                if(self::authenticateDayOfWeek($currentDayOfWeek, $constraint['days_of_week_value']))
                {
                    if(self::authenticateTimeRange($currentTime, $constraint['time_range_start'], $constraint['time_range_end']))
                    {
                        return true;
                    }
                }

            }

            return false;
        }

        return false;
    }

    private static function authenticateTimeRange($currentTime,$startTime,$endTime)
    {
        $current = DateTime::createFromFormat('H:i', $currentTime);
        $start = DateTime::createFromFormat('H:i', $startTime);
        $end = DateTime::createFromFormat('H:i', $endTime);

        if ($current >= $start && $current <= $end)
        {
           return true;
        }
        else
        {
            return false;
        }
    }

    private static function authenticateDayOfWeek($day,$day_of_week_value)
    {
       $dayOfWeek = array(
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 4,
            'thursday' => 8,
            'friday' => 16,
            'saturday' => 32,
            'sunday' => 64
        );

       if($dayOfWeek[$day] & $day_of_week_value)
       {
           return true;
       }
       else
       {
           return false;
       }

    }


    //login user after a successful username and apssword authentication
    private static function authSuccess($data)
    {
        Configuration::set('attempt_counter_'.$data[0]['user_name'], 0);
        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $data[0]["user_id"];
        $_SESSION["user_name"] = $data[0]["user_name"];
        $_SESSION["user_firstname"] = $data[0]["first_name"];
        $_SESSION["user_lastname"] = $data[0]["last_name"];
        $_SESSION["read_only"] = $data[0]['read_only'];
        $_SESSION["role_id"] = $data[0]["role_id"];
        $_SESSION['branch_id'] = $data[0]['branch_id'];
        $_SESSION["department_id"] = $data[0]['department_id'];
        
        //check role to see if the permission has changed and update the menu tree
        self::checkRolePermissionChange($data);
        
        self::updateLastLoginTime($data);
        
        Sessions::bindUser($data[0]['user_id']);
        User::log("Logged in");
        Application::redirect(self::getHomeRedirect());
    }
    
    public static function checkRolePermissionChange($data)
    {
        $userId = $data[0]['user_id'];
        
        $userModel = Model::load("auth.users");
        $userData = $userModel ->getWithField("user_id", $data[0]['user_id']);

        $roleValidityModel = Model::load('auth.role_validity');
        $roleValidityData = $roleValidityModel->getWithField("role_id", $data[0]['role_id']);
        
        if(count($roleValidityData) > 0)
        {
            if($userData[0]['last_login_time'] < $roleValidityData[0]['last_modified'])
            {
                AuthMenu::generate($userId);
            }
        }
    }
    
    public static function updateLastLoginTime($data)
    {
        $user = Model::load("auth.users");
        $userData = $user->getWithField("user_id", $data[0]['user_id']);
            
        $userData[0]["last_login_time"] = time();
        
        $user->setData($userData[0]);
        $user->update("user_id", $_SESSION["user_id"]);

        $user->update("user_id", $data[0]['user_id']);
    }

    //function to redirect the user to change their password
    private static function redirectToChangePassword($data)
    {
        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $data[0]["user_id"];
        $_SESSION["user_name"] = $data[0]["user_name"];
        $_SESSION["role_id"] = $data[0]["role_id"];
        $_SESSION["department_id"] = $data[0]['department_id'];
        $_SESSION["user_firstname"] = $data[0]["first_name"];
        $_SESSION["user_lastname"] = $data[0]["last_name"];
        $_SESSION['branch_id'] = $data[0]['branch_id'];
        $_SESSION["user_mode"] = "2";
        $_SESSION["user_status"] = $data[0]['user_status'];//this is added to pass the user status as wyf hard codes the "2" to redirect
        Sessions::bindUser($data[0]['user_id']);
        User::log("Logged in for first time");
        Application::redirect(self::getHomeRedirect());
    }

    private static function handleNumAttempts($data)
    {
        $attempts = Configuration::get('attempt_counter_'.$data[0]['user_name']);

        //check if an attempt was made and log the first attempt
        if(Configuration::get('attempt_counter_'.$data[0]['user_name']) == '0')
        {
            Configuration::set('time_first_try_'.$data[0]['user_name'],time());
        }

        //Increment the attempts if and only if the username exists
        if(!empty($data))
        {
            Configuration::set('attempt_counter_'.$data[0]['user_name'], ++$attempts);
        }

        $timeElapsed = self::checkElapsedTime(Configuration::get('time_first_try_'.$data[0]['user_name']), time());

        if(Configuration::get('attempt_counter_'.$data[0]['user_name']) >= '2')
        {
            if($timeElapsed -> i < 5)
            {
                self::disableUserAccount($data);
            }
            else
            {
                Configuration::set('attempt_counter_'.$data[0]['user_name'], 0);
            }
        }
    }

    //Function to determine if login attempts is within "$limit" milliseconds in order to disable user account
    private static function checkElapsedTime($referenceTime,$currentTime)
    {
        $date = new DateTime();
        $date->setTimestamp($referenceTime);
        $timeDifference = $date->diff((new DateTime())->setTimestamp($currentTime));
        return $timeDifference;
    }

    private static function getHomeRedirect()
    {
        return Application::getLink("/");
    }


    //Disable user account if number of tries exceeds 3
    private static function disableUserAccount($data)
    {
        $user = Model::load("auth.users");
        $userData = $user->get(
            array(
                "filter" => "user_name=?",
                "bind" => array($data[0]["user_name"])
            ), Model::MODE_ASSOC, false, false);

        if($userData !== null)
        {
            $userData[0]['user_status'] = '3';
            $user->setData($userData[0]);
            $user->update('user_id', $userData[0]['user_id']);
        }
    }

}
