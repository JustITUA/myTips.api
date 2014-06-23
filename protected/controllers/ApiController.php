<?php

class ApiController extends Controller {
    // Members

    Const APPLICATION_ID = 'heydaraliyevcenter.api';

    private $format = 'json';

    /**
     * @return array action filters
     */
    public function filters() {
        return array();
    }

    // Actions
    public function actionList() {
        switch ($_GET['model']) {
            case 'test':
                $output = array("message" => "OK");
                break;
            default:
                // Model not implemented error
            	$this->error(501, sprintf('Error: Mode list is not implemented for model %s', $_GET['model']));
        }
        // Send the response
        $this->_sendResponse(200, CJSON::encode($output));
    }

    public function actionView() {
        $this->_checkAuth(true);
        // Check if id was submitted via GET
        if (!isset($_GET['id']))
        	$this->error(500, 'Error: Parameter id is missing');
            
        switch ($_GET['model']) {
            // Find respective model    
            case 'none':
                break;
            default:
                $this->error(501, sprintf('Error: Mode list is not implemented for model %s', $_GET['model']));
        }
        $this->_sendResponse(200, CJSON::encode($model));
    }

    public function actionCreate() {
        switch ($_GET['model']) {

            case 'screenshot':
            	
            	$target_path = "uploads/";
            	$file_name = "screen.png";
            	$target_path = $target_path . $file_name;
            	
            	$image = $_FILES ['userfile'];
            	
            	
            	if (move_uploaded_file ( $image ['tmp_name'], $target_path )) {
            		echo "Done";
            	} else
            		echo "Error";

                break;
            default:
                // Model not implemented error
                $this->error(501, sprintf('Error: Mode list is not implemented for model %s', $_GET['model']));
        }
    }

    public function actionUpdate($USER_ID, $AUTH_PHONE) {
        $this->_checkAuth(true);
        $json = file_get_contents('php://input');
        $put_vars = CJSON::decode($json, true);

        switch ($_GET['model']) {
            case 'none':

                break;
            default:
                $this->error(501, sprintf('Error: Mode list is not implemented for model %s', $_GET['model']));
        }
        $this->_sendResponse(200, CJSON::encode($output));
    }

    public function actionDelete($USER_ID, $AUTH_PHONE) {
        $this->_checkAuth(true);
        $id = $_GET['id'];

        switch ($_GET['model']) {
            // Load the respective model
            case 'none':

                break;
            default:
                $this->error(501, sprintf('Error: Mode list is not implemented for model %s', $_GET['model']));
        }
        $this->_sendResponse(200, "ok");
    }

    private function _sendResponse($status = 200, $body = '', $content_type = 'text/json') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
</head>
<body>
    <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
    <p>' . $message . '</p>
    <hr />
    <address>' . $signature . '</address>
</body>
</html>';

            echo $body;
        }
        Yii::app()->end();
    }

    private function _getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Not activated',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    private function _checkAuth($check_is_active = false) {
        // Check if we have the PHONE and TOKEN HTTP parameters set?
        if (!( isset($_GET['AUTH_PHONE']) and isset($_GET['AUTH_TOKEN']) and isset($_GET["AUTH_TIMESTAMP"]) )) {
            // Error: Unauthorized
            $this->_sendResponse(401);
        }
        $userphone = $_GET['AUTH_PHONE'];
        $token = $_GET['AUTH_TOKEN'];
        $timestamp = $_GET['AUTH_TIMESTAMP'];
        // Find the user
        $user = User::model()->find('LOWER(phone)=?', array(strtolower($userphone)));
        if ($user === null) {
            // Error: Unauthorized
            $this->_sendResponse(401, 'Пользователя не существует');
        } else if (!$user->validatePassword($userphone, $token, $timestamp)) {
            // Error: Unauthorized
            $this->_sendResponse(401, 'Неверный номер телефона/пароль');
        }

        if ($check_is_active && $user->is_active == 0)
            $this->_sendResponse(402, 'Пользователь не активирован');

        if ($user->is_blocked == 1)
            $this->_sendResponse(401, 'Пользователь заблокирован');

        if ($_GET['USER_ID'] > -1 && $user->id != $_GET['USER_ID'])
            $this->_sendResponse(402, 'Передан неверный идентификатор пользоателя');
    }

    private function _selectCurrentUser($AUTH_PHONE) {
        $criteria = new CDbCriteria;
        $criteria->addSearchCondition("phone", $AUTH_PHONE);
        $criteria->with = array(
            'profile',
        );
        $criteria->select = "id, phone, phone_code, first_name, middle_name, last_name, email, photo, is_active";
        $user = User::model()->find($criteria);
        return $user;
    }
    
    private function error($code, $message) {
    	$message = CJSON::encode(
    			array("error" => array("code" => $code,
    								   "message" =>$message)
	    			));
    	$this->_sendResponse($code, $message);
    	Yii::app()->end();
    }

}