<?php
class ModelExtensionPaymentCompropago extends Model{

    public function getMethod($address, $total) {
        return [
          'code' => 'compropago',
          'title' => '<img src="https://cdn.compropago.com/cp-assets/ui-compropago/logo.svg" style="height:50px;"  alt="ComproPago - Efectivo"/>',
          'terms' => '',
          'sort_order' => 1
        ];
      }

}