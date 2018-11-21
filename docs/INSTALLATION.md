# Manual de instalación para Plugin Magento2

## Descripción

Este plugin oficial ha sido creado para que puedas integrar Webpay fácilmente en tu comercio, basado en Magento2.

## Requisitos

1. Debes tener instalado previamente Magento2 y asegurarte de tener [Composer](https://getcomposer.org) instalado.
2. Tus credenciales de Magento Market a mano. Si no sabes cuales son tus credenciales puedes revisar esta guia: [https://devdocs.magento.com/guides/v2.2/install-gde/prereq/connect-auth.html](https://devdocs.magento.com/guides/v2.2/install-gde/prereq/connect-auth.html)

## Instalación del Plugin

**Nota:** En este punto composer podría pedirte si lo requiere tus creedenciales de magento2.

En tu directorio de Magento2, ejecutar el comando:

    composer require transbank/webpay-magento2

  ![Paso 7](img/paso7.png)

Cuando finalice, ejecutar el comando:

    magento module:enable Transbank_Webpay --clear-static-content

  ![Paso 8](img/paso8.png)

Cuando finalice, ejecutar el comando:

    magento setup:upgrade && magento setup:di:compile && magento setup:static-content:deploy

  ![Paso 9](img/paso9.png)
  
Una vez realizado el proceso anterior, Magento2 debe haber instalado el plugin Webpay. Cuando finalice, debes activar el plugin en el administrador de Magento2.

## Configuración

Este plugin posee un sitio de configuración que te permitirá ingresar credenciales que Transbank te otorgará y además podrás generar un documento de diagnóstico en caso que Transbank te lo pida.

Para acceder a la configuración, debes seguir los siguientes pasos:

1. Dirígete a la página de administración de Magento2 (usualmente en http://misitio.com/admin, http://localhost/admin) e ingresa usuario y clave.

  ![Paso 10](img/paso10.png)
  
2. Dentro del sitio de administración dirígete a (Stores / Configuration).

  ![Paso 11](img/paso11.png)

3. Luego a sección (Sales / Payments Methods).

  ![Paso 12](img/paso12.png)

4. Elegir el país Chile

  ![Paso 13](img/paso13.png)

5. Bajando al listado de métodos de pagos verás Webpay

  ![Paso 14](img/paso14.png)

6. ¡Ya está! Estás en la pantalla de configuración del plugin, debes ingresar la siguiente información:
  * **Enable**: Al activarlo, Webpay estará disponible como medio de pago. Ten la precaución de que se encuentre marcada esta opción cuando quieras que los usuarios paguen con Webpay.
  * **Endpoint**: Ambiente hacia donde se realiza la transacción. 
  * **APIKey**: Es lo que te identifica como comercio.
  * **Shared Secret**: Llave secreta que te autoriza y valida a hacer transacciones.
  
  Las opciones disponibles para _Endpoint_ son: "Integración" para realizar pruebas y certificar la instalación con Transbank, y "Producción" para hacer transacciones reales una vez que Transbank ha aprobado el comercio. Dependiendo de cual Endpoint se ha seleccionado el plugin usará uno de los dos set de APIKey y Shared Secret según corresponda. 
  
### Credenciales de Prueba

Para el ambiente de Integración, puedes utilizar las siguientes credenciales para realizar pruebas:

* APIKey: `dKVhq1WGt_XapIYirTXNyUKoWTDFfxaEV63-O5jcsdw`
* Shared Secret: `?XW#WOLG##FBAGEAYSNQ5APD#JF@$AYZ`

7. Guardar los cambios presionando el botón [Save Config]

  ![Paso 15](img/paso15.png)

8. Además, puedes generar un documento de diagnóstico en caso que Transbank te lo pida. Para ello, haz click en "Generar PDF de Diagnóstico", y automáticamente se descargará dicho documento.

  ![Paso 17](img/paso17.png)

## Configuración de magento2 para Chile CLP

El plugin solamente funciona con moneda chilena CLP dado esto magento2 debe estar correctamente configurado para que que se pueda usar Webpay.

1. Ir a la sección de administración (Stores / General / Country Option) y elegir Chile tal como se muestra en la siguiente imagen, luego guardar los cambios.

  ![Paso 1](img/clp1.png)

2. Ir a la sección de administración (Stores / Currency Setup / Country Option) y elegir Chile tal como se muestra en la siguiente imagen, luego guardar los cambios.

  ![Paso 2](img/clp2.png)

3. Ir a la sección de administración (Stores / Currency) y verificar en las dos secciones (Currency Rates y Currency Symbols) que CLP se encuentre activo.

  ![Paso 3](img/clp3.png)

  ![Paso 4](img/clp4.png)

  ![Paso 5](img/clp5.png)

## Prueba de instalación con transacción

En ambiente de integración es posible realizar una prueba de transacción utilizando un emulador de pagos online.

* Ingresa al comercio

  ![Paso 1](img/paso18.png)

* Ya con la sesión iniciada, ingresa a cualquier sección para agregar productos

  ![Paso 4](img/paso19.png)

* Agrega al carro de compras un producto:

  ![Paso 5](img/paso20.png)

* Selecciona el carro de compras y luego presiona el botón [Proceed to Checkout]:

  ![Paso 6](img/paso21.png)

* Selecciona método de envío y presiona el botón [Next]
  
  Debes asegurarte que tu dirección de envio sea en Chile.

  ![Paso 7](img/paso22.png)

* Selecciona método de pago Transbank Webpay, luego presiona el botón [Place Order]

  ![Paso 8](img/paso23.png)

* Una vez presionado el botón para iniciar la compra, se mostrará la ventana de pago Webpay, tal como se ve en la imagen. Toma nota del número que aparece como "Código de compra", ya que lo necesitarás para emular el pago en el siguiente paso:
  
  ![Paso 9](img/paso24.png)
  
* En otra ventana del navegador, ingresa al emulador de pagos desde [https://webpay.ionix.cl/mobile-payment-emulator/](https://webpay.ionix.cl/mobile-payment-emulator/), utiliza test@webpay.cl como correo electrónico, y el código de compra obtenido desde la pantalla anterior. Una vez ingresado los datos solicitados, presiona el botón "Iniciar Pago":
* 
  ![Paso 10](img/paso25.png)
  
* Si todo va bien, el emulador mostrará opciones para simular situaciones distintas. Para simular un pago exitoso, presiona el botón `PRE_AUTHORIZED`. En caso de querer simular un pago fallido, presiona le botón `REJECTED`. Simularemos un pago exitóso presionando el botón `PRE_AUTHORIZED`.

  ![Paso 11](img/paso26.png)
  
* Vuelve a la ventana del navegador donde se encuentra Magento2 y podrás comprobar que el pago ha sido exitoso.

 ![Paso 12](img/paso27.png)

* Además si accedes al sitio de administración seccion (Sales / Ordes) se podrá ver la orden creada y el detalle de los datos entregados por Webpay.

 ![Paso 13](img/paso28.png)

 ![Paso 14](img/paso29.png)

 ![Paso 15](img/paso30.png)
