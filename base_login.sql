/* 
  En este archivo vas a crear la base de datos dentro Workench 
  Recorde que el Servicio debe este Running para poder hacerlo
  Ademas el usuario que vas a usar para la conexión OJO con este que lo tenes que 
  pasar como parametro dentro del db_connect.php para que funcione
  Para poder obviar el uso del usuario root creamos también un usuario con solo permisos
  para el uso de schema 
  El usuario debe ser el nombre y el apellido del alumno que realice el TP

*/

CREATE DATABASE db_proyecto_login;
use db_proyecto_login;

CREATE USER 'thiagotena'@'localhost' IDENTIFIED BY 'user.123$';
GRANT SELECT, INSERT, UPDATE, DELETE ON db_proyecto_login* TO 'thiagotena'@'localhost';

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
