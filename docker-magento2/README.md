![Magento 2](https://cdn.rawgit.com/rafaelstz/magento2-snippets-visualstudio/master/images/icon.png)

#  Magento 2 Docker para desarrollo

### Apache 2.4 + PHP 7.1 + MariaDB

## Requerimientos

**MacOS:**

Instalar [Docker](https://docs.docker.com/docker-for-mac/install/), [Docker-compose](https://docs.docker.com/compose/install/#install-compose) y [Docker-sync](https://github.com/EugenMayer/docker-sync/wiki/docker-sync-on-OSX).

**Windows:**

Instalar [Docker](https://docs.docker.com/docker-for-windows/install/), [Docker-compose](https://docs.docker.com/compose/install/#install-compose) y [Docker-sync](https://github.com/EugenMayer/docker-sync/wiki/docker-sync-on-Windows).

**Linux:**

Instalar [Docker](https://docs.docker.com/engine/installation/linux/docker-ce/ubuntu/) y [Docker-compose](https://docs.docker.com/compose/install/#install-compose).

**Cuenta**

Además debes tener o crear una cuenta en Magento Marketplace siguiendo este tutorial oficial: [https://devdocs.magento.com/guides/v2.2/install-gde/prereq/connect-auth.html](https://devdocs.magento.com/guides/v2.2/install-gde/prereq/connect-auth.html)

Luego de crear la cuenta y crear la llave de acceso debes respaldar "Public Key" y "Private Key" dado que pueden ser requeridas durante el proceso de instalación de magento2.

## Instalación y/o ejecución

**NOTA:** Puedes seguir este README, pero además existe un documento más detallado con imagenes de este proceso:

[Documento de instalación detallado](docs/INSTALLATION.md)

### Como instalar magento2

Para instalar Magento 2, hacer lo siguiente:

Además se puede especificar la versión a instalar (e.j. `install-magento2 2.2.6`).

```
./init
install-magento2 2.2.6
magento sampledata:deploy && magento setup:upgrade && magento setup:di:compile && magento setup:static-content:deploy
cp pub/errors/local.xml.sample pub/errors/local.xml
```

### Como usar

### Construir el contenedor desde cero

```
./init
```

### Iniciar el contenedor construido anteriormente

```
./start
```

### Acceder al contenedor

```
./shell
```

### Instala el plugin de Onepay en magento2 siguiendo el README

[Ir al plugin de Onepay](https://github.com/TransbankDevelopers/transbank-plugin-magento2-onepay)

## Paneles (Estos comandos son información extra)

**Web server:** http://localhost/

**Admin:** http://localhost/admin

    user: admin
    password: admin123

**PHPMyAdmin:** http://localhost:8090

### Lista de commandos

| Comandos  | Descripcion  | Opciones & Ejemplos |
|---|---|---|
| `./init`  | Crea los contenedores, images, volemes, etc.. |
| `./start`  | Iniciar los contenedores  | |
| `./stop`  | Detener los contenedores  | |
| `./kill`  | Detener los contendores y eliminar contenedores, networks, volumes, e images creadas para el proyecto  | |
| `./shell`  | Aceder al contenedor  | `./shell root` | |
| `./magento`  | Usar Magento CLI | |
| `./composer`  |  Usar comando Composer | `./composer update` |

### Licencia

MIT © 2018

Basado en: [Rafael Corrêa Gomes](https://github.com/rafaelstz/) and [contributors](https://github.com/clean-docker/Magento2/graphs/contributors).
