# Transbank Webpay - Magento

Modulo de pago para e-commerce magento, en base a webpay de Transbank

## Recomendaciones Previas

### GNU-Linux / macOSX
  - manejo nivel medio de terminal consola
  - contar con usuario con permisos sudo o en su defecto con root
  - acceso al directorio raiz de instalacion de magento (no disponible en sitios hosteados en web reseller)

### Windows
  - manejo a nivel medio de CMD
  - declarar como variable de entorno el ejecutable de php [disponible aca](https://stackoverflow.com/a/12870987/5322827)
  - acceso al directorio raiz del sitio


Para efectos de estandarizacion, se separaran ambos tipos de instalacion, si el ejecutable de **php** es declarado como variable de entorno seguir los mismos pasos solo ejecutando ` c:>\php [comandos] `

## Instalacion

- Una vez descargado el plugin, descomprimirlo y copiarlo en la carpeta ` [raiz del sitio]/app/code `, los plugins tienen que seguir la siguientes estructura: ` Desarrollador/Plugin  ` por ende el plugin va a quedar de la siguiente forma

  ```
  [Raiz del sitio]/app/
  ├── code
  │   ├── community
  │   └── Transbank
  │       └── Webpay
  ...
  ```
- En una terminal o consola (dependiendo del sistema operativo) dirigirse al directorio raiz del sitio

  **GNU-Linux / macOSX / Windows**
  ```

  cd [raiz del sitio]

  ```

- Se deben cargar los cambios a magento con el siguiente comando

  **GNU-Linux / macOSX**
  ```bash
  sudo php bin/magento setup:upgrade

  ```

  **Windows**
  ```
  C:\[instalacion de php]\php.exe bin/magento setup:upgrade

  ```

- Posteriormente se debe compilar la instalacion

  **GNU-Linux / macOSX**
  ```bash
  sudo php bin/magento setup:di:compile

  ```

  **Windows**
  ```
  C:\[instalacion de php]\php.exe bin/magento setup:di:compile

  ```

    - En caso de que se presente algun error al tratar de compilar (comun al tratar de compilar mas de una vez) eliminar el archivo de bloqueo de compilacion de la siguiente forma:

    **GNU-Linux / macOSX / Windows**
    ```
      rm -rf [directorio raiz del sitio]/var/di
    ```
- Para finalizar se debe limpiar y realizar flush de la cache del sitio con los siguientes comandos:

  **GNU-Linux / macOSX**
  ```bash
  sudo php bin/magento cache:clean
  sudo php bin/magento cache:flush


  ```

  **Windows**
  ```
  C:\[instalacion de php]\php.exe bin/magento cache:clean
  C:\[instalacion de php]\php.exe bin/magento cache:flush


  ```

    -  En linux puede que aparezca una ventana de error posterior a la ejecucion de estos comandos, al intentar ver el sitio, esto es debido a que el directorio donde se aloja el cache queda con permisos de administrador (root) esto se cambia simplemente con el siguiente comando:

    ```bash
    sudo chown apache:apache -R "[directorio raiz del sitio]/var/cache"
    ```
    (cambiar *apache* con usuario del servidor web)


<div class="page-break"></div>

## Configuracion

Una vez realizado los pasos anteriores se tiene que configurar el plugin en el sitio administrativo de magento, esto es igual para todos los sistemas operativos, para efectos de simplificar el manual, se asumira que el sitio administrativo esta alojado en ` https://localhost/magento/mag_admin `

- En la pagina principal desde el menu lateral ir a **Tiendas** -> **Configuracion**

![configuracion](assets/markdown-img-paste-20170612114412466.png)

- Esperar que la pagina cargue y en el sub menu interno desplegar **Ventas** y seleccionar **Metodos de Pago**

![metodos](assets/markdown-img-paste-20170612114652665.png =250x)

- En la pagina ahora van a salir los distintos metodos de pago por defecto que tiene Magento, ademas de Webpay

![webpaypp](assets/markdown-img-paste-20170612115011568.png)

- En el formulario llenar con la informacion correspondiente al ambiente que vas a habilitar

**Observaciones**:

Los ambientes que encontrarás en la configuración del plugin son:

**Integración:** Modo de prueba, tanto código de comercio, llaves y certificados, vienen dadas por defecto en el plugin y te permitirá generar simulaciones de compra para verificar la conexión con webpay  de Transbank.

**Certificación:** Etapa en la que Transbank está validando tu e-commerce (proceso QA)

**Producción:** Una vez que Transbank certifica tu e-commerce, estarás en condiciones de vender a través de tu sitio.

Para acceder a los ambientes de Certificación y Producción, debes afiliar tu comercio a Transbank. Una vez realizado, se te entregará un código de comercio, con el que deberás crear la llave y el certificado público. El certificado público debes enviarlo a Transbank para validar tu comercio.
Para conocer los requisitos de afiliación. [click aca!](https://www.transbank.cl/public/hazte-cliente/ventas-internet.html)


<div class="page-break"></div>

## Posibles errores

Es probable que poseterior a la instalacion de magento y compilacion de modulos / plugins tengamos paginas en blanco al tratar de acceder al sitio y/o al panel de administacion, irrelevantemente si se tiene certificado web se tiene que crear un archivo ` .htaccess ` en el directorio raiz del sitio para manejar este tipo de error, el contenido del documento debe ser el siguiente

**.htaccess**

```apacheconf
	# All explanations you could find in .htaccess.sample file
	DirectoryIndex index.php
		<IfModule mod_php5.c>
    	php_value memory_limit 768M
    	php_value max_execution_time 18000
    	php_flag session.auto_start off
    	php_flag suhosin.session.cryptua off
			</IfModule>
			<IfModule mod_php7.c>
    	php_value memory_limit 768M
    	php_value max_execution_time 18000
    	php_flag session.auto_start off
    	php_flag suhosin.session.cryptua off
			</IfModule>
			<IfModule mod_security.c>
    	SecFilterEngine Off
    	SecFilterScanPOST Off
			</IfModule>
			<IfModule mod_ssl.c>
    	SSLOptions StdEnvVars
			</IfModule>
			<IfModule mod_rewrite.c>
    	Options +FollowSymLinks
    	RewriteEngine on
    	RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    	RewriteCond %{REQUEST_METHOD} ^TRAC[EK]
    	RewriteRule .* - [L,R=405]
    	RewriteCond %{REQUEST_FILENAME} !-f
    	RewriteCond %{REQUEST_FILENAME} !-d
    	RewriteCond %{REQUEST_FILENAME} !-l
    	RewriteRule .* index.php [L]
			</IfModule>
    	AddDefaultCharset Off
    	AddType 'text/html; charset=UTF-8' html
			<IfModule mod_expires.c>
    	ExpiresDefault "access  1 year"
    	ExpiresByType text/html A0
    	ExpiresByType text/plain A0
			</IfModule>
    	RedirectMatch 403 /\.git
    	<Files composer.json>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files composer.lock>
        order allow,deny
        	deny from all
    	</Files>
    	<Files .gitignore>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files .htaccess>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files .htaccess.sample>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files .php_cs>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files .travis.yml>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files CHANGELOG.md>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files CONTRIBUTING.md>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files COPYING.txt>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files Gruntfile.js>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files LICENSE.txt>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files LICENSE_AFL.txt>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files nginx.conf.sample>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files package.json>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files php.ini.sample>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files README.md>
        	order allow,deny
        	deny from all
    	</Files>
    	<Files magento_umask>
        	order allow,deny
        	deny from all
    	</Files>
			ErrorDocument 404 /pub/errors/404.php
			ErrorDocument 403 /pub/errors/404.php
			<IfModule mod_headers.c>
    	Header set X-UA-Compatible "IE=edge"
    	<FilesMatch 		"\.(appcache|atom|bbaw|bmp|crx|css|cur|eot|f4[abpv]|flv|geojson|gif|htc|ico|jpe?g|js|json(ld)?|m4[av]|manifest|map|mp4|oex|og[agv]|opus|otf|pdf|png|rdf|rss|safariextz|svgz?|swf|topojson|tt[cf]|txt|vcard|vcf|vtt|webapp|web[mp]|webmanifest|woff2?|xloc|xml|xpi)$">
        	Header unset X-UA-Compatible
    	</FilesMatch>
			</IfModule>


```

Si una vez creado el archivo no aparece informacion del sitio se tienen que cambiar los **ownership** del directorio raiz del sitio recursivamente al usuario del servicio web

**GNU-Linux / macOSX**
```bash
sudo chown -R [usuario]:[grupo] [directorio raiz del sitio]
```

ejemplo
```bash
sudo chown -R apache:apache /var/www/html/magento
```




### Changelog

- **3.0.2**
  - se añade la libreria xmlseclibs desde su proyecto github, utilizando composer

- **3.0.3**
  - Se corrige SOAP para registrar versiones

- **3.0.4**
  - Se modifica certificado de servidor para ambiente de integracion.

- **3.1.0***
  - Se agregan validaciones de depencias en instalacion a través de composer
  - Se modifica herramienta de diagnostico, metodo es desde ahora ondemand.
  - Se realizan correcciones a obtencion de orden de compra.
  - Se realizan correcciones a flujo de compra considerando anulacion por parte del cliente.

- **3.1.1***
  - Se modifica código de comercio y certificados.
  
