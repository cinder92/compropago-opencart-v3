<?php
class ControllerExtensionPaymentCompropago extends Controller {
    private $error = array();
    
    public function index() {
        $this->language->load('extension/payment/compropago');
        $this->document->setTitle('Configurar Compropago');
        $this->load->model('setting/setting');
    
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
        $this->model_setting_setting->editSetting('payment_compropago', $this->request->post);
        $this->session->data['success'] = 'Guardado con éxito.';
        $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }
    
        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_text_config_one'] = $this->language->get('text_config_one');
        $data['entry_text_config_two'] = $this->language->get('text_config_two');
        $data['button_save'] = $this->language->get('text_button_save');
        $data['button_cancel'] = $this->language->get('text_button_cancel');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/compropago', 'user_token=' . $this->session->data['user_token'], true)
        );
    
        $data['action'] = $this->url->link('extension/payment/compropago', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
                
        if (isset($this->request->post['payment_compropago_status'])) {
            $data['payment_compropago_status'] = $this->request->post['payment_compropago_status'];
        } else {
            $data['payment_compropago_status'] = $this->config->get('payment_compropago_status');
        }
            
        if (isset($this->request->post['payment_compropago_order_status_id'])) {
            $data['payment_compropago_order_status_id'] = $this->request->post['payment_compropago_order_status_id'];
        } else {
            $data['payment_compropago_order_status_id'] = $this->config->get('payment_compropago_order_status_id');
        }

        if (isset($this->request->post['payment_compropago_public_key'])) {
            $data['payment_compropago_public_key'] = $this->request->post['payment_compropago_public_key'];
        } else {
            $data['payment_compropago_public_key'] = $this->config->get('payment_compropago_public_key');
        }

        if (isset($this->request->post['payment_compropago_private_key'])) {
            $data['payment_compropago_private_key'] = $this->request->post['payment_compropago_private_key'];
        } else {
            $data['payment_compropago_private_key'] = $this->config->get('payment_compropago_private_key');
        }

        if (isset($this->request->post['payment_compropago_mode'])) {
            $data['payment_compropago_mode'] = $this->request->post['payment_compropago_mode'];
        } else {
            $data['payment_compropago_mode'] = $this->config->get('payment_compropago_mode');
        }

        if (isset($this->request->post['payment_compropago_show_logos'])) {
            $data['payment_compropago_show_logos'] = $this->request->post['payment_compropago_show_logos'];
        } else {
            $data['payment_compropago_show_logos'] = $this->config->get('payment_compropago_show_logos');
        }
        
    
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        /*$this->template = 'payment/custom.tpl';
                
        $this->children = array(
        'common/header',
        'common/footer'
        );
    
        $this->response->setOutput($this->render());*/

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/compropago', $data));
    }

    public function install(){
        $querys = $this->sqlCreateTables(DB_PREFIX);
        foreach($querys as $query){
            $this->db->query($query);
        }
    }

	public function uninstall() {
		$querys = $this->sqlDroTables(DB_PREFIX);
		foreach ($querys as $query) {
			$this->db->query($query);
		}
    }

    /**
	 * Compropago query tables
	 * 
	 * @param string $prefix
	 * 
	 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
	 */
	private function sqlCreateTables($prefix=null) {
		return array(
			'CREATE TABLE `' . $prefix . 'compropago_orders` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`date` int(11) NOT NULL,
			`modified` int(11) NOT NULL,
			`compropagoId` varchar(50) NOT NULL,
			`compropagoStatus`varchar(50) NOT NULL,
			`storeCartId` varchar(255) NOT NULL,
			`storeOrderId` varchar(255) NOT NULL,
			`storeExtra` varchar(255) NOT NULL,
			`ioIn` mediumtext,
			`ioOut` mediumtext,
			PRIMARY KEY (`id`), UNIQUE KEY (`compropagoId`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8  DEFAULT COLLATE utf8_general_ci  AUTO_INCREMENT=1 ;',
			'CREATE TABLE `' . $prefix . 'compropago_transactions` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`orderId` int(11) NOT NULL,
			`date` int(11) NOT NULL,
			`compropagoId` varchar(50) NOT NULL,
			`compropagoStatus` varchar(50) NOT NULL,
			`compropagoStatusLast` varchar(50) NOT NULL,
			`ioIn` mediumtext,
			`ioOut` mediumtext,
			PRIMARY KEY (`id`)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8  DEFAULT COLLATE utf8_general_ci  AUTO_INCREMENT=1 ;',
			'CREATE TABLE `' . $prefix . 'compropago_webhook_transactions` (
			`id` integer not null auto_increment,
			`webhookId` varchar(50) not null,
			`webhookUrl` varchar(300) not null,
			`updated` integer not null,
			`status` varchar(50) not null,
			primary key(id)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8  DEFAULT COLLATE utf8_general_ci  AUTO_INCREMENT=1 ;'
		);
	}
    
    /**
	 * Compropago drop tables
	 * 
	 * @param string $prefix
	 * 
	 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
	 */
	private function sqlDropTables($prefix=null) {
		return array(
			'DROP TABLE IF EXISTS `' . $prefix . 'compropago_orders`;',
			'DROP TABLE IF EXISTS `' . $prefix . 'compropago_transactions`;',
			'DROP TABLE IF EXISTS `' . $prefix . 'compropago_webhook_transactions`'
		);
	}
}