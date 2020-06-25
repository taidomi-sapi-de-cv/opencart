<?php
class ModelExtensionPaymentDomitai extends Model {
  public function getMethod($address, $total) {
    $this->load->language('extension/payment/domitai');
  
    $method_data = array(
      'code'     => 'domitai',
      'title'    => $this->language->get('text_title'),
      'sort_order' => $this->config->get('domitai_sort_order')
    );
  
    return $method_data;
  }
}