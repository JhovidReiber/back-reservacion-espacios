# Sistema de Reserva de Espacios – API Symfony 7 + API Platform

Esta es una API RESTful desarrollada con Symfony 7 y API Platform para gestionar reservas de espacios. 
Incluye autenticación JWT, validación de conflictos de horario y gestión de usuarios.

---

## Tecnologías

* Symfony 7
* API Platform
* JWT Authentication
* MySQL / PostgreSQL
* PHP 8.1+
* Doctrine ORM

---

##  Instalación

1. Clona el repositorio:

   ```bash
   git clone hhttps://github.com/JhovidReiber/back-reservacion-espacios.git
   cd back-reservacion-espacios
   ```

2. Instala las dependencias:

   ```bash
   composer install
   ```

3. Copia el archivo `.env` y configúralo:

   ```bash
   cp .env .env.local
   ```

   Configura tus variables de entorno en `.env.local`:

   ```
   DATABASE_URL="mysql://root:root@127.0.0.1:3306/reservas"
   JWT_PASSPHRASE="tu_clave_privada"
   ```

4. Genera las llaves para JWT:

   ```bash
    mkdir -p config/jwt
    openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:2048
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   ```

5. Crear base de datos:

   ```bash
    php bin/console doctrine:database:create
   ```

6. Ejecuta las migraciones y seeders:

   ```bash
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```

6. Inicia el servidor:

   ```bash
   symfony server:start
   ```

---

## Autenticación

### Registro de usuario

`POST /api/register`

**Body ejemplo:**

```json
{
    "name": "name",
    "username": "username",
    "password": "Contra1234*",
    "role": "ROL_USUARIO"
}
```

### Login y obtención de token JWT

`POST /api/login`

**Body:**

```json
{
    "username": "admin",
    "password": "admin"
}
```

**Respuesta:**

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJh..."
}
```

Luego puedes usar este token en tus peticiones protegidas:

```http
Authorization: Bearer <token>
```

---

## 🔑 Endpoints principales

```bash
   php bin/console debug:router
```

| Método | Ruta                   | Descripción                        |
| ------ | ---------------------- | ---------------------------------- |
| GET    | /api/spaces            | Listar espacios disponibles        |
| GET    | /api/spaces/{id}       | Ver detalle de un espacio          |
| POST   | /api/spaces            | Crear espacio (solo admin)         |
| PATCH  | /api/spaces/{id}       | Editar espacio (solo admin)        |
| DELETE | /api/spaces/{id}       | Eliminar espacio (solo admin)      |
| GET    | /api/reservations      | Listar reservas del usuario actual |
| POST   | /api/reservations      | Crear reserva                      |
| POST   | /user/reservations     | Obtener mis reservas               |
| DELETE | /api/reservations/{id} | Cancelar reserva propia            |

---

## Documentación de la API

Puedes explorar todos los endpoints desde Swagger:

[`http://localhost:8000/api/docs`](http://localhost:8000/api/docs)

---


## Extras

* Validación de conflictos de horario al reservar.
* Relaciones correctamente mapeadas con IRI.
* Protecciones por roles (`ROLE_USER`, `ROLE_ADMIN`).
* Uso de formatos `application/ld+json` con Hydra para compatibilidad RESTful completa.

---
