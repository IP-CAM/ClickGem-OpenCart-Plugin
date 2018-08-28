<?php
class ControllerExtensionPaymentClickGem extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('extension/payment/clickgem');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('payment_clickgem', $this->request->post);				
				
			$this->session->data['success'] = $this->language->get('text_success');
		
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}
	
		
  		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->error['user'])) {
			$data['error_user'] = $this->error['user'];
		} else {
			$data['error_user'] = '';
		}
		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}
		if (isset($this->error['signature'])) {
			$data['error_signature'] = $this->error['signature'];
		} else {
			$data['error_signature'] = '';
		}
		

 	
  		$this->document->breadcrumbs = array();

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=common/home&user_token=' . $this->session->data['user_token'],
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&user_token=' . $this->session->data['user_token'],
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/payment/clickgem&user_token=' . $this->session->data['user_token'],
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
				
		$data['action'] = HTTPS_SERVER . 'index.php?route=extension/payment/clickgem&user_token=' . $this->session->data['user_token'];
		
		$data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&user_token=' . $this->session->data['user_token'];
		
		$data['callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/clickgem/callback';
		
		if (isset($this->request->post['payment_clickgem_order_status_id'])) {
			$data['payment_clickgem_order_status_id'] = $this->request->post['payment_clickgem_order_status_id'];
		} else {
			$data['payment_clickgem_order_status_id'] = $this->config->get('payment_clickgem_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_clickgem_geo_zone_id'])) {
			$data['payment_clickgem_geo_zone_id'] = $this->request->post['payment_clickgem_geo_zone_id'];
		} else {
			$data['payment_clickgem_geo_zone_id'] = $this->config->get('payment_clickgem_geo_zone_id'); 
		} 

		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['payment_clickgem_status'])) {
			$data['payment_clickgem_status'] = $this->request->post['payment_clickgem_status'];
		} else {
			$data['payment_clickgem_status'] = $this->config->get('payment_clickgem_status');
		}
		
		if (isset($this->request->post['payment_clickgem_user'])) {
			$data['payment_clickgem_user'] = $this->request->post['payment_clickgem_user'];
		} else {
			$data['payment_clickgem_user'] = $this->config->get('payment_clickgem_user');
		}
		if (isset($this->request->post['payment_clickgem_password'])) {
			$data['payment_clickgem_password'] = $this->request->post['payment_clickgem_password'];
		} else {
			$data['payment_clickgem_password'] = $this->config->get('payment_clickgem_password');
		}
		if (isset($this->request->post['payment_clickgem_signature'])) {
			$data['payment_clickgem_signature'] = $this->request->post['payment_clickgem_signature'];
		} else {
			$data['payment_clickgem_signature'] = $this->config->get('payment_clickgem_signature');
		}
		
		if (isset($this->request->post['payment_clickgem_sort_order'])) {
			$data['payment_clickgem_sort_order'] = $this->request->post['payment_clickgem_sort_order'];
		} else {
			$data['payment_clickgem_sort_order'] = $this->config->get('payment_clickgem_sort_order');
		}
		
		$data['clickgem_typepayments'] = $this->typepayments();
		if (isset($this->request->post['payment_clickgem_typepayments'])) {
			$data['payment_clickgem_typepayments'] = $this->request->post['payment_clickgem_typepayments'];
		} else {
			$data['payment_clickgem_typepayments'] = $this->config->get('payment_clickgem_typepayments');
		}
		
		$data['clickgem_typeorderfee'] = $this->typeorderfee();
		if (isset($this->request->post['payment_clickgem_typeorderfee'])) {
			$data['payment_clickgem_typeorderfee'] = $this->request->post['payment_clickgem_typeorderfee'];
		} else {
			$data['payment_clickgem_typeorderfee'] = $this->config->get('payment_clickgem_typeorderfee');
		}
		$data['clickgem_typeinvoice'] = $this->typeinvoice();
		if (isset($this->request->post['payment_clickgem_typeinvoice'])) {
			$data['payment_clickgem_typeinvoice'] = $this->request->post['payment_clickgem_typeinvoice'];
		} else {
			$data['payment_clickgem_typeinvoice'] = $this->config->get('payment_clickgem_typeinvoice');
		}
		$data['clickgem_duedate'] = $this->duedate();
		if (isset($this->request->post['payment_clickgem_duedate'])) {
			$data['payment_clickgem_duedate'] = $this->request->post['payment_clickgem_duedate'];
		} else {
			$data['payment_clickgem_duedate'] = $this->config->get('payment_clickgem_duedate');
		}
		$data['payment_clickgem_url'] = 'https://api.clickgem.com/paygate/';
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/payment/clickgem', $data));
	}
	private function typepayments(){
		$data = array();
		$data[] = array(
			'key' => 'one',
			'value' => 'Single payment'
		);
		$data[] = array(
			'key' => 'multiple',
			'value' => 'Multiple payments'
		);
		return $data;
	}
	private function typeorderfee(){
		$data = array();
		$data[] = array(
			'key' => 'creator',
			'value' => 'Seller'
		);
		$data[] = array(
			'key' => 'customers',
			'value' => 'Buyer'
		);
		$data[] = array(
			'key' => 'customers_creator',
			'value' => 'Seller and Buyer'
		);
		
		return $data;
	}
	private function typeinvoice(){
		$data = array();
		$data[] = array(
			'key' => 'quantity',
			'value' => 'Quantity'
		);
		$data[] = array(
			'key' => 'hours',
			'value' => 'Hours'
		);
		$data[] = array(
			'key' => 'amountOnly',
			'value' => 'Amount only'
		);
		
		return $data;
	}
	private function duedate(){
		$data = array();
		$data[] = array(
			'key' => '0',
			'value' => 'No due date'
		);
		$data[] = array(
			'key' => '12h',
			'value' => '12 hours'
		);
		$data[] = array(
			'key' => '1d',
			'value' => '1 day'
		);
		$data[] = array(
			'key' => '3d',
			'value' => '3 day'
		);
		$data[] = array(
			'key' => '1w',
			'value' => '1 week'
		);
		$data[] = array(
			'key' => '1m',
			'value' => '1 month'
		);
		
		return $data;
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/clickgem')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->request->post['payment_clickgem_user']) {
			$this->error['user'] = $this->language->get('error_user_empty');
		}

		if (!$this->request->post['payment_clickgem_password']) {
			$this->error['password'] = $this->language->get('error_password_empty');
		}

		if (!$this->request->post['payment_clickgem_signature']) {
			$this->error['signature'] = $this->language->get('error_signature_empty');
		}
		

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}