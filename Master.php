<?php
if (session_id() === "")
    session_start();
require_once('DBConnection.php');
/**
 * Login Registration Class
 */
class Master extends DBConnection
{
    function __construct()
    {
        parent::__construct();
    }
    function __destruct()
    {
        parent::__destruct();
    }
    function save_topic()
    {
        if (!isset($_POST['user_id']))
            $_POST['user_id'] = $_SESSION['user_id'];
        foreach ($_POST as $k => $v) {
            if (!in_array($k, ['formToken']) && !is_array($_POST[$k]) && !is_numeric($v)) {
                $_POST[$k] = $this->escapeString($v);
            }
        }
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['topic-form'];
        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        } else {
            if (empty($topic_id)) {
                $sql = "INSERT INTO `topic_list` (`user_id`, `title`, `description`, `status`) VALUES ('{$user_id}', '{$title}', '{$description}', '{$status}')";
            } else {
                $sql = "UPDATE `topic_list` set `title` = '{$title}', `description` = '{$description}', `status` = '{$status}' where `topic_id` = '{$topic_id}'";
            }
            $qry = $this->query($sql);
            if ($qry) {
                $resp['status'] = 'success';
                if (empty($topic_id))
                    $resp['msg'] = 'New Topic has been addedd successfully';
                else
                    $resp['msg'] = 'Topic Data has been updated successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = 'Error:' . $this->lastErrorMsg() . ", SQL: {$sql}";
            }
        }
        return json_encode($resp);
    }
    function delete_topic()
    {
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['topics'];
        if (!isset($token) || (isset($token) && $token != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Token is invalid.";
        } else {
            $sql = "DELETE FROM `topic_list` where `topic_id` = '{$id}'";
            $delete = $this->query($sql);
            if ($delete) {
                $resp['status'] = 'success';
                $resp['msg'] = 'The Topic data has been deleted successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function save_comment()
    {
        if (!isset($_POST['user_id']))
            $_POST['user_id'] = $_SESSION['user_id'];
        foreach ($_POST as $k => $v) {
            if (!in_array($k, ['formToken']) && !is_array($_POST[$k]) && !is_numeric($v)) {
                $_POST[$k] = $this->escapeString($v);
            }
        }
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['comment-form'];
        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Form Token is invalid.";
        } else {
            if (empty($comment_id)) {
                $sql = "INSERT INTO `comment_list` (`user_id`, `topic_id`, `comment`) VALUES ('{$user_id}', '{$topic_id}', '{$comment}')";
            } else {
                $sql = "UPDATE `comment_list` set `comment` = '{$comment}' where `comment_id` = '{$comment_id}'";
            }
            $qry = $this->query($sql);
            if ($qry) {
                $resp['status'] = 'success';
                if (empty($comment_id))
                    $resp['msg'] = 'New comment has been addedd successfully';
                else
                    $resp['msg'] = 'Comment Data has been updated successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = 'Error:' . $this->lastErrorMsg() . ", SQL: {$sql}";
            }
        }
        return json_encode($resp);
    }
    function delete_comment()
    {
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['topicDetails'];
        if (!isset($token) || (isset($token) && $token != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Token is invalid.";
        } else {
            $sql = "DELETE FROM `comment_list` where `comment_id` = '{$comment_id}'";
            $delete = $this->query($sql);
            if ($delete) {
                $resp['status'] = 'success';
                $resp['msg'] = 'The comment data has been deleted successfully';
                $_SESSION['message']['success'] = $resp['msg'];
            } else {
                $resp['status'] = 'failed';
                $resp['msg'] = $this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function get_comment()
    {
        extract($_POST);
        $allowedToken = $_SESSION['formToken']['topicDetails'];
        if (!isset($formToken) || (isset($formToken) && $formToken != $allowedToken)) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Security Check: Token is invalid.";
        } else {
            $resp = $this->query("SELECT * FROM `comment_list` where `comment_id` = '{$comment_id}'")->fetchArray(SQLITE3_ASSOC);
        }
        return json_encode($resp);
    }
}
$a = isset($_GET['a']) ? $_GET['a'] : '';
$master = new Master();
switch ($a) {
    case 'save_settings':
        echo $master->save_settings();
        break;
    case 'save_topic':
        echo $master->save_topic();
        break;
    case 'delete_topic':
        echo $master->delete_topic();
        break;
    case 'save_comment':
        echo $master->save_comment();
        break;
    case 'delete_comment':
        echo $master->delete_comment();
        break;
    case 'get_comment':
        echo $master->get_comment();
        break;
    default:
        // default action here
        break;
}
