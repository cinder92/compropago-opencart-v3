<?php
include_once __DIR__ . '/../../../../CompropagoSdk/UnitTest/autoload.php';

use CompropagoSdk\Client;
use CompropagoSdk\Factory\Factory;

class ControllerExtensionPaymentCompropago extends Controller {
	public function index() {
        
        $this->language->load('payment/compropago');

        /**
         * POST	/v1/charges	crear un cargo
         *   GET	/v1/charges/{payment_id}	verificar un cargo existente
         *   POST	/v1/charges/{payment_id}/sms	enviar instrucciones vía SMS
         */

         /**
          * curl https://api.compropago.com/v1/charges/c90870de-55a2-4b50-bd6b-9c7887787b35 \
-u sk_live_xxxxxxxxxxxxxx:pk_live_xxxxxxxxxxxxxxx
          */
    
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $client = new Client(
            $this->config->get('payment_compropago_public_key'),
            $this->config->get('payment_compropago_private_key'),
            $this->config->get('payment_compropago_mode') == '1' ? true : false
        );

        $data['text_title'] = $this->language->get('text_title');
        $data['entry_payment_type'] = $this->language->get('entry_payment_type');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['continue'] = $this->url->link('checkout/success');
        //$this->addBreadcrums($data);
        $this->addConfig($data,$client);
        //$this->addConfig($data, $client);
        /*$data['show_logos'] = true;
        $data['providers'] = array(
          array(
            'internal_name' => 'BANAMEX',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-banamex-medium'
          ),
          array(
            'internal_name' => 'OXXO',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-oxxo-medium'
          ),
          array(
            'internal_name' => 'COPPEL',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-coppel-medium'
          ),
          array(
            'internal_name' => 'EXTRA',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-extra-medium'
          ),
          array(
            'internal_name' => 'TELECOMM',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-telecomm-medium'
          ),
          array(
            'internal_name' => 'SEVEN_ELEVEN',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-seven-medium'
          ),
          array(
            'internal_name' => 'FARMACIAS_BENAVIDES',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-benavides-medium'
          ),
          array(
            'internal_name' => 'BANCOMER',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-bancomer-medium'
          ),
          array(
            'internal_name' => 'ELEKTRA',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-elektra-medium'
          ),
          array(
            'internal_name' => 'SCOTIABANK',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-scotiabank-medium'
          ),
          array(
            'internal_name' => 'SANTANDER',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-santander-medium'
          ),
          array(
            'internal_name' => 'BANORTE',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-banorte-medium'
          ),
          array(
            'internal_name' => 'INBURSA',
            'image_medium' => 'https://s3.amazonaws.com/compropago/assets/images/receipt/receipt-inbursa-medium'
          )
        );*/
        //$uri = ( defined('VERSION') && ( version_compare( VERSION, '2.2.0.0' ,'>=' ) && version_compare( VERSION, '2.3.0.0' ,'<' ) ) ) ? 'payment/cp_providers' : 'default/template/payment/cp_providers.tpl';
        //print_r($data);
        return $this->load->view('extension/payment/compropago', $data);
        //return $this->load->view( $uri , $data);
    
	}

