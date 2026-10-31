# DWES. Resumen de teoría – 01_pcRamiro

## 1. Arquitectura Cliente-Servidor en Aplicaciones Web

- **Parte del Cliente (Navegador)**: Ejecuta el HTML y el CSS, encargándose de capturar los datos que introduce el usuario (inputs). Las peticiones hacia el servidor se inician mediante solicitudes HTTP compuestas por URLs (simples, con parámetros GET o mediante el atributo action de un formulario POST).
- **Parte del Servidor (PHP / Localhost)**: El servidor (como localhost) procesa las peticiones, lee los parámetros GET y POST, ejecuta la lógica de negocio, realiza validaciones y genera dinámicamente el HTML que se devuelve al cliente.
- **Autoridad de Validación**: El cliente solo captura datos; la autoridad real para validar que la información sea correcta y segura reside siempre en el servidor.

## 2. Estructura y Organización Modular del Proyecto

El proyecto organiza sus responsabilidades en archivos independientes para mantener el código estructurado:

- **Datos (datos.php)**: Almacena las constantes, los datos de carga iniciales y el catálogo de productos.
- **Funciones (funciones.php)**: Agrupa las funciones reutilizables y la lógica pura en PHP.
- **Punto de entrada (index.php)**: Sirve como catálogo principal y procesa la lógica inicial de ordenación y filtrado.
- **Módulos de negocio**: Archivos específicos para el detalle de producto, búsqueda, formulario de compra, validaciones y resumen.
- **Estilos CSS**: PHP asigna clases de forma dinámica a las etiquetas HTML en función del estado de los datos (por ejemplo, cambios de estilo según el stock).

## 3. Sintaxis Base y Manejo de Variables en PHP

- **Delimitadores**: El código PHP se encierra entre &lt;?php y ?&gt;. En archivos que contienen exclusivamente código PHP (como los de funciones), no es imprescindible incluir la etiqueta de cierre.
- **Sintaxis abreviada de echo**: La expresión &lt;?= ... ?&gt; se utiliza para imprimir directamente el resultado de un valor en el HTML.
- **Variables**: Se declaran antecediendo el signo dólar (\$nombreVariable = valor). Es necesario inicializarlas antes de su uso para evitar advertencias de variables no declaradas por parte del servidor.
- **Tipos de datos básicos**: Uso de string (con comillas simples o dobles), int, float, boolean, null y array.

## 4. Arrays y Constantes

- **Constantes**: Almacenan valores fijos que no cambian durante la ejecución y, por convención de buenas prácticas, se definen en mayúsculas.
- **Arrays Indexados**: Permiten añadir elementos al final de la estructura mediante la sintaxis \$array\[\] = valor sin sobrescribir el contenido previo.
- **Arrays Asociativos**: Organizan la información mediante pares de clave y valor ('clave' => 'valor'), permitiendo acceder a campos específicos como \$producto\['id'\].

## 5. Operadores y Coalescencia Nula

- **Operadores estándar**: Asignación (=), comparación e igualdad estricta (===), desigualdad estricta (!==), operadores lógicos (&&, ||, !), concatenación mediante punto (.) y operaciones aritméticas básicas.
- **Operador de Coalescencia Nula (??)**: Evalúa si una expresión (como \$\_GET\['id'\]) es nula y, de ser así, le asigna un valor por defecto para garantizar que la variable nunca sea nula.

## 6. Estructuras de Control, Funciones y Tipado

- **Estructuras de control**: Condicionales if/else y bucles foreach para recorrer arrays e integrar bloques dinámicos dentro del HTML.
- **Declaración de funciones**: Se definen mediante la palabra clave function, especificando parámetros de entrada y tipos de retorno (por ejemplo, : string).
- **Importación de archivos**: Uso de require_once para incluir archivos de código una sola vez durante la ejecución.
- **Retorno Anulable (?tipo)**: Indicar un signo de interrogación antes del tipo de retorno (ej. : ?array) especifica que la función puede devolver el tipo indicado (un array) o null si el recurso buscado no existe.
- **Funciones nativas empleadas**:
  - number_format(): Formatea cantidades numéricas definiendo decimales y separadores de millares.
  - Cadenas: trim(), strtolower(), strtoupper() y str_contains().
  - Validación y seguridad: filter_var() y htmlspecialchars().

