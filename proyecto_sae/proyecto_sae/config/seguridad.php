<?php
// Encriptación de contraseñas.
// Se usa SHA1 (40 caracteres). Para usar MD5 (32 caracteres) basta cambiar sha1 por md5 aquí
// y en el SQL: SHA1('...') por MD5('...'). La columna PWUSER (40) alcanza para ambos.
function encriptarPw($contrasena) {
    return sha1($contrasena);
}

// Nombre del algoritmo, solo para mostrarlo en pantalla
function nombreAlgoritmoPw() {
    return "SHA1";
}
