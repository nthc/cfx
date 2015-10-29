<?php

class CfxAuthUsersController extends ModelController
{
    private static $permissionsModel;
    
    public $listFields = array(
        "auth.users.user_id",
        "auth.users.user_name",
        "auth.users.first_name",
        "auth.users.last_name"
    );
    public $modelName = "auth.users";

    public function __construct()
    {
        parent::__construct();
        $this->table->addOperation('roles', "Add Role(s)");
        $this->table->addOperation('reset_password', "Reset Password");
        $this->table->addOperation('disable_user', "Disable User","javascript:wyf.confirmRedirect('Are you sure you want to disable user?','{$this->urlPath}/%path%/%key%')");
    }

    public function reset_password($params)
    {
        $this->model->queryResolve = false;
        $user = $this->model->getWithField2('user_id', $params[0]);
        $user[0]['user_status'] = '4';
        $this->model->setData($user[0]);
        $this->model->update('user_id', $params[0]);
        Application::redirect($this->urlPath . "?notification=User's password reset");
    }
    
    public function disable_user($params)
    {
        $this->model->queryResolve = false;
        $user = $this->model->getWithField2('user_id', $params[0]);
        $user[0]['user_status'] = '0';
        $this->model->setData($user[0]);
        $this->model->update('user_id', $params[0]);
        Application::redirect($this->urlPath . "?notification=User has been disabled");
    }

    //Add roles to user
    public function roles($params)
    {
        //Load necessary models
        $usersModel = Model::load("auth.users");
        $usersRolesModel = Model::load("auth.users_roles");
        $rolesModel = Model::load("auth.roles");

        //required queries
        $user = $usersModel->getWithField("user_id", $params[0]);
        $usersRoles = $usersRolesModel->getWithField("user_id", $params[0]);
        $loggedInUsersRoles = $usersRolesModel->getWithField("user_id", $_SESSION['user_id']);
        $roles = $rolesModel->get();

        $this->label = "Select Role(s) for " . $user[0]['first_name'] . " " . $user[0]['last_name'];

        //create a new form
        $form = new Form();
        $form->setRenderer("table");
        $fieldset = Element::create('ColumnContainer', 3);
        $form->add($fieldset);

        foreach ($roles as $role) {
            if ($role['role_id'] == 1) {
                //Boolean to determine if the outer foreach loop should "continue" particular loop or not
                $continueBool = false;
                //Loop through all the current user's
                foreach ($loggedInUsersRoles as $userRole) {
                    if ($userRole['role_id'] == 1) {
                        $continueBool = false;
                        break;
                    } else {
                        $continueBool = true;
                    }
                }
                if ($continueBool) {
                    continue;
                }
            }

            $checkbox = Element::create("Checkbox", $role['role_name'], self::underscore($role['role_name']), "", $role['role_id']);
            foreach ($usersRoles as $userRole) {
                if ($userRole['role_id'] == $role['role_id']) {
                    $checkbox->setValue($role['role_id']);
                }
            }

            $fieldset->add($checkbox);
        }

        $userIdHiddenField = Element::create("HiddenField", "user_id", $params[0]);

        $form->add($userIdHiddenField);
        $form->setValidatorCallback("{$this->getClassName()}::roles_callback");
        $form->setShowClear(false);

        //render the form
        return $form->render();
    }

    public static function roles_callback($data, $form)
    {
        $usersRolesModel = Model::load("auth.users_roles");
        
        $usersRolesModel->datastore->beginTransaction();

        $userId = array_pop($data);

        $loggedInUsersRoles = $usersRolesModel->getWithField("user_id", $_SESSION['user_id']);

        //this is for hackers who try to use scripts of a kind to bypass the UI..this throws an exception to prevent
        //the user from giving himself super user access
        //the exception is thrown and basically the use's roles are deleted from the table -> bug or not
        //If a user tries to set the role to 1 and the user is not super user throw exception
        foreach ($data as $role) {
            if ($role == 1) {

                foreach ($loggedInUsersRoles as $userRole) {
                    if ($userRole['role_id'] == 1) {
                        $throwException = false;
                        break;
                    } else {
                        $throwException = true;
                    }
                }

                if ($throwException) {
                    throw new Exception('Unauthorised Action');
                }
            }
        }

        //delete all the entries related to that user
        $usersRolesModel->delete('user_id', $userId);

        //defaults to true and changes to false if the logged in user is really superuser
        $throwException = true;

        foreach ($data as $role) {

            if ($role != 0) {
                $usersRolesModel->setData(array(
                    'user_id' => $userId,
                    'role_id' => $role
                ));
                $usersRolesModel->save();
            }
        }
        
                
        $menuFile = __DIR__ . "/cache/menus/side_menu_u{$userId}.html";
        $objectFile = __DIR__ . "/cache/menus/menu_u{$userId}.object";
        
        //delete menu & object file for user
        unlink($menuFile);
        unlink($objectFile);
        
        //generate menu for user
        AuthMenu::generate($userId);
        
        $usersRolesModel->datastore->endTransaction();

        Application::redirect("/auth/users?notification=Role(s) saved successfully");

        return true;
    }

    private static function underscore($word)
    {
        $retWord = explode(" ", strtolower($word));

        return join("_", $retWord);
    }

}
