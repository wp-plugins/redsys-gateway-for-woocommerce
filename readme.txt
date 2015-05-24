=== RedSys Gateway for WooCommerce ===
Contributors: carazo, hornero
Donate link: http://www.codection.com/
Tags: woocommerce, servired, redsys, credit card, martercard, visa, ecommerce
Requires at least: 3.5
Tested up to: 4.2.2
Stable tag: 0.92
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integración para WooCommerce del sistema de pago por tarjeta Servired / RedSys / Sermepa.

== Description ==

Con este plugin podemos usar pasarelas de pago de bancos españoles con WooCommerce. RedSys a día de hoy es el método más usado por la mayor parte de los bancos: Banco Santander, BBVA, La Caixa, ING, etc. El plugin incorpora un formulario de configuración para sólo tener que introducir los datos que nos ofrezcan desde el banco. Este formulario, tiene una validación incorporada para facilitar su uso y minimizar errores.

Algunas características interesantes:
*	El plugin automáticamente marca el pedido como completado si el pago se realiza de forma satisfactoria.
*	Preparado para internacionalización
*	WPML ready

The description of this plugin is in Spanish because of RedSys is a system from Spanish banks. If need anything about it in English, tell us directly at contacto AT codection DOT com.

== Installation ==

Puede instalarlo automáticamente desde Wordpress, o manualmente:

1. Descomprima el archivo, y copie la carpeta 'woocommerce-servired-integration-light' en su carpeta de plugins del servidor (/wp-content/plugins/).
1. Active el plugin desde el menú de Plugins.

== Preguntas frecuentes (FAQs) ==

= Configuración =

1. Nos aseguramos en Plugins que este plugin está activado
2. Nos dirigimos al menú, concretamente a WooCommerce -> Ajustes -> Finalizar compra, arriba veremos RedSys/Servired, hacemos clic sobre ese enlace
3. Rellenamos el formulario

= ¿Cómo consigo una pasarela de pago para que mis clientes paguen con tarjeta? =
Debe hablar este tema directamente con su banco y asegurarse que le ofrecen una pasarela del tipo ReSys o Sermepa o Servired. Una vez contratada la pasarela le enviarán los datos necesarios, los introducierá en el formulario de configuración y el plugin estará listo para funcionar.

Lea detenidamente las condiciones de la pasarela y aparte, también el correo que le envíen, los bancos suelen activar las pasarelas en pruebas para posteriormente pasarlas a producción.

== Screenshots ==

1. Formulario de datos de la configuración de WooCommerce
2. Pasarela de pago en el front-end en la selección del proceso de pago.

== Changelog ==

= 0.92 =
* Más arreglos en créditos y textos.

= 0.91 =
* Arreglados créditos, textos y agregadas imágenes.

= 0.9 =
* Versión inicial.
