# Auditoría de páginas · theme_customboost

Recorre las páginas más usadas de Moodle con el tema activo, a 360, 768 y 1280 px, y en cada una comprueba:

| Comprobación | Criterio |
|---|---|
| Desborde horizontal | `scrollWidth` del documento no mayor que el viewport (WCAG 1.4.10). Lista los elementos culpables. |
| Objetivos táctiles | 44 × 44 px en las zonas del tema (cabecera, panel móvil, hoja de acceso, tarjetas, navegación secundaria); 24 × 24 px o separación suficiente en el resto (WCAG 2.5.8). Los enlaces dentro de un texto quedan exentos. |
| Accesibilidad | axe-core con las reglas WCAG 2.0, 2.1 y 2.2 A/AA, contraste incluido. |
| Errores | Errores de JavaScript y de consola. |
| Regresión visual | Captura del viewport comparada con `baseline/` en las páginas propias del tema (acceso, área personal, Mis cursos, curso). Diferencia > 0,1 % → imagen `.diff.png`. |

> **Solo para sitios de prueba.** Los datos de demostración crean usuarios con contraseñas conocidas.

## Uso

```bash
cd public/theme/customboost/tests/audit
```

```bash
npm install
```

Datos de demostración (usuarios `ana` y `prof`, seis cursos y un curso con todos los tipos de actividad):

```bash
php ../fixtures/demo_site.php --confirm
```

Lista de páginas resuelta contra esos datos:

```bash
php pages.php > pages.json
```

Fija el preset del tema y ejecuta la auditoría con el mismo nombre de modo:

```bash
php ../../../../../admin/cli/cfg.php --component=theme_customboost --name=colourpreset --set=dark
```

```bash
AUDIT_PASSWORD_ADMIN='contraseña-del-admin' node audit.mjs --base=http://localhost:8080 --mode=dark
```

Opciones:

| Opción | Efecto |
|---|---|
| `--only=login,dashboard` | Solo esas páginas |
| `--widths=360` | Solo esos anchos |
| `--visual=login,course` | Páginas con regresión visual (`none` para ninguna) |
| `--update-baseline` | Guarda las capturas actuales como referencia |
| `--report=nombre` | Nombre del informe (por defecto `report`), para que una pasada parcial no sobrescriba la completa |

Sin `AUDIT_PASSWORD_ADMIN` se omiten las páginas de administración.

## Resultados

`results/<modo>/report.md` (resumen y detalle), `report.json` y las capturas. La carpeta `results/` no se versiona.

## Separar lo que es del tema de lo que es del core

Ejecuta la misma auditoría con Boost (`--mode=boost-light`, cambiando antes el tema del sitio a Boost) y compara los
dos `report.json`. Lo que aparece igual con Boost es del core. Así se clasificó la ronda del
[informe de F1](../../docs/informes/2026-11-17.md).

## Referencias visuales

Las capturas de `baseline/` dependen de la tipografía y el renderizado del sistema, así que son fiables en la misma
máquina en la que se generaron. Regénéralas (`--update-baseline`) al cambiar a propósito el aspecto del tema y revisa
las diferencias antes de hacerlo.
