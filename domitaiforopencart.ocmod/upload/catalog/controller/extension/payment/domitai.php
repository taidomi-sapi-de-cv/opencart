<?php 
class ControllerExtensionPaymentDomitai extends Controller {
  public function index() {
    $this->language->load('extension/payment/domitai');
    $data['button_confirm'] = $this->language->get('button_confirm');
    $data['action'] = $this->url->link('extension/payment/domitai/callback');
    $data['text_title'] = $this->language->get("text_title");
    $data['select_text'] = $this->language->get("select_text");
    $data['title_address'] = $this->language->get("title_address");
    $data['scan'] = $this->language->get("scan");
    $data['qr_text'] = $this->language->get("qr_text");
    $data['get_address'] = $this->language->get("get_address");
  
    $this->load->model('checkout/order');
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    if ($order_info) {
      $data['cryptocurrencies'] = $this->generateDomitaiGateway($order_info); 
      return $this->load->view('extension/payment/domitai',$data);
    }
  }

  private function generateDomitaiGateway($pData){
    $amount = 0;
    
    if($pData['currency_code'] != "MXN") $amount = $this->currency->format($this->currencyConverter($pData['total'],$pData['currency_code']), 'MXN' , false, false);
    else $amount = $this->currency->format($pData['total'], $pData['currency_code'] , false, false);

    $isTestnetActive = $this->config->get("payment_domitai_test");
    $postFields = array(
      "slug" => $this->config->get("payment_domitai_sale"),
      "currency" => $isTestnetActive == '1'?"MXNt":'MXN',
      "amount" => $amount,
      "customer_data" => array(
        "first_name" => $pData['firstname'],
        "last_name" => $pData['lastname'],
        "email" => $pData['email'],
        "orderid" => $this->session->data['order_id']
    ),
    "generateQR" => true);
    $postFieldsString = json_encode($postFields);
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"https://domitai.com/api/pos");
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array(
        "Content-Type: application/json"
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    $todo = json_decode($response,true);
    $qr = $todo['payload']['accepted'];
    return $qr;
  }

  private function currencyConverter($pTotal,$pCurrency) {
    $ch = curl_init();
    print_r($pTotal);
    curl_setopt($ch,CURLOPT_URL,"https://api.exchangeratesapi.io/latest?base=".$pCurrency."&symbols=MXN");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $todo = json_decode($response,true);
    if(isset($todo['error'])){
      $error = $this->language->get('error_currency');
      print_r($error);
      exit();
    }
    $equivalent = number_format($todo['rates']['MXN'], 2, '.', '');
    $conversor = $pTotal * $equivalent;
    return number_format($conversor,2, '.', '');
  }
  
  public function callback() {
    /*$order_id= "";
    if (isset($this->request->post['orderid'])) {
      $order_id = trim(substr(($this->request->post['orderid']), 6));
    } else {
      die('Illegal Access');
    }*/
    $this->load->model('checkout/order');
      
    $data = file_get_contents('php://input');
    $data = json_decode($data,true);
    if($data and $data['customer_data']['orderid'] ){
      $orderid = $data['customer_data']['orderid'];
      $order = $this->model_checkout_order->getOrder($orderid);
      if($order['order_status'] == "Canceled" or $order['order_status'] == "Complete"){
        $messageStatus = $order['order_status'] == "Canceled"?'cancelada':'completada';
        return array("message" =>"Esta orden esta ".$messageStatus,"code" => 200);
      }else{
        if($data['status'] == "payment_received") $this->model_checkout_order->addOrderHistory($orderid, 2);
        elseif($data['status'] == "payment_confirmed") $this->model_checkout_order->addOrderHistory($orderid, 5);
        else $this->model_checkout_order->addOrderHistory($orderid, 7);
        return array("message" => "Success","code" => 200);
      }
    }else{
      return array("message" => "Error al guardar","code" => 403);
    }
  }

  public function verify_status_order(){
    $this->load->model('checkout/order');
    $json = array();
		if ($this->session->data['payment_method']['code'] == 'domitai') {
      $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
      if($order['order_status'] == "Processing" || $order['order_status'] == "Complete") $json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
  }
}
?>