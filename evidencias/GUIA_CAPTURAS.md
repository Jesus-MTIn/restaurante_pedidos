# Guía de capturas de evidencias

Para demostrar la actividad, realiza las siguientes capturas de pantalla:

1. **Estructura del proyecto Laravel**: captura del árbol de carpetas del proyecto (app, database, routes, tests, docs).
2. **Contratos JSON**: captura del archivo `docs/CONTRATOS_JSON.md` o de un ejemplo de solicitud/respuesta.
3. **Solicitud de registro de pedido**: captura de la herramienta (Postman/curl) enviando el JSON al endpoint `POST /api/v1/pedidos`.
4. **Respuesta exitosa**: captura de la respuesta JSON con `success: true` y los datos del pedido.
5. **Error de validación**: captura de una respuesta 422 con `success: false` y `errors`.
6. **Consulta de pedido**: captura del endpoint `GET /api/v1/pedidos/{id}` con la respuesta completa.
7. **Actualización de estado**: captura del endpoint `PATCH /api/v1/pedidos/{id}/estado` con la respuesta.
8. **Ejecución de pruebas**: captura de la terminal con `php artisan test` y el resumen de pruebas aprobadas.
9. **Ejecución del script de verificación**: captura de la terminal con el script PowerShell y el mensaje final de aprobado.

No generes capturas ficticias; toma las capturas reales durante la ejecución.
