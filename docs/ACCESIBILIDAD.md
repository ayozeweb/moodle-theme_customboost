# Declaración de accesibilidad · Custom Boost

- **Tema:** Custom Boost (`theme_customboost`) **0.7.0** (beta) para Moodle 5.3
- **Fecha de la revisión:** 22 de noviembre de 2026
- **Norma de referencia:** WCAG 2.1 nivel AA, más dos criterios de WCAG 2.2 que afectan a los temas (2.4.11 foco
  no oculto y 2.5.8 tamaño del objetivo). Son los requisitos web de la norma EN 301 549 que aplica el Real Decreto
  1112/2018 en el sector público español, y la base del Anexo IV de la Orden TMS/369/2019 para la formación en
  línea.

Esta declaración describe el **tema**: su aspecto y lo que añade a Moodle. No sustituye la declaración de
accesibilidad de cada sitio, que depende también de la versión de Moodle, de las extensiones instaladas y, sobre
todo, del contenido de los cursos.

## Situación de cumplimiento

**Parcialmente conforme.** Lo que el tema añade o modifica cumple WCAG 2.1 AA en todas las comprobaciones
automáticas y de teclado realizadas. «Parcialmente» por dos motivos:

1. Las pruebas con **lectores de pantalla** y **dispositivos físicos** están pendientes (ver
   [Pendiente](#pendiente)). Hasta hacerlas no se puede afirmar la conformidad total.
2. Quedan algunos problemas del **núcleo de Moodle**, que ocurren igual con el tema Boost (ver
   [Contenido no accesible](#contenido-no-accesible)).

## Alcance

| Incluido | Qué es |
|---|---|
| Plantillas propias | Página de acceso, saludo y tarjetas del área personal, cabecera, franja del pie, vista previa y herramientas de administración |
| Estilos | Colores, tipografía, foco, tamaños de objetivo y adaptación móvil de todas las páginas de Moodle |
| Comportamiento | Paneles laterales en el móvil (teclado y foco) y fila de tarjetas del área personal |

| No incluido | Por qué |
|---|---|
| Funcionamiento de Moodle y de sus actividades | Es del núcleo; el tema solo cambia su aspecto |
| Contenido de los cursos | Lo crea cada profesor |
| Extensiones de terceros | Se revisan por separado |

## Funciones de accesibilidad del tema

- **Contraste garantizado.** Los colores se calculan para alcanzar 4,5:1 en texto y 3:1 en bordes y foco, sea cual
  sea el color de marca elegido. El administrador ve en la página de herramientas las 196 combinaciones con su ratio.
- **Alto contraste.** Dos presets (claro y oscuro) con texto a 7:1 como mínimo (nivel AAA), enlaces siempre
  subrayados, bordes marcados y foco más grueso. Se aplican también automáticamente cuando el dispositivo pide más
  contraste (`prefers-contrast: more`), con cualquier preset.
- **Colores forzados** (alto contraste de Windows): foco, barras de progreso, etiquetas, interruptor de edición y
  página actual del menú siguen visibles.
- **Foco visible** de 3 px (4 px en alto contraste), dibujado con `outline` para que ningún contenedor lo recorte y
  nunca tapado por la cabecera fija.
- **Teclado en el móvil.** Los paneles laterales se cierran con Esc, mantienen el foco dentro mientras están abiertos
  y lo devuelven al botón que los abrió. En la fila deslizable de cursos, la tarjeta enfocada se muestra entera.
- **«Saltar al contenido principal»** lleva al saludo y las tarjetas del área personal, que forman parte de la región
  principal.
- **Objetivos táctiles** de 44 × 44 px en cabecera, menú, acceso y tarjetas, también con la cabecera llena a 320 px.
- **Texto ampliable y reflujo.** Sin desplazamiento horizontal a 320 px ni con el texto al 200 %; los campos de
  formulario nunca bajan de 16 px.
- **Movimiento reducido.** Con «reducir movimiento» no hay animaciones ni desplazamientos suaves.
- **Imágenes.** La imagen de acceso y las de las tarjetas son decorativas (`alt=""`); el texto sobre la imagen de
  acceso mantiene el contraste incluso con fotos casi blancas. Ninguna imagen del tema contiene texto.
- **Tipografía local.** Nada se carga de servidores externos.

## Contenido no accesible

### Del núcleo de Moodle (también con Boost)

| Dónde | Problema | Criterio |
|---|---|---|
| Calificador y lista de entregas de una tarea | Tablas de datos más anchas que el móvil: obligan a desplazarse en horizontal | 1.4.10 (las tablas de datos están exentas) |
| Mensajería | Un elemento enfocable oculto | 2.4.7 |
| Curso en modo edición, 320–344 px | El lápiz de edición rápida tapa parte del enlace de una actividad de nombre largo | 2.5.8 |
| Migas de pan, índice del curso y categoría en *Mis cursos* | Textos largos recortados con puntos suspensivos; el texto completo está en el enlace o en la página de destino | 1.4.4 |
| Página de error con la depuración activada | Avisos de depuración sin estructura | Solo en sitios en modo desarrollador |

### Del tema

| Dónde | Limitación | Alternativa |
|---|---|---|
| Cabecera en el móvil | Con el texto del sistema al 200 % o la cabecera llena, el nombre del sitio se acorta y puede quedar solo el logotipo | El enlace conserva el nombre completo como nombre accesible y el título de la página lo repite |
| Interruptor de edición en el móvil | Su etiqueta visible («Modo de edición») se oculta por espacio | El nombre accesible se conserva; el interruptor sigue visible y mide 44 px |
| Logotipo para modo oscuro en la página de acceso | Con el preset automático se muestra el logotipo normal | Usar un logotipo válido para ambos fondos |
| Fila deslizable de cursos en el móvil | Se recorre en horizontal | Con teclado se recorre con Tab; el botón «Todos mis cursos» da la lista completa |

## Cómo se ha comprobado

| Método | Qué cubre | Resultado |
|---|---|---|
| axe-core (WCAG 2.0, 2.1 y 2.2 A/AA) en 35 páginas | 360, 768 y 1280 px, claro y oscuro | 0 infracciones graves atribuibles al tema |
| Recorrido a 320, 344, 375, 412 y 430 px | Desborde, objetivos táctiles, axe | Sin desbordes del tema |
| Condiciones extremas (`tests/audit/stress.mjs`) | Texto al 200 %, espaciado de texto (1.4.12), zoom al 200 % y 400 %, móviles en horizontal | Sin desbordes ni texto perdido atribuible al tema |
| Muestreo de píxeles | Texto sobre la imagen de acceso, con la ilustración y con una foto casi blanca | Todo ≥ 4,5:1 (el título, texto grande, ≥ 4,76:1) |
| Contraste de los colores | Las 196 combinaciones de los cuatro juegos de colores (PHPUnit, `cli/contrast.php` y página de herramientas) | 100 % AA; 100 % AAA en alto contraste |
| Teclado | Saltar al contenido, paneles del móvil (Esc, Tab, foco al cerrar), fila de tarjetas, foco nunca tapado por la cabecera | Correcto; escenarios Behat en `keyboard.feature` |
| Emulación | `prefers-contrast: more`, `forced-colors: active`, `prefers-reduced-motion` | Correcto |
| Pruebas automáticas en cada cambio | Behat con axe en los presets claro y oscuro, PHPUnit con colores hostiles | En verde en la integración continua |

Detalle de la revisión: [informe de F3](informes/2026-11-22.md).

## Pendiente

Estas pruebas necesitan una persona y equipos reales. El tema se considerará conforme cuando estén hechas al menos
ACC-30 y ACC-32.

| Id | Combinación | Recorrido |
|---|---|---|
| ACC-30 | NVDA + Firefox (Windows) | Ver protocolo |
| ACC-31 | JAWS + Chrome (Windows) | Ver protocolo |
| ACC-32 | VoiceOver + Safari (iOS) | Ver protocolo, con gestos; fila deslizable de tarjetas |
| ACC-33 | TalkBack + Chrome (Android) | Ver protocolo |
| ACC-34 | VoiceOver + Safari (macOS) | Ver protocolo |
| ACC-35 | Alto contraste de Windows real | Bordes, foco e iconos visibles (emulado ya en Chrome) |
| ACC-36 | Ampliador de pantalla al 400 % | La cabecera fija no tapa el contenido |
| ACC-37 | Control por voz (Voice Control, Acceso por voz) | Cada control se activa diciendo su nombre visible |
| MOV-04 a 06 | iPhone y Android físicos | Zonas seguras, teclado virtual sobre el formulario, altura con la barra del navegador |

**Protocolo para lectores de pantalla.** Con el sitio en el preset claro y luego en *Alto contraste oscuro*:

1. Página de acceso: el lector anuncia el encabezado del formulario (en inglés, «Welcome back»); el campo de usuario y el de contraseña
   tienen nombre; con una contraseña errónea el error se anuncia y el foco queda en el formulario.
2. Área personal: «Saltar al contenido principal» lleva al saludo; la navegación por encabezados encuentra el
   saludo (nivel 2), «Sigue aprendiendo» (nivel 3) y cada curso (nivel 4); cada tarjeta anuncia nombre, categoría
   y progreso en texto.
3. Menú en el móvil: el botón anuncia su nombre y estado; al abrirlo el foco entra en el panel; al cerrarlo vuelve al
   botón.
4. Abrir un curso desde una tarjeta y volver.
5. Con los modos de color de Boost activados, cambiar de modo con el selector de la cabecera.

Anota en cada paso si el anuncio es correcto, incompleto o falta, y abre una incidencia con lo que falle.

## Comentarios

Si encuentras una barrera, abre una incidencia en el repositorio del tema indicando la página, el navegador, el
dispositivo, la tecnología de apoyo que usas y qué esperabas que ocurriera. Si la barrera está en tu sitio Moodle y
no en el tema, el administrador de tu sitio es quien puede resolverla.
