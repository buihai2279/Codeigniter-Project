<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public $table='product';
    public $url_api ='https://www.nganluong.vn/checkout.api.nganluong.post.php';  
    public $merchant_id = '45525';
    public $merchant_password = '2daa09faf06829a2d97bcde3b8ee2003';
    public $receiver_email = 'buihai2603@gmail.com';
    public $cur_code = 'vnd';
    public function __construct()
    {
        parent::__construct();
        $this->load->library('cart');
    }
    public function index()
    {
        $this->load->helper('form');
        $query = $this->db->query("SELECT name,slug,img,price,description FROM product");
        $data['result']=$query->result_array();
        $news = $this->db->query("SELECT id,title FROM news LIMIT 4");
        $data['news']=$news->result_array();
        $this->My_model->Load_front_end('home',$data);
    }
    public function cart_detail()
    {
        $this->load->helper('form');
        $this->load->view('front-end/cart_detail');
    }
    public function edit()
    {
        $this->load->helper('form');
        $this->My_model->Load_front_end('nganluong/pay');
    }
    public function update_cart()
    {
        if (isset($_POST['update'])) {
            $data = $_POST;
            $this->cart->update($data);
            $this->session->set_userdata('count_cart',count($this->cart->contents()));
            $this->My_model->Sent_message('Cập nhật thành công','home/edit','success');
        }else
            $this->My_model->Sent_message('Có lỗi xảy ra','home','danger');
    }
    public function pay()
    {
        if (isset($_POST)) {
            if ($this->session->has_userdata('mail')) {
                $user=$this->session->userdata('mail');
            } else {
                $user='';
            }
            foreach ($this->cart->contents() as $key => $value) {
                $data = array(
                        'id_order' => $id_order, 
                        'id_product' => $value['id'], 
                        'qty' => $value['qty'], 
                        'price' => $value['price'],
                    );
                $this->db->insert('order_detail',$data);
            }
            //sentmail
            unset($_SESSION['count_cart']);
            unset($_SESSION['cart_contents']);
            $this->My_model->Sent_message('Thanh toán thành công','home/pay_thank','success');
        } else {
            echo "string";
        }
    }
    public function Add_to_cart()
    {
        $data = array(
                    'id'      => $_POST['id'],
                    'qty'     => $_POST['qty'],
                    'price'   => $_POST['price'],
                    'img'   => $_POST['img'],
                    'link'   => current_url(),
                    'name'    => $_POST['name']
                );
        $this->cart->insert($data);
        echo "Thêm 1 sản phẩm vào giỏ hàng thành công";
    }
    public function slug($slug)
    {
        $data['result']=$this->My_model->get_row_by_slug($slug);
        if ($data['result']!=false) {
            $query = $this->db->select('name,img,slug,price,description')->order_by('name', 'RANDOM')->limit(4)->get($this->table);
            $data['suggest']=$query->result_array();
            $this->My_model->Load_front_end('info',$data);
        }else $this->My_model->Sent_message('Bạn đang truy cập vào đường link không tồn tại','home/index','danger');
    }
    public function count_cart()
    {
        $this->session->set_userdata('count_cart',count($this->cart->contents()));
        echo $_SESSION['count_cart'];
    }
    public function feedback()
    {
        $this->My_model->Load_front_end('front-end/feedback');
    }
    public function pay_thank()
    {
        $this->My_model->Load_front_end('nganluong/payment_success');
    }
    public function pay_cancel()
    {
        $this->db->where('order_code',$_GET['order_code'])->update('order',array('status'=>3));
        unset($_SESSION['count_cart']);
        unset($_SESSION['cart_contents']);
        $this->My_model->Sent_message('Cảm ơn bạn đã ghé thăm website','home','info');
    }
    public function save()
    {
        if (isset($_GET['token'])) {
            $token=$_GET['token'];
        }else $this->My_model->Sent_message('Error','home','danger');
        $nl_result=$this->GetTransactionDetail($token);
        if($nl_result){
            $nl_errorcode           = (string)$nl_result->error_code;
            $nl_transaction_status  = (string)$nl_result->transaction_status;
            if($nl_errorcode == '00') {
                if($nl_transaction_status == '00') {
                    $data = array(
                        'status'=> 0,
                        'total'=> $nl_result->total_amount,
                        'bank_code'=>$nl_result->bank_code,
                        'transaction_id'=>$nl_result->transaction_id,
                    );
                    $this->db->where('order_code',$nl_result->order_code)->update('order', $data);
                    unset($_SESSION['count_cart']);
                    unset($_SESSION['cart_contents']);
                    $this->My_model->Sent_message('Cảm ơn bạn đã mua hàng tại website,chúng tôi sẽ giao hàng đúng thời gian,hãy để lại feedback','home/feedback','info');
                }
            }else{
                echo $nlcheckout->GetErrorMessage($nl_errorcode);
            }
        }
    }



    protected  function GetTransactionDetail($token){    
                ###################### BEGIN #####################
                        $params = array(
                            'merchant_id'       => $this->merchant_id ,
                            'merchant_password' => MD5($this->merchant_password),
                            'version'           => '3.1',
                            'function'          => 'GetTransactionDetail',
                            'token'             => $token
                        );                      
                        $post_field = '';
                        foreach ($params as $key => $value){
                            if ($post_field != '') $post_field .= '&';
                            $post_field .= $key."=".$value;
                        }
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,$this->url_api);
                        curl_setopt($ch, CURLOPT_ENCODING , 'UTF-8');
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field);
                        $result = curl_exec($ch);
                        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
                        $error = curl_error($ch);
                        if ($result != '' && $status==200){
                            $nl_result  = simplexml_load_string($result);                       
                            return $nl_result;
                        }
                        return false;
                ###################### END #####################
          } 
}

/* End of file Home.php */
/* Location: ./application/controllers/Home.php */
