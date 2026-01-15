# silice
Prueba tecnica

## Descripción del Proyecto
Este proyecto es una prueba técnica que implementa un sistema de gestión de usuarios utilizando PHP y MySQL. El objetivo es demostrar habilidades en el desarrollo de aplicaciones web.

## Estructura del Proyecto
- **app/**: Contiene la lógica de la aplicación.
  - **Common/**: Clases comunes utilizadas en la aplicación.
  - **Http/**: Controladores y middleware para manejar las solicitudes HTTP.
  - **Infrastructure/**: Implementaciones de la infraestructura, como la base de datos y repositorios.
  - **Model/**: Modelos de datos utilizados en la aplicación.
  - **Views/**: Vistas de la aplicación.
- **public/**: Contiene el archivo `index.php`, que es el punto de entrada de la aplicación.
- **vendor/**: Dependencias del proyecto gestionadas por Composer.

## Instalación
1. Clona el repositorio.
2. Ejecuta `composer install` para instalar las dependencias.
3. Configura el archivo `config_mysql.php` con tus credenciales de base de datos.
4. Ejecuta las migraciones necesarias para crear las tablas en la base de datos.

## Uso
Inicia el servidor web y accede a `http://localhost` para ver la aplicación en funcionamiento.

## Contribuciones
Las contribuciones son bienvenidas. Por favor, abre un issue o un pull request para discutir cambios.

## Licencia
Este proyecto está bajo la Licencia MIT.