	/**
   * Generate order in compropago and return a json with the redirect url to the receipt
   * 
   * @author Eduardo Aguilar <dante.aguilar41@gmail.com> 
   */
  public function confirm() {
    $this->load->model('checkout/order');
    $this->load->model('setting/setting');
    $client = new Client(
      $this->config->get('payment_compropago_public_key'),
      $this->config->get('payment_compropago_private_key'),
      $this->config->get('payment_compropago_mode') == '1' ? true : false
    );
    $order_id = $this->session->data['order_id'];
    $order_info = $this->model_checkout_order->getOrder($order_id);
    $products = $this->cart->getProducts();
    $order_name = '';
    foreach ($products as $product) {
      $order_name .= $product['name'];
    }
    $data_order = [
      'order_id' => $order_id,
      'order_name' => $order_name,
      'order_price' => (float) $order_info['total'],
      'customer_name' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
      'customer_email' => $order_info['email'],
      'currency' => "MXN",//$order_info['currency_code'],
      'payment_type' => $this->request->post['payment_type'],
      'app_client_name' => 'opencart',
      'app_client_version' => VERSION
    ];
    try {
      $order = Factory::getInstanceOf('PlaceOrderInfo', $data_order);
      $response = $client->api->placeOrder($order);
      $recordTime = time();
      $order_id = $order_info['order_id'];
      $ioIn = base64_encode(json_encode($response));
      $ioOut = base64_encode(json_encode($data_order));
      $query = "INSERT INTO " . DB_PREFIX . "compropago_orders 
        (`date`,`modified`,`compropagoId`,`compropagoStatus`,`storeCartId`,`storeOrderId`,`storeExtra`,`ioIn`,`ioOut`)".
        " values (:fecha:,:modified:,':cpid:',':cpstat:',':stcid:',':stoid:',':ste:',':ioin:',':ioout:')";
      $query = str_replace(":fecha:", $recordTime, $query);
      $query = str_replace(":modified:", $recordTime, $query);
      $query = str_replace(":cpid:", $response->id, $query);
      $query = str_replace(":cpstat:", $response->type, $query);
      $query = str_replace(":stcid:", $order_id, $query);
      $query = str_replace(":stoid:", $order_id, $query);
      $query = str_replace(":ste:", 'COMPROPAGO_PENDING', $query);
      $query = str_replace(":ioin:", $ioIn, $query);
      $query = str_replace(":ioout:", $ioOut, $query);
      $this->db->query($query);
      $compropagoOrderId = $this->db->getLastId();
      $query2 = "INSERT INTO ".DB_PREFIX."compropago_transactions
        (orderId,date,compropagoId,compropagoStatus,compropagoStatusLast,ioIn,ioOut)
        values (:orderid:,:fecha:,':cpid:',':cpstat:',':cpstatl:',':ioin:',':ioout:')";
      $query2 = str_replace(":orderid:", $compropagoOrderId, $query2);
      $query2 = str_replace(":fecha:", $recordTime, $query2);
      $query2 = str_replace(":cpid:", $response->id, $query2);
      $query2 = str_replace(":cpstat:", $response->type, $query2);
      $query2 = str_replace(":cpstatl:", $response->type, $query2);
      $query2 = str_replace(":ioin:", $ioIn, $query2);
      $query2 = str_replace(":ioout:", $ioOut, $query2);
      $this->db->query($query2);
      $status_update = $this->config->get('compropago_order_status_new_id');
      $query_update = "UPDATE ".DB_PREFIX."order SET order_status_id = 1 WHERE order_id = $order_id";
      $this->db->query($query_update);
      $json = [
        'status' => 'success',
        'redirect' => htmlspecialchars_decode($this->url->link('extension/payment/compropago/success', 'cp_id=' . $response->id , 'SSL'))
      ];
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($json));
    } catch (Exception $e) {
      $json = [
        'status' => 'error',
        'message' => $e->getMessage()
      ];
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($json));
    }
  }
  /**
   * Display ComproPago receipt
   * 
   * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
   */
  public function success() {
    $this->language->load('payment/compropago');
    $this->cart->clear();
    $data['order_id'] = $this->request->get['cp_id'];
    $this->addBreadcrums($data);
    $this->addData($data);
    //$uri = ( defined('VERSION') && ( version_compare( VERSION, '2.2.0.0' ,'>=' ) && version_compare( VERSION, '2.3.0.0' ,'<' ) ) ) ? 'payment/cp_receipt' : 'default/template/payment/cp_receipt.tpl';
    $this->response->setOutput($this->load->view('extension/payment/compropago_receipt', $data));
  }

  public function webhook(){
    $this->response->addHeader('Content-Type: application/json');
    
    $request = @file_get_contents('php://input');
    if(!$resp_webhook = Factory::getInstanceOf('CpOrderInfo', $request)){
      $this->response->setOutput(json_encode([
        'status' => 'error',
        'message' => 'invalid request',
        'short_id' => null,
        'reference' => null
      ]));
      return;
    }
    $publickey = $this->config->get('payment_compropago_public_key');
    $privatekey = $this->config->get('payment_compropago_private_key');
    $live = $this->config->get('payment_compropago_mode') == '1' ? true : false;
    try{
        $client = new Client($publickey, $privatekey, $live );
        if($resp_webhook->short_id == "000000"){
          $this->response->setOutput(json_encode([
            'status' => 'success',
            'message' => 'OK - test',
            'short_id' => $resp_webhook->short_id,
            'reference' => $resp_webhook->order_info->order_id
          ]));
          return;
        }
        $this_order = $this->db->query("SELECT * FROM ". DB_PREFIX ."compropago_orders WHERE compropagoId = '".$resp_webhook->id."'");
        if($this_order->num_rows == 0){
          $this->response->setOutput(json_encode([
            'status' => 'error',
            'message' => 'Order not found in store',
            'short_id' => null,
            'reference' => null
          ]));
          return;
        }
        $id = intval($this_order->row['storeOrderId']);
        $response = $client->api->verifyOrder($resp_webhook->id);
        $status_id = 1;
        switch ($response->type){
          case 'charge.success':
            $status_id = 2;
            break;
          case 'charge.pending':
            $this->response->setOutput(json_encode([
              'status' => 'success',
              'message' => 'OK - ' . $response->type,
              'short_id' => $response->short_id,
              'reference' => $response->order_info->order_id    
            ]));
            return;
          case 'charge.expired':
            $status_id = 7;
            break;
          default:
            $this->response->setOutput(json_encode([
              'status' => 'error',
              'message' => 'invalid webhook type',
              'short_id' => $response->short_id,
              'reference' => $response->order_info->order_id    
            ]));
            return;
        }
        $this->db->query("UPDATE ". DB_PREFIX . "order SET order_status_id = ".$status_id." WHERE order_id = ".$id);
        $recordTime = time();
        $query = "UPDATE ". DB_PREFIX ."compropago_orders SET
          modified = ".$recordTime.",
          compropagoStatus = '".$response->type."',
          storeExtra = '".$response->type."'
          WHERE id = ".$id;
        $this->db->query($query);
        $ioIn = base64_encode(json_encode($request));
        $ioOut = base64_encode(json_encode($response));
        $query2 = "INSERT INTO ".DB_PREFIX."compropago_transactions
          (orderId,date,compropagoId,compropagoStatus,compropagoStatusLast,ioIn,ioOut)
          values (:orderid:,:fecha:,':cpid:',':cpstat:',':cpstatl:',':ioin:',':ioout:')";
        $query2 = str_replace(":orderid:", $this_order->row['id'], $query2);
        $query2 = str_replace(":fecha:", $recordTime, $query2);
        $query2 = str_replace(":cpid:", $response->id, $query2);
        $query2 = str_replace(":cpstat:", $response->type, $query2);
        $query2 = str_replace(":cpstatl:", $this_order->row['compropagoStatus'], $query2);
        $query2 = str_replace(":ioin:", $ioIn, $query2);
        $query2 = str_replace(":ioout:", $ioOut, $query2);
        $this->db->query($query2);
        $this->response->setOutput(json_encode([
          'status' => 'success',
          'message' => 'OK - ' . $response->type,
          'short_id' => $resp_webhook->short_id,
          'reference' => $resp_webhook->order_info->order_id
        ]));
        return;
    }catch (Exception $e) {
      $this->response->setOutput(json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'short_id' => null,
        'reference' => null    
      ]));
      return;
    }
  }
  /**
   * Add breadcrums data
   * 
   * @param array $data
   * 
   * @author Eduardo Aguilar <dante.aguilar41@gmail.com> 
   */
  private function addBreadcrums(&$data) {
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/home')
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_basket'),
      'href' => $this->url->link('checkout/cart')
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_checkout'),
      'href' => $this->url->link('checkout/checkout', '', 'SSL')
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_success'),
      'href' => $this->url->link('checkout/success')
    );
  }
  /**
   * Add secuencial data for reder view
   * 
   * @param array $data
   * 
   * @author Eduardo Aguilar <dante.aguilar41@gmail.com> 
   */
  private function addData(&$data) {
    $data['button_continue'] = $this->language->get('button_continue');
    $data['continue'] = $this->url->link('common/home');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
  }
  /**
   * Add config to display in providers selection in checkout
   * 
   * @param array $data
   * @param Client $client
   * 
   * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
   */
  //https://github.com/compropago/compropago-php#gu%C3%ADa-básica-de-uso
  private function addConfig(&$data, $client) {
    //$active_providers = $this->config->get('cppayment_providers');
    //$active_providers = explode(',', $active_providers);
    
    $order_id = $this->session->data['order_id'];
    $order_info = $this->model_checkout_order->getOrder($order_id);

    //print_r($order_info);

    $providers = $client->api->listProviders(0 /*para todos*/, "MXN"); //$order_info['currency_code']
    //$final = [];
    foreach ($providers as $provider) {
      //if (in_array($provider->internal_name, $active_providers)) {
        $data['providers'][] = $provider;
      //}
    }
    $data['show_logos'] = $this->config->get('payment_compropago_show_logos') == '1' ? true : false;
  }
}
