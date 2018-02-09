# Compropago para Opencart v3+
Plugin para utilizar la pasarela de pago Compropago con Opencart en su versión 3.+

## Guía básica de Uso

Se debe contar con una cuenta activa de ComproPago. [Registrarse en ComproPago](https://compropago.com)

### Configuración del Cliente

Para poder hacer uso del plugin es necesario generar sus llaves públicas y privadas
*Sus llaves las encontrara en su Panel de ComproPago en el menú Configuración.*

[Consulte Aquí sus Llaves](https://compropago.com/panel/configuracion)

## Ayuda y Soporte de ComproPago

- [Centro de ayuda y soporte](https://compropago.com/ayuda-y-soporte)
- [Solicitar Integración](https://compropago.com/integracion)
- [Guía para Empezar a usar ComproPago](https://compropago.com/ayuda-y-soporte/como-comenzar-a-usar-compropago)
- [Información de Contacto](https://compropago.com/contacto)

## Configuracin del Plugin

Siguiendo la ruta (desde el panel de administración de OpenCart) -> Extensiones -> Pagos -> Compropago

- Habilitar el plugin (con esto aparecerá en la selección de pagos en el checkout)
- Seleccionar el modo de configuración del plugin (Desarrollo ó Producción)
- Llene los campos con sus Llaves Públicas y Privadas
- Seleccione si se verán los íconos de los proveedores de pago activos en Compropago, o solamente un selector.

## URL del Webhook
`http://mystore.com/index.php?route=extension/payment/compropago/webhook`

Esta url se debe asignar en el panel de Compropago

## Pull Request
Todos son bienvenidos

## TODO

- Seleccionar los métodos de pago disponibles
- Posibilidad de seleccionar si se envia un mensaje de texto al usuario despúes de realizar la compra en el panel

