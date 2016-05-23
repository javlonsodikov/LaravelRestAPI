<?php
/**
 * Created by PhpStorm.
 * Author: Javlon Sodikov
 * Date time: 26.04.2016 11:29
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('todo_model');
        $this->lang->load("api", $this->config->item('language'));
    }

    public function index()
    {
        return $this->_success(["commands" =>
                                    ["list"   => "/api/list",
                                     "add"    => "/api/add",
                                     "delete" => "/api/delete",
                                     "edit"   => "/api/edit"
                                    ]
        ]);
    }

    private function _success($data = [], $return_code = 200)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($return_code)
            ->set_output(json_encode(array_merge(["success" => true], $data)));
    }

    /**
     * Register new user and send confirmation email
     */
    public function create_user()
    {
        $email = $this->input->get('email');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->_error(["error" => $this->lang->line('verify_email_address')], 422);
        }
        if ($this->users_model->check_email($email)) {
            return $this->_error(["error" => $this->lang->line('email_already_registered')], 400);
        }
        $name = $this->input->get('name');
        $password = $this->input->get('password');
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $confirm_token = sha1($this->input->get_post('email') . $this->config->item('encryption_key'));
        $data = ["name" => $name, "email" => $email, "password" => $hash, "confirm_token" => $confirm_token];
        $this->users_model->add_user($data);

        $this->load->helper('url');
        $confirm_code = site_url("/api/confirm_user/?token=$confirm_token");
        //$confirmation = sprintf($this->lang->line('user_email_confirmation_body'), $confirm_code, $confirm_code);
        $confirmation = $confirm_code;
        $res = $this->users_model->send_email($email, $this->lang->line('verify_email_address'), $confirmation);
        if ($res == 0) {
            return $this->_success(["status" => $this->lang->line('verification_email_sent')], 201);
        } else {
            return $this->_error(["error" => $this->lang->line('account_created_but_verification_email_not_sent')]);
        }
    }

    private function _error($data, $error_code = 400)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header($error_code)
            ->set_output(json_encode(array_merge(["success" => false], $data)));
    }

    /**
     * User confimation check
     */
    public function confirm_user()
    {
        if ($this->users_model->confirm_user($this->input->get('token')) == false) {
            return $this->_error(["error" => $this->lang->line('something_went_wrong')]);
        } else {
            return $this->_success(["message" => $this->lang->line('email_verified')]);
        }
    }

    /**
     * User login action
     */
    public function login_user()
    {
        $email = $this->input->get('email');
        $password = $this->input->get('password');
        if ($email && $password) {
            $result = $this->users_model->login($email, $password);
            if ($result) {
                //TODO: token should be expirable
                return $this->_success(["token" => session_id()]);
            } else {
                return $this->_error(["error" => $this->lang->line('login_or_password_incorrect')], 401);
            }
        } else {
            return $this->_error(["error" => $this->lang->line('please_provide_login_and_password')], 401);
        }
    }

    public function add_todo()
    {
        if ($this->session->userdata('user_id') != ""
            && $this->session->userdata('token') == $this->input->get("token")
        ) {
            $todo_title = $this->input->get('todo_title');
            if ($todo_title) {
                $user_id = $this->session->userdata('user_id');
                $data = array(
                    'todo_title' => $todo_title,
                    'user_id'    => $user_id
                    /*,
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'password' => sha1($this->input->post('password'))*/
                );

                $insert_id = $this->todo_model->add_todo($data);
                return $this->_success(["id" => $insert_id], 201);
            } else {
                return $this->_error(["error" => $this->lang->line('please_add_title')], 400);
            }
        } else {
            return $this->_error(["error" => $this->lang->line('unauthorized')], 401);
        }
    }

    public function edit_todo()
    {
        if ($this->session->userdata('user_id') != ""
            && $this->session->userdata('token') == $this->input->get("token")
        ) {
            $todo_title = $this->input->get('todo_title');
            if ($todo_title) {
                $user_id = $this->session->userdata('user_id');
                $todo_id = $this->input->get('todo_id');
                $data = [
                    'todo_title' => $todo_title,

                ];
                $where = [
                    'user_id' => $user_id,
                    'todo_id' => $todo_id
                ];

                $this->todo_model->update_todo($data, $where);
                return $this->_success();
            } else {
                return $this->_error(["error" => $this->lang->line('please_add_title')]);
            }
        } else {
            return $this->_error(["error" => $this->lang->line('unauthorized')], 401);
        }
    }

    public function list_todo()
    {
        if ($this->session->userdata('user_id') != ""
            && $this->session->userdata('token') == $this->input->get("token")
        ) {
            $rows = $this->todo_model->list_todo();
            return $this->_success($rows);
        } else {
            return $this->_error(["error" => $this->lang->line('unauthorized')], 401);
        }
    }

    public function delete_todo()
    {
        if ($this->session->userdata('user_id') != ""
            && $this->session->userdata('token') == $this->input->get("token")
        ) {
            $todo_id = $this->input->get('todo_id');
            $data = array("todo_id" => $todo_id, "user_id" => $this->session->userdata('user_id'));
            if ($this->todo_model->delete_todo($data)) {
                return $this->_success();
            } else {
                return $this->_error(["error" => $this->lang->line('todo_not_found')], 404);
            }
        } else {
            return $this->_error(["error" => $this->lang->line('unauthorized')], 401);
        }
    }
}
