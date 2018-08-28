<?php
/**
 * @package		OpenCart
 * @author		Meng Wenbin
 * @copyright	Copyright (c) 2010 - 2017, Chengdu Guangda Network Technology Co. Ltd. (https://www.opencart.cn/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.cn
 */

class ModelExtensionPaymentClickGem extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/clickgem');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_wechat_pay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		$array_currency = $this->array_currency();
		if (!in_array($this->config->get('config_currency'),$array_currency)) {
			$status = false;
		} elseif (!$this->config->get('payment_clickgem_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'clickgem',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_clickgem_sort_order')
			);
		}
		
		return $method_data;
	}
	private function array_currency(){
		$array_currency = ['CGM', 'USD', 'BTC', 'EUR', 'LTC', 'BCH'];
		return $array_currency;
	}
}
