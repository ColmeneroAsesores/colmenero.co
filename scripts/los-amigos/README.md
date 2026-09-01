# Los Amigos — conexión de consulta a Google Sheets

Estado: lectura real confirmada por el usuario en HostGator el 1 de septiembre de 2026. No se han copiado datos personales de la hoja al repositorio.

La interfaz conectada está activa en `los-amigos/control/index.html` y `conectado.html`. Ambos archivos usan el mismo endpoint de lectura autenticado.

## Fuente y reglas verificadas

Hoja: `1cO5P-2vJHZEO-WrSYRX9OioWb1lqvdtSUxZD1MY4y0g`, zona `America/Mexico_City`.
Encabezados en fila 4. Se relacionan `RESERVAS` y `ASIGNACION` por `ID Reserva`.
Se aceptan reservas `Confirmada`, `Check-in` y `Finalizada` con asignaciones `Activa`; se excluyen canceladas y asignaciones liberadas. La consulta histórica de asignaciones liberadas queda pendiente de una regla operativa explícita; no se infiere que una asignación liberada haya sido efectivamente ocupada.
Cada alojamiento tiene su propia tarjeta. Las fechas por unidad provienen de ASIGNACION. Se incluye la salida en la consulta; una llegada de otra reserva en ese mismo día aparece también.
Los estados visibles dependen exclusivamente de fechas. Los checkboxes de entrada/salida no se consultan. Un número total de grupo no se reparte ni se repite entre unidades: varias unidades o recuento incompleto muestran «PERSONAS POR CONFIRMAR».
Se incluyen hostales porque son alojamientos asignados en el sistema vigente.

## Activación en HostGator

1. Confirmar PHP 8.1+ con cURL y OpenSSL y HTTPS en la ruta. El repositorio no almacena secretos.
2. En Google Cloud habilitar Google Sheets API. Crear una cuenta de servicio dedicada sin roles administrativos ni delegación de dominio y descargar su clave JSON.
3. Compartir únicamente esta hoja con el correo de la cuenta de servicio como **Lector**. No publicar la hoja en la web ni habilitar «cualquiera con el enlace».
4. Crear `los-amigos-private` al lado de la raíz pública del dominio (fuera de TODAS las raíces públicas y alias). Puede ajustarse con `LOS_AMIGOS_PRIVATE_DIR` en el entorno del servidor.
5. Subir allí la clave como `google-service-account.json`, permisos 0600. No enviarla a GitHub ni pegarla en el chat.
6. Crear `config.json` en el mismo directorio, permisos 0600, con la estructura `{"users":{"nombre-empleado":"HASH_DE_PASSWORD"}}`. Generar cada hash con `password_hash` de PHP; no usar contraseñas en texto plano. El directorio debe permitir escritura al proceso PHP para el límite de intentos de acceso.
7. Abrir `conectado.html`: el navegador solicitará usuario y contraseña. Verificar que sin autenticación no se recibe ningún dato; probar las fechas acordadas con la hoja y las entradas/salidas del mismo día.
8. Reemplazar la demostración solo cuando estas comprobaciones terminen correctamente.

El navegador recibe únicamente alojamientos, nombres, personas, entrada y salida del día seleccionado. No se envían teléfonos, pagos, IDs ni credenciales. Ninguna ruta escribe en Sheets. La interfaz reconsulta cada minuto mientras permanece visible y al volver a ella; no guarda huéspedes en localStorage ni ofrece funcionamiento offline. Si Google falla, se muestra un error y no un falso resultado vacío.

## Migración

Copiar los archivos de la aplicación y trasladar por separado el directorio privado al nuevo alojamiento. Revisar HTTPS, PHP, permisos y autenticación. La hoja y la cuenta lectora se conservan. Ajustar la variable del directorio privado si hace falta. Revocar accesos que dejen de utilizarse.

## Referencias técnicas

- https://developers.google.com/identity/protocols/oauth2/service-account
- https://developers.google.com/workspace/sheets/api/reference/rest/v4/spreadsheets.values/batchGet
