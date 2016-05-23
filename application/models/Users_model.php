<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Users_model extends CI_Model
{
    const TABLE = "users";

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Login check
     * @param string $email
     * @param string $password
     * @return boolean
     */
    public function login($email, $password)
    {
        $this->db->where("email", $email);
        $query = $this->db->get(self::TABLE, 1, 0);
        if ($query->num_rows() > 0) {
            $data = $query->result_array()[0];
            if (password_verify($password, $data["password"])) {
                $data = $query->row_array();
                $data["token"] = session_id();
                $this->session->set_userdata($data);
                return true;
            }
        }
        return false;
    }

    /**
     * Logout from website
     */
    public function logout()
    {
        $this->session->sess_destroy();
    }

    /**
     * Confirm user registration token
     * @param $token
     * @return mixed
     */
    public function confirm_user($token)
    {
        $this->db->where("confirm_token", $token);
        $this->db->update(self::TABLE, ["confirmed" => 1]);
        return $this->db->affected_rows();
    }

    /**
     * Check email existence in database
     * @param string $email
     * @return boolean
     */
    public function check_email($email)
    {
        $this->db->where("email", $email);
        $query = $this->db->get(self::TABLE, 1, 0);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Send email to user
     * @param string $email
     * @param string $subject
     * @param string $body
     * @return mixed
     */
    public function send_email($email, $subject, $body)
    {
        $this->config->load('email_phpmailer');

        $this->load->library('email');
        $this->email->from($this->config->item('smtp_from'), $this->config->item('smtp_from_name'));
        $this->email->to($email);
        $this->email->subject($subject);
        $this->email->message($body);
        $this->email->send(false);
        $this->email->print_debugger();

        /*
        $mail = new PHPMailer();
        if ($this->config->item('smtp_method') == "smtp") {
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = $this->config->item('smtp_secure');
            $mail->Host = $this->config->item('smtp_server');
            $mail->Port = $this->config->item('smtp_port');
            $mail->Username = $this->config->item('smtp_user');
            $mail->Password = $this->config->item('smtp_password');
        } elseif ($this->config->item('smtp_method') == "sendmail") {
            $mail->isSendmail();
        } else {
            $mail->isMail();
        }
        $mail->From = $this->config->item('smtp_from');
        $mail->FromName = $this->config->item('smtp_from_name');

        $mail->addAddress($email);
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return 0;
        }*/
    }

    /**
     * Add user to database
     * @param array $data
     * @return integer
     */
    public function add_user($data)
    {
        $this->db->insert(self::TABLE, $data);
        return $this->db->insert_id();
    }

}