## 7. Peticiones HTTP: GET vs. POST y Códigos de Error

- **Método GET**: Transmite los parámetros visibles en la URL (adecuado para ordenación o búsquedas de catálogo) por ser rápido y público.
- **Método POST**: Los datos viajan protegidos en el cuerpo (body) de la petición HTTP (utilizado en formularios de compra). El servidor detecta el método recibido mediante \$\_SERVER\['REQUEST_METHOD'\].
- **Códigos de error HTTP**:
  - **Error 400 (Bad Request)**: El parámetro enviado no es válido o coherente con lo que requiere la búsqueda (por ejemplo, enviar texto o números negativos cuando se espera un ID numérico válido).
  - **Error 404 (Not Found)**: El identificador enviado sí es coherente y tiene un formato válido, pero el recurso solicitado no existe en la base de datos o sistema.

## 8. Validación en Tres Niveles, Seguridad y Máscaras de Vista

- **Tres niveles de validación**:
  1. Formato: Comprobar la estructura de los datos (por ejemplo, validar un email mediante filter_var con FILTER_VALIDATE_EMAIL).
  2. Existencia: Verificar que el recurso o ID existe en el sistema.
  3. Negocio / Operativa: Validar las reglas de la aplicación (por ejemplo, verificar si hay stock suficiente para realizar la compra).
- **Escapado de datos de salida**: Aplicación de funciones como htmlspecialchars() para evitar la inyección indeseada de código HTML.
- **Máscaras de Vista**: Aislar los datos crudos para presentar descripciones amigables en la interfaz (como mostrar "Agotado" en lugar de un valor 0, o "Últimas unidades" en lugar de < 5).

## Arquitectura Cliente-Servidor en Aplicaciones Web

En la arquitectura de aplicaciones web en PHP, la ejecución se divide de forma estricta entre el cliente y el servidor. El cliente (el navegador web) es el encargado de procesar y renderizar el HTML y el CSS, capturando además las entradas de datos (inputs) introducidas por el usuario. La comunicación desde el cliente hacia el servidor se realiza mediante solicitudes HTTP a través de URLs, ya sea incorporando parámetros GET en la barra de direcciones o enviando peticiones POST asociadas al atributo action de un formulario.

Por su parte, el servidor (que en entornos de desarrollo local funciona bajo un servidor web como localhost) recibe las peticiones, lee los parámetros entrantes, ejecuta la lógica de negocio, evalúa las estructuras condicionales, realiza las validaciones y construye dinámicamente el HTML que se devuelve al cliente. Es fundamental remarcar que el cliente actúa únicamente como interfaz visual y capturador de datos, mientras que la autoridad real y definitiva para validar y procesar la información reside de forma exclusiva en el servidor. Un ejemplo del flujo completo ocurre al solicitar la ordenación de un catálogo: el navegador realiza una petición GET a la dirección index.php?orden=precio, el servidor PHP captura dicho parámetro, ordena la estructura de datos en memoria, genera el marcado HTML de los productos y entrega el documento final renderizado al navegador.

## Organización Modular y Estructura del Código

La separación de responsabilidades en un proyecto PHP se organiza dividiendo el código en archivos independientes según su propósito:

