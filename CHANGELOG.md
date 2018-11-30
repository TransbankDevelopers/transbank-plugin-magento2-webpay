# Changelog
Todos los cambios notables a este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
y este proyecto adhiere a [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.1.5] - 2018-11-30
### Changed
- Se mejora la experiencia de pago con webpay.
### Fixed
- Se corrige un problema al cargar el sdk de webpay

## [3.1.4] - 2018-11-28
### Changed
- Se mejora la experiencia de pago con webpay.
- Se elimina configuración de logs en sección de administración.

## [3.1.3] - 2018-11-27
### Changed
- Se mejora la creación del pdf de diagnóstico.
- Se elimina la comprobación de la extención mcrypt dado que ya no es necesaria por el plugin.

## [3.1.2] - 2018-11-21
### Changed
- Se corrigen varios problemas internos del plugin para entregar una mejor experiencia en magento2 con Webpay.
- Ahora el certificado de transbank Webpay es opcional.
- Ahora soporta php 7.1

## [3.1.1] - 2018-08-24
### Changed
- Se modifica código de comercio y certificados.

## [3.1.0] - 2018-07-11
### Added
- Se agregan validaciones de depencias en instalacion a través de composer
### Modificado
- Se modifica herramienta de diagnostico, metodo es desde ahora ondemand.
- Se realizan correcciones a obtencion de orden de compra.
- Se realizan correcciones a flujo de compra considerando anulacion por parte del cliente.

## [3.0.4] - 2018-05-28
### Changed
- Se modifica certificado de servidor para ambiente de integracion.

## [3.0.3] - 2018-05-18
### Modificado
- Se corrige SOAP para registrar versiones.
