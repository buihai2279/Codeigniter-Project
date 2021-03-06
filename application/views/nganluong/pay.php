<div class="container"><br><hr><br>
<?php if (count($this->cart->contents()) > 0) {?>
<form action="<?php echo base_url('home/update_cart'); ?>"  method='POST'>
<table cellpadding="6" cellspacing="1" style="width:100%" border="0">
<tr><th>IMG</th> <th>Qty</th> <th>Name</th> <th style="text-align:right">Item Price</th> <th style="text-align:right">Sub-Total</th><th style="text-align:right">Delete</th>
</tr>
<?php $i = 1;?>
<?php foreach ($this->cart->contents() as $items): ?>
        <?php echo form_hidden($i . '[rowid]', $items['rowid']); ?>
        <tr style="border-bottom: 1px solid #f1f1f1;">
            <td><img src="<?php echo $items['img']; ?>" class='img-responsive' style='max-height: 150px;'></td>
            <td><?php echo form_input(array('name' => $i . '[qty]', 'value' => $items['qty'], 'maxlength' => '2', 'size' => '5')); ?></td>
            <td>
                <?php echo $items['name']; ?>
            </td>
            <td style="text-align:right"><?php echo $this->cart->format_number($items['price']); ?></td>
            <td style="text-align:right">$<?php echo $this->cart->format_number($items['subtotal']); ?></td>
            <td style="text-align:right"><a href="<?php echo base_url('home/delete_cart/'.$items['rowid']); ?>"><i class="fa fa-trash"></i></a></td>
        </tr>
<?php $i++;?>
<?php endforeach;?>
<tr>
<td colspan="5"></td>
<td  class="pull-right">
    <?php echo form_submit(array('class' => 'btn btn-info', 'name' => 'update'), 'Update'); ?>
</td>
    </tr>
    <?php if ($this->cart->total()>100000) {
        $ship=0;
    } else {
        $ship=$this->fee_shipping;
    }
    if (isset($_SESSION['cart_contents'])) {
        $total=($this->cart->total()+$ship)+$this->tax_amount*.01*$this->cart->total()-$this->discount_amount*.01*$this->cart->total();
    } else {
        $total=0;
    }
     ?>
<tr>
    <td colspan="4"></td>
    <td class="pull-left"><strong>Tổng cộng</strong></td>
    <td class="pull-right"><?php echo number_format($this->cart->total()); ?> VNĐ</td>
</tr>
<tr>
    <td colspan="4"></td>
    <td class="pull-left"><strong>Phí ship</strong></td>
    <td class="pull-right"><?php echo $ship; ?> VNĐ</td>
</tr>
<tr>
    <td colspan="4"></td>
    <td class="pull-left"><strong>Thuế </strong>(<?php echo $this->tax_amount; ?>%)</td>
    <td class="pull-right"><?php echo number_format($this->tax_amount*.01*$this->cart->total()); ?></td>
</tr>
<tr>
    <td colspan="4"></td>
    <td class="pull-left"><strong>Chiết khấu(<?php echo $this->discount_amount; ?>%)</strong></td>
    <td class="pull-right"><?php echo $this->discount_amount*.01*$this->cart->total()?></td>
</tr>
<tr>
    <td colspan="4"></td>
    <td class="pull-left"><strong>Số tiền phải thanh toán</strong></td>
    <td class="pull-right"><?php echo number_format($total); ?> VNĐ</td>
</tr>
</table>
</form>
<div class="clearfix"></div>
<?php } else {
    echo '<div class="alert alert-warning">Không có sản phẩm nào trong giỏ hàng</div>';
    echo "Mua Laptop";
    echo "Mua Smartphone";
    echo "Mua Phụ Kiện";
    echo "Mua Tablet";
}?>
<br><hr><br>

<?php
if (@$_POST['nlpayment']) {
        $user=(isset($_SESSION['mail'])) ? $_SESSION['mail'] : '' ;
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $date_time = date('Y-m-d H:i:s');
            $code              = substr(str_shuffle("ABCDEFGHABCDEFGHIKLMNOPQRABCDEFGHSTUVWXYZ"), 0, 8);
            $order_code        = $code.'_'. time();
            $data = array(
                        'user'=> $user,
                        'date_order'=> $date_time,
                        'order_code'=>$order_code,
                        'date_ship'=>$_POST['date_ship'],
                        'receiver_name'=> $_POST['buyer_fullname'],
                        'contact'=> $_POST['buyer_mobile'],
                        'mail'=>$_POST['buyer_email'],
                        'note'=> $_POST['order_description'],
                        'status'=> 0,
                        'fee_shipping'=>'',
                        'bank_code'=>'',
                        'address_ship'=>$_POST['address'],
                        'transaction_id'=>'',
                        'tax_amount'=>'',
                        'discount_amount'=>''
                );
    $this->db->insert('order', $data);
    include 'NL_Checkoutv3.php';
    $nlcheckout     = new NL_CheckOutV3('45525', '2daa09faf06829a2d97bcde3b8ee2003', 'buihai2603@gmail.com', 'https://www.nganluong.vn/checkout.api.nganluong.post.php');
    $total_amount   = $_POST['total_amount'];
    foreach ($this->cart->contents() as $key => $value) {
        $array_items[$key] = array(
                    'item_name'.$key+1 => $value['name'],
                    'item_quantity'.$key+1                    => $value['qty'],
                    'item_amount'.$key+1                     => $total_amount,
                    'id'.$key+1                           => $value['id'],
                    'link'=> $value['link']
            );
        }
    $array_items       = array();
    $payment_method    = $_POST['option_payment'];
    $bank_code         = @$_POST['bankcode'];
    $payment_type      = '';
    $discount_amount   = 0;
    $order_description = 'order_description';
    $tax_amount        = 0;
    $fee_shipping      = 0;
    $return_url        = base_url('home/save');

    $cancel_url     = urlencode(base_url('home/pay_cancel/?order_code=' . $order_code));
    $buyer_fullname = $_POST['buyer_fullname'];
    $buyer_email    = $_POST['buyer_email'];
    $buyer_mobile   = $_POST['buyer_mobile'];
    $buyer_address  = $_POST['address'];
    if ($payment_method != '' && $buyer_email != "" && $buyer_mobile != "" && $buyer_fullname != "" && filter_var($buyer_email, FILTER_VALIDATE_EMAIL)) {
        if ($payment_method == "NL") {
            $nl_result = $nlcheckout->NLCheckout($order_code, $total_amount, $payment_type, $order_description, $tax_amount,
                $fee_shipping, $discount_amount, $return_url, $cancel_url, $buyer_fullname, $buyer_email, $buyer_mobile,
                $buyer_address, $array_items);
        } elseif ($payment_method == "ATM_ONLINE" && $bank_code != '') {
            $nl_result = $nlcheckout->BankCheckout($order_code, $total_amount, $bank_code, $payment_type, $order_description, $tax_amount,
                $fee_shipping, $discount_amount, $return_url, $cancel_url, $buyer_fullname, $buyer_email, $buyer_mobile,
                $buyer_address, $array_items);
        } elseif ($payment_method == "IB_ONLINE") {
            $nl_result = $nlcheckout->IBCheckout($order_code, $total_amount, $bank_code, $payment_type, $order_description, $tax_amount, $fee_shipping, $discount_amount, $return_url, $cancel_url, $buyer_fullname, $buyer_email, $buyer_mobile, $buyer_address, $array_items);
        }
    }
    if ($nl_result->error_code == '00') {; //Cập nhât order với token  $nl_result->token để sử dụng check hoàn thành sau này
        ?>
          <script type="text/javascript">
                window.location = "<?php echo (string) $nl_result->checkout_url; ?>"
          </script>
          <?php
} else {
        echo $nl_result->error_message;
    }
} else {
    if (isset($_SESSION['cart_contents'])&&count($this->cart->contents())>0) {
    echo "<h3> Vui lòng nhập đủ thông tin nhận hàng </h3>";}
}
?>
<?php if (isset($_SESSION['cart_contents'])&&count($this->cart->contents())>0) {
    ?>

<h3>Chọn phương thức thanh toán</h3>
<form  name="NLpayBank" action="#" method="post">
 <table style="clear:both;width:500px;padding-left:46px;">
  <tr><td colspan="2"><p><span style="color:#ff5a00;font-weight:bold;text-decoration:underline;">Lưu ý</span> Bạn nhập đầy đủ thông tin sau </td></tr>
  <tr><td>Số tiền thanh toán: </td>
   <td><?php  echo $this->cart->format_number($total); ?>VND</td></tr>
   <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
  <tr><td>Họ Tên: </td>
   <td><input type="text" style="width:270px" id="fullname" name="buyer_fullname" class="field-check " value="" autocomplete="off" required></td></tr>
  <tr><td>Email: </td>
   <td><input type="text" style="width:270px" id="fullname" name="buyer_email" class="field-check " value="" autocomplete="off" required></td></tr>
  <tr><td>Số Điện thoại: </td>
   <td><input type="text" style="width:270px" id="fullname" name="buyer_mobile" class="field-check " value="" autocomplete="off" required></td></tr>
  <tr><td>Order description: </td>
   <td><input type="text" style="width:270px" id="fullname" name="order_description" class="field-check " value="" required></td></tr>
  <tr><td>Address: </td>
   <td><input type="text" style="width:270px" id="fullname" name="address" class="field-check " value="" autocomplete="off" required></td></tr>
  <tr><td>Date ship: </td>
   <td><input type="date" style="width:270px" id="fullname" name="date_ship" class="field-check "  min="<?php echo date("m/d/Y") ?>" required value="<?php echo date("m/d/Y") ?>" ></td></tr>
 </table>
 <link rel="stylesheet" type="text/css" href="<?php echo base_url('bootstrap/css/style_nganluong.css'); ?>">
 <ul class="list-content"><li class="active"><label><input type="radio" value="NL" name="option_payment" selected="true">Thanh toán bằng Ví điện tử NgânLượng</label><div class="boxContent"><p>Thanh toán trực tuyến AN TOÀN và ĐƯỢC BẢO VỆ, sử dụng thẻ ngân hàng trong và ngoài nước hoặc nhiều hình thức tiện lợi khác. Được bảo hộ & cấp phép bởi NGÂN HÀNG NHÀ NƯỚC, ví điện tử duy nhất được cộng đồng ƯA THÍCH NHẤT 2 năm liên tiếp, Bộ Thông tin Truyền thông trao giải thưởng Sao Khuê <br/>Giao dịch. Đăng ký ví NgânLượng.vn miễn phí <a href="https://www.nganluong.vn/?portal=nganluong&amp;page=user_register" target="_blank">tại đây</a></p></div></li><li><label><input type="radio" value="ATM_ONLINE" name="option_payment">Thanh toán online bằng thẻ ngân hàng nội địa</label><div class="boxContent"><p><i><span style="color:#ff5a00;font-weight:bold;text-decoration:underline;">Lưu ý</span>: Bạn cần đăng ký Internet-Banking hoặc dịch vụ thanh toán trực tuyến tại ngân hàng trước khi thực hiện.</i></p><ul class="cardList clearfix"><li class="bank-online-methods "><label for="vcb_ck_on"><i class="BIDV" title="Ngân hàng TMCP Đầu tư &amp; Phát triển Việt Nam"></i><input type="radio" value="BIDV" name="bankcode" ></label></li><li class="bank-online-methods "><label for="vcb_ck_on"><i class="VCB" title="Ngân hàng TMCP Ngoại Thương Việt Nam"></i><input type="radio" value="VCB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="sml_atm_mb_ck_on"><i class="MB" title="Ngân hàng Quân Đội"></i><input type="radio" value="MB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="sml_atm_vtb_ck_on"><i class="ICB" title="Ngân hàng Công Thương Việt Nam"></i><input type="radio" value="ICB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="sml_atm_acb_ck_on"><i class="ACB" title="Ngân hàng Á Châu"></i><input type="radio" value="ACB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="sml_atm_vpb_ck_on"><i class="VPB" title="Ngân Hàng Việt Nam Thịnh Vượng"></i><input type="radio" value="VPB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="bnt_atm_pgb_ck_on"><i class="PGB" title="Ngân hàng Xăng dầu Petrolimex"></i><input type="radio" value="PGB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="bnt_atm_agb_ck_on"><i class="AGB" title="Ngân hàng Nông nghiệp &amp; Phát triển nông thôn"></i><input type="radio" value="AGB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="sml_atm_bab_ck_on"><i class="SHB" title="Ngân hàng TMCP Sài Gòn - Hà Nội (SHB)"></i><input type="radio" value="SHB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="sml_atm_bab_ck_on"><i class="OJB" title="Ngân hàng TMCP Đại Dương (OceanBank)"></i><input type="radio" value="OJB" name="bankcode" ></label></li></ul></div></li><div class="clearfix"></div><li><label><input type="radio" value="IB_ONLINE" name="option_payment">Thanh toán bằng IB</label><div class="boxContent"><p><i><span style="color:#ff5a00;font-weight:bold;text-decoration:underline;">Lưu ý</span>: Bạn cần đăng ký Internet-Banking hoặc dịch vụ thanh toán trực tuyến tại ngân hàng trước khi thực hiện.</i></p><ul class="cardList clearfix"><li class="bank-online-methods "><label for="vcb_ck_on"><i class="BIDV" title="Ngân hàng TMCP Đầu tư &amp; Phát triển Việt Nam"></i><input type="radio" value="BIDV" name="bankcode" ></label></li><li class="bank-online-methods "><label for="vcb_ck_on"><i class="VCB" title="Ngân hàng TMCP Ngoại Thương Việt Nam"></i><input type="radio" value="VCB" name="bankcode" ></label></li><li class="bank-online-methods "><label for="vnbc_ck_on"><i class="DAB" title="Ngân hàng Đông Á"></i><input type="radio" value="DAB" name="bankcode" ></label></li></ul></div></li>
</ul>
<table style="clear:both;width:500px;padding-left:46px;">
  <tr><td></td>
   <td><input type="submit" class="btn btn-success" name="nlpayment" value="thanh toán"/></td></tr>
 </table>
</form>
<script language="javascript">
 $('input[name="option_payment"]').bind('click', function() {
 $('.list-content li').removeClass('active');
 $(this).parent().parent('li').addClass('active');
 });
</script>

<?php
}
 ?>
</div>
<br>
<br>
<br><hr>