- **Archivo de datos (datos.php)**: Actúa como repositorio central de información, almacenando las constantes de configuración, los datos de carga iniciales y las estructuras multidimensionales del catálogo.
- **Archivo de funciones (funciones.php)**: Contiene la lógica de negocio reutilizable y la declaración de las funciones auxiliares de la aplicación.
- **Punto de entrada (index.php)**: Es el controlador y vista principal que muestra el catálogo, intercepta las opciones de filtrado u ordenación y coordina el renderizado inicial.
- **Módulos transaccionales y de consulta**: Archivos dedicados como producto.php para la búsqueda y detalle individual, junto a scripts específicos para la gestión del formulario de compra, procesamiento de validaciones y generación del resumen de pedido.
- **Asignación de clases CSS dinámicas**: PHP permite condicionar el atributo class de las etiquetas HTML según los valores del modelo de datos (por ejemplo, aplicando estilos de alerta a las tarjetas de productos según el nivel de stock).

## Sintaxis Base de PHP y Gestión del Ciclo de Vida de Variables

El código ejecutable en PHP se encierra entre las etiquetas de apertura &lt;?php y cierre ?&gt;. En archivos que contienen de forma exclusiva lógica en PHP (como bibliotecas de funciones), es una buena práctica omitir la etiqueta de cierre ?> para prevenir la emisión accidental de caracteres o espacios en blanco hacia el búfer de salida. Asimismo, para volcar directamente el contenido de una variable dentro de la plantilla HTML se utiliza la sintaxis reducida &lt;?= \$variable ?&gt;.

En la gestión de memoria y variables se diferencian tres conceptos clave:

1. **Declarar**: Consiste en informar al intérprete sobre la existencia e identificador de una variable en el programa.
2. **Inicializar**: Implica asignar un valor inicial por defecto (por ejemplo, \$errores = \[\] o \$id = null) para garantizar que la variable tenga una estructura limpia y no se encuentre vacía o indefinida. Inicializar las variables antes de su consumo es obligatorio para evitar que el servidor emita advertencias de tipo undefined variable.
3. **Instanciar**: Se reserva en exclusiva para la Programación Orientada a Objetos (POO) y describe el proceso de crear un objeto en la memoria RAM a partir de una clase utilizando la palabra clave new. La sentencia \$variable = \[\] en PHP no constituye una instanciación, sino la declaración e inicialización combinada de una variable nativa de tipo array.

PHP maneja tipos de datos nativos como string (entre comillas simples o dobles), int, float, boolean, null y array. Por su parte, las **constantes** se definen por convención en mayúsculas y almacenan valores fijos que no pueden ser modificados tras su asignación inicial.

## Estructuras de Datos: Arrays Indexados y Asociativos

Los arrays representan la estructura primaria para agrupar información en PHP:

- **Arrays Indexados**: Almacenan elementos mediante un índice numérico correlativo. Para añadir un nuevo elemento al final de un array indexado sin sobrescribir los valores existentes se utiliza la sintaxis vacía de corchetes: \$errores\[\] = "Mensaje de error".
- **Arrays Asociativos**: Vinculan pares de clave y valor mediante el operador de asociación => (por ejemplo, 'precio' => 19.99). Para consultar un atributo específico dentro de la estructura se accede especificando el nombre de la clave en corchetes: \$producto\['id'\].

## Operadores, Evaluación en Cortocircuito y Coalescencia Nula

PHP proporciona operadores para la asignación (=), la comparación de igualdad estricta que valida tanto valor como tipo de dato (===), y la desigualdad estricta (!==). La concatenación de cadenas se realiza mediante el punto (.), mientras que las operaciones lógicas emplean los símbolos && (AND), || (OR) y ! (NOT).

El **operador de coalescencia nula (??)** evalúa si una expresión es nula y, de ser así, le asigna un valor por defecto en una sola instrucción (por ejemplo, \$id = \$\_GET\['id'\] ?? ''), garantizando que la variable resultante contenga siempre un valor seguro y no null. Por otro lado, la **evaluación en cortocircuito** de los operadores lógicos interrumpe la ejecución de una condición compuesta si el primer término determina el resultado final (por ejemplo, en \$var !== null && \$var > 0, si \$var es nula, el segundo término no se llega a evaluar, evitando errores en tiempo de ejecución).

## Estructuras de Control, Declaración de Funciones y Tipado

