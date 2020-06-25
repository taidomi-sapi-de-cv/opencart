<?php
class ControllerExtensionPaymentDomitai extends Controller {
  private $error = array();
 
  public function index() {
    $this->language->load('extension/payment/domitai');
    $this->document->setTitle('Domitai for Opencart');
    $this->load->model('setting/setting');
    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
      $this->model_setting_setting->editSetting('payment_domitai', $this->request->post);
      $this->session->data['success'] = 'Saved.';
      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']."&type=payment", true));
    }
    
    
    $data['heading_title'] = $this->language->get('heading_title');
    
    $data['title'] = $this->language->get('title');
    $data['description'] = $this->language->get('description');
    $data['point_of_sale'] = $this->language->get('point_of_sale');
    $data['test_enable'] = $this->language->get('test_enable');
    $data['test_disable'] = $this->language->get('test_disable');
    $data['testnet'] = $this->language->get('testnet');
    $data['help'] = $this->language->get('help');
    $data['button_save'] = $this->language->get('text_button_save');
    $data['button_cancel'] = $this->language->get('text_button_cancel');
    $data['entry_order_status'] = $this->language->get('entry_order_status');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');
    $data['entry_status'] = $this->language->get('entry_status');
    $data['action'] = $this->url->link('extension/payment/domitai', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']."&type=payment", true);
 
    if (isset($this->request->post['payment_domitai_title'])) {
      $data['payment_domitai_title'] = $this->request->post['payment_domitai_title'];
    } else {
      $data['payment_domitai_title'] = $this->config->get('payment_domitai_title');
    }
        
    if (isset($this->request->post['payment_domitai_description'])) {
      $data['payment_domitai_description'] = $this->request->post['payment_domitai_description'];
    } else {
      $data['payment_domitai_description'] = $this->config->get('payment_domitai_description');
    }
            
    if (isset($this->request->post['payment_domitai_sale'])) {
      $data['payment_domitai_sale'] = $this->request->post['payment_domitai_sale'];
    } else {
      $data['payment_domitai_sale'] = $this->config->get('payment_domitai_sale');
    }
        
    if (isset($this->request->post['payment_domitai_test'])) {
      $data['payment_domitai_test'] = $this->request->post['payment_domitai_test'];
    } else {
      $data['payment_domitai_test'] = $this->config->get('payment_domitai_test');
    }

    if (isset($this->request->post['payment_domitai_status'])) {
			$data['payment_domitai_status'] = $this->request->post['payment_domitai_status'];
		} else {
			$data['payment_domitai_status'] = $this->config->get('payment_domitai_status');
		}
    
    $this->load->model('localisation/order_status');
    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            
    $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    
    $this->response->setOutput($this->load->view("extension/payment/domitai",$data));
  }
}