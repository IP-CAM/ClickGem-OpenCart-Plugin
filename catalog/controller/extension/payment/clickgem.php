<?php
/**
 * @package		OpenCart
 * @author		Meng Wenbin
 * @copyright	Copyright (c) 2010 - 2017, Chengdu Guangda Network Technology Co. Ltd. (https://www.opencart.cn/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.cn
 */

class ControllerExtensionPaymentClickGem extends Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['totalVoucher'] = 0;
		if(isset($this->session->data['voucher'])){
			$totalVoucher = $this->model_extension_total_voucher->getVoucher($this->session->data['voucher']);
			$data['totalVoucher'] = $totalVoucher['amount'];
		}
		$data['redirect'] = $this->url->link('extension/payment/clickgem/createTransaction','totalVoucher=' . $data['totalVoucher'], true);
		$data['redirect'] = urldecode(htmlspecialchars_decode($data['redirect']));
		return $this->load->view('extension/payment/clickgem', $data);
	}
	public function createTransaction(){
		
		$this->load->language('extension/payment/clickgem');
		
		$this->document->setTitle($this->language->get('text_title'));
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$mail = $this->customer->getEmail();
			$telephone = $this->customer->getTelephone();
		}else{
			$mail = $this->session->data['guest']['email'];
			$telephone = $this->session->data['guest']['telephone'];
		}
		
		$itemDiscount = $this->session->data['currency'];
		
		$otherDiscount = 0;
		

		if(isset($this->request->get['totalVoucher'])){
			$otherDiscount += $this->request->get['totalVoucher'];
		}
		$data = array();
		$data['invoiceNumber'] = $this->session->data['order_id'];
		$data['invoiceDate'] = date('m/d/Y',time());
		$data['billTo'] = $mail;
		$data['paymentType'] = $this->config->get('payment_clickgem_typepayments');
		$data['feeTo'] = $this->config->get('payment_clickgem_typeorderfee');
		$data['itemUnit'] =  $this->config->get('payment_clickgem_typeinvoice');
		$data['dueDate'] = $this->config->get('payment_clickgem_duedate');
		$data['currency'] = $this->session->data['currency'];
		$data['itemDiscount'] = $itemDiscount;
		$data['otherDiscount'] = $otherDiscount;
		$data['shippingCost'] = ($this->cart->hasShipping()) ? $this->session->data['shipping_method']['cost'] : 0;
		$data['notes'] = '';
		$data['terms'] = '';
		$data['websiteURL'] = $this->url->link('extension/payment/clickgem/confirm', 'order_id=' . $this->session->data['order_id']);
		$data['websiteURL'] = urldecode(htmlspecialchars_decode($data['websiteURL']));
		$payment_address = $this->session->data['payment_address'];
		$data['billingAddress']['bl_firstname'] = isset($payment_address['firstname']) ? $payment_address['firstname'] : '';
		$data['billingAddress']['bl_lastname'] = isset($payment_address['lastname']) ? $payment_address['lastname'] : '';
		$data['billingAddress']['bl_telephone'] = $telephone;
		$data['billingAddress']['bl_address'] = isset($payment_address['address_1']) ? $payment_address['address_1'] : '';
		$data['billingAddress']['bl_city'] = isset($payment_address['city']) ? $payment_address['city'] : '';
		$data['billingAddress']['bl_postcode'] = isset($payment_address['postcode']) ? $payment_address['postcode'] : '';
		$data['billingAddress']['bl_country'] = isset($payment_address['country']) ? $payment_address['country'] : '';
		$data['billingAddress']['bl_note'] = '';
		$data['billingAddress'] = json_encode($data['billingAddress']);
		
		if ($this->cart->hasShipping()) {
			
			$shipping_address = $this->session->data['shipping_address'];
			
			$data['shippingAddress']['sp_firstname'] = $shipping_address['firstname'];
			$data['shippingAddress']['sp_lastname'] = $shipping_address['lastname'];
			$data['shippingAddress']['sp_address'] = $shipping_address['address_1'];
			$data['shippingAddress']['sp_telephone'] = $telephone;
			$data['shippingAddress']['sp_city'] = $shipping_address['city'];
			$data['shippingAddress']['sp_postcode'] = $shipping_address['postcode'];
			$data['shippingAddress']['sp_country'] = $shipping_address['country'];
			$data['shippingAddress'] = json_encode($data['shippingAddress']);
		}
		
		$products = $this->cart->getProducts();
		$item = [];
		if(!empty($products)){
			foreach($products as $key => $value){
				$item[$key]['itemName'] = $value['name'];
				$item[$key]['itemQuantity'] = $value['quantity'];
				$item[$key]['itemPrice'] = $value['price'];
				$item[$key]['discountAmount'] = 0;
				$item[$key]['discountType'] = $this->session->data['currency'];
				$item[$key]['itemDescription'] = '';
			}
		}
		
		$data['item'] = json_encode($item);
	
		$transaction = $this->create_paygate($data);
		
		$data = [];
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_createTransaction'),
			'href' => $this->url->link('extension/payment/clickgem/createTransaction')
		);
		
		if(isset($transaction->error_warning)){
			$data['error_warning'] = $transaction->error_warning;
		}elseif(isset($transaction->error)){
			$data['error'] = $transaction->error;
		}else{
			$data['redirect'] = $transaction->success;
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('extension/payment/clickgem_create', $data));
	}
	public function confirm(){
		$data = [];
		$this->load->language('extension/payment/clickgem');
		$order_id 	= 0;
		$get_id 	= '';
		if(isset($this->request->get['order_id'])){
			$order_id = $this->request->get['order_id'];
		}
		if(isset($this->request->get['getid'])){
			$get_id = $this->request->get['getid'];
		}
		$getstatus = $this->get_status(array('getid' => $get_id));
		if(isset($getstatus->success)){
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$i = $this->get_status_id($getstatus->success);
			
			if ($order_info) {
				if ($i !== $order_info["order_status_id"]) {
					$this->model_checkout_order->addOrderHistory($order_id, $i);
				}
			}
		}
		$json['redirect'] = $this->url->link('checkout/success');
		$this->response->setOutput($this->load->view('extension/payment/clickgem_create', $json));
	}
	private function get_status_id($text){
		$data_status = array(
			'unpaid' => 15,
			'pending' => 1,
			'paid' => 5,
			'refunded' => 11,
			'canceled' => 7,
			'partially_refunded' => 13,
			'partial_paid' => 2,
		);
		return $data_status[$text];
	}
	private function show_paygate($params = array()){
		
		if(!empty($params)){
			return $this->curl_request('show', $params);
		}
		return false;
	}
	
	private function create_paygate($params = array()){
		
		if(!empty($params)){
			return $this->curl_request('create', $params);
		}
		return false;
	}
	private function get_status($params = array()){
				
		if(!empty($params)){
			return $this->curl_request('getstatus', $params);
		}
		return false;
	}
	private function curl_request($method, $params=array()){
		
		$username      		= $this->config->get('payment_clickgem_user');
        $password	        = $this->config->get('payment_clickgem_password');
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		
		curl_setopt($ch, CURLOPT_URL, $this->config->get('payment_clickgem_url') . $method . '/');
		
		curl_setopt($ch, CURLOPT_POST, TRUE);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		
		$ret = curl_exec($ch);
		
		if($ret !== FALSE)
		{
			$formatted = @json_decode($ret);			
			return $formatted;
		}
		else
		{
			$error = array('error_warning' => curl_error($ch));
			$error = json_encode($error);
			return @json_decode($error);
		}
	}
}