El control del flujo de ejecución se apoya en condicionales if/else y bucles foreach. La sintaxis de foreach requiere indicar primero el array a iterar y posteriormente la variable elemento mediante la estructura foreach (\$productos as \$producto).

En la definición de funciones personalizadas se utiliza la palabra clave function, pudiendo declarar parámetros de entrada y especificar el tipo de retorno tras los dos puntos (por ejemplo, function buscar(int \$id): string). El indicar el tipo de dato (type hinting) en los parámetros es una buena práctica en PHP para garantizar la robustez del código y autodocumentar la interfaz. A diferencia de JavaScript básico (donde los parámetros de función son dinámicos y carecen de tipado nativo a menos que se use TypeScript o comprobaciones manuales mediante typeof), PHP permite validar los tipos de forma nativa.

Cuando una función puede no encontrar un resultado, se utiliza el **operador de retorno anulable (?tipo)**. Colocar un signo de interrogación antes del tipo de retorno (como : ?array) indica que la función devolverá la estructura esperada o un valor null si la búsqueda fracasa. Para la importación de archivos de código externos se emplea require_once, lo que asegura la inclusión del fichero exactamente una sola vez durante el flujo de ejecución.

## Peticiones HTTP (GET vs. POST) y Códigos de Error (400 vs. 404)

Las peticiones hacia el servidor se gestionan diferenciando el método de transporte HTTP:

- **Método GET**: Envía los datos expuestos directamente en la URL. Es un método rápido e ideal para operaciones de lectura como filtros o búsquedas en catálogos.
- **Método POST**: Transporta la información de forma oculta en el cuerpo (body) de la petición HTTP, siendo el estándar obligatorio para el envío de datos sensibles o formularios de compra. La detección del método en el servidor se efectúa leyendo la variable superglobal \$\_SERVER\['REQUEST_METHOD'\].

En la respuesta del servidor ante peticiones anómalas se distinguen dos códigos de estado HTTP esenciales:

- **HTTP 400 (Bad Request)**: Ocurre cuando el parámetro enviado no posee una estructura coherente o válida con el recurso esperado (por ejemplo, enviar texto o valores negativos cuando la búsqueda requiere un identificador numérico entero).
- **HTTP 404 (Not Found)**: Ocurre cuando la petición es coherente y el identificador cumple con la sintaxis válida (por ejemplo, solicitar el ID 200), pero dicho elemento no existe almacenado en el sistema.

## Validación en Tres Niveles, Seguridad y Máscaras de Vista

Cualquier procesamiento de datos en el servidor debe estructurarse bajo **tres niveles de validación**:

1. **Nivel 1: Validación de Formato**: Comprueba que los datos cumplan la estructura exigida (por ejemplo, validar un correo electrónico utilizando la función filter_var(\$email, FILTER_VALIDATE_EMAIL)).
2. **Nivel 2: Validación de Existencia**: Verifica que el registro o ID consultado exista efectivamente en el sistema de datos.
3. **Nivel 3: Validación de Negocio u Operativa**: Comprueba el cumplimiento de las reglas operativas de la aplicación (por ejemplo, comprobar si la cantidad solicitada no supera el stock disponible).

Para garantizar la seguridad y evitar ataques de inyección de código al imprimir datos introducidos por el usuario, se debe aplicar la función de escapado htmlspecialchars(), la cual convierte los caracteres especiales en entidades HTML neutras. Por último, las **máscaras de vista** permiten desacoplar los valores numéricos crudos de la interfaz del usuario, traduciendo por ejemplo un stock de 0 al texto "Agotado", o valores inferiores a 5 al mensaje "Últimas unidades".

Entre las funciones nativas clave destacan number_format() para dar formato a valores numéricos definiendo decimales y separadores, trim() para limpiar espacios al inicio y final de cadenas, strtolower()/strtoupper() para transformar el formato de caja, y str_contains() para verificar la presencia de subcadenas en un texto.