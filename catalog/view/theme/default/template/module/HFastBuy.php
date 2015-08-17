<?php  
class ControllerModuleHFastBuy extends Controller {
	public function index() {
		$this->load->language('module/HFastBuy');
	
		$data['text_entry'] = $this->language->get('text_entry');
		$data['text_phone'] = $this->language->get('text_phone');
		$data['text_phoneholder'] = $this->language->get('text_phoneholder');
		$data['text_send'] = $this->language->get('text_send');
		
		$data['product_id'] = $this->request->get['product_id'];
		$data['user_phone'] = '';
		
		if ($this->customer->isLogged())
		{
			$this->load->model('account/customer');
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
			$data['user_phone'] = $customer_info['telephone'];
		}
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/HFastBuy.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/module/HFastBuy.tpl', $data);
		} else {
			return $this->load->view('default/template/module/HFastBuy.tpl', $data);
		}
	}
	
	public function save() {
		$this->load->language('module/HFastBuy');

		$json = array();

		if (!isset($this->request->post['product_id']) || !ctype_digit($this->request->post['product_id']))
			$json['error']['warning'] = $this->language->get('error_input');
		
		if (!isset($this->request->post['input-quantity']) || !ctype_digit($this->request->post['input-quantity']))
			$json['error']['warning'] = $this->language->get('error_input');
		
		if (!isset($this->request->post['recall']) || (utf8_strlen($this->request->post['recall']) < 7) || (utf8_strlen($this->request->post['recall']) > 20))
			$json['error']['warning'] = $this->language->get('error_phone');
			
		
		if (!isset($json['error']))
		{
			$phone = htmlspecialchars($this->request->post['recall']);
			
			$order_data = array();

			$order_data['totals'] = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name');

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				$order_data['store_url'] = HTTP_SERVER;
			}

			if ($this->customer->isLogged()) {
				$this->load->model('account/customer');

				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

				$order_data['customer_id'] = $this->customer->getId();
				$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				$order_data['firstname'] = $customer_info['firstname'];
				$order_data['lastname'] = $customer_info['lastname'];
				$order_data['email'] = $customer_info['email'];
				$order_data['telephone'] = $phone;
				$order_data['fax'] = $customer_info['fax'];
				$order_data['custom_field'] = unserialize($customer_info['custom_field']);
			} else{
				$order_data['customer_id'] = 0;
				$order_data['customer_group_id'] = 1;
				$order_data['firstname'] = '';
				$order_data['lastname'] = '';
				$order_data['email'] = "none";
				$order_data['telephone'] = $phone;
				$order_data['fax'] = '';
				$order_data['custom_field'] = '';
			}

			$order_data['payment_firstname'] = '';
			$order_data['payment_lastname'] = '';
			$order_data['payment_company'] = '';
			$order_data['payment_address_1'] = '';
			$order_data['payment_address_2'] = '';
			$order_data['payment_city'] = '';
			$order_data['payment_postcode'] = '';
			$order_data['payment_zone'] = '';
			$order_data['payment_zone_id'] = '';
			$order_data['payment_country'] = '';
			$order_data['payment_country_id'] = '';
			$order_data['payment_address_format'] = '';
			$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

			if (isset($this->session->data['payment_method']['title'])) {
				$order_data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$order_data['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$order_data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$order_data['payment_code'] = '';
			}

				$order_data['shipping_firstname'] = '';
				$order_data['shipping_lastname'] = '';
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = '';
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_country'] = '';
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_custom_field'] = array();
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';

			$order_data['products'] = array();

			$this->load->model('catalog/product');
			$product = $this->model_catalog_product->getProduct($this->request->post['product_id']);
			
			$option_data = array();
			
			if (isset($product['option']))
				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $this->request->post['input-quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => ($product['price'] * $this->request->post['input-quantity']),
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
				
				if (isset($product['download']))
					$order_data['products']['download'] = $product['download'];

			// Gift Voucher
			$order_data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$order_data['vouchers'][] = array(
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']
					);
				}
			}

			$order_data['comment'] = $this->language->get('text_header');
			$order_data['total'] = ($order_data['products'][0]['price'] * $this->request->post['input-quantity']);

			if (isset($this->request->cookie['tracking'])) {
				$order_data['tracking'] = $this->request->cookie['tracking'];

				$subtotal = $this->cart->getSubTotal();

				// Affiliate
				$this->load->model('affiliate/affiliate');

				$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);

				if ($affiliate_info) {
					$order_data['affiliate_id'] = $affiliate_info['affiliate_id'];
					$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
				}

				// Marketing
				$this->load->model('checkout/marketing');

				$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

				if ($marketing_info) {
					$order_data['marketing_id'] = $marketing_info['marketing_id'];
				} else {
					$order_data['marketing_id'] = 0;
				}
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
			}

			$order_data['language_id'] = $this->config->get('config_language_id');
			$order_data['currency_id'] = $this->currency->getId();
			$order_data['currency_code'] = $this->currency->getCode();
			$order_data['currency_value'] = $this->currency->getValue($this->currency->getCode());
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$order_data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			$this->load->model('checkout/order');

			
			$this->load->model('module/HFastBuy');
			$this->session->data['order_id'] = $this->model_module_HFastBuy->addOrder($order_data);
			
			$json['success'] = $this->language->get('text_success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}
?>