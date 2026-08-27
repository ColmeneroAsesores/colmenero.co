# COLMENERO Asesores Patrimoniales

Sitio institucional estático de COLMENERO Asesores Patrimoniales.

## Estado

- Rama de producción: `main`
- Página vigente: home institucional
- Despliegue: automático hacia HostGator mediante GitHub Actions
- Subpáginas comerciales: pendientes
- Integración visual de GNP: pendiente del activo oficial
- Animaciones: pendientes; `css/animations.css` se conserva para esa fase

## Tecnologías

- HTML5
- CSS3 modular
- JavaScript Vanilla
- Lucide para iconografía

## Estructura

```text
.
├── .github/workflows/deploy.yml
├── aviso-de-privacidad.html
├── terminos-y-condiciones.html
├── index.html
├── robots.txt
├── sitemap.xml
├── css/
│   ├── style.css
│   ├── responsive.css
│   ├── hero.css
│   ├── philosophy.css
│   ├── process.css
│   ├── advisory.css
│   ├── firm.css
│   ├── footer.css
│   ├── cta.css
│   ├── legal.css
│   └── animations.css
├── favicon/
├── js/script.js
├── media/
└── scripts/validate-site.mjs
```

## Flujo de trabajo

Todo cambio aprobado se integra en `main`. Cada `push` activa GitHub Actions:

1. Descarga el repositorio.
2. Comprueba la sintaxis de JavaScript.
3. Valida la estructura HTML básica y las referencias internas.
4. Detiene el proceso si encuentra un error detectable.
5. Despliega por FTP únicamente cuando las validaciones terminan correctamente.

Validación local:

```bash
node --check js/script.js
node scripts/validate-site.mjs
```

## Principios de desarrollo

- Conservar HTML semántico, accesibilidad y responsive design.
- Mantener los componentes separados en hojas CSS lógicas.
- Evitar dependencias innecesarias.
- No inventar oferta, información jurídica ni decisiones estratégicas.
- Aplicar únicamente activos visuales oficiales.
- Mantener `main` como fuente única de verdad para producción.

## Pendientes

- Sustituir el contenido provisional de las páginas legales cuando exista texto aprobado.
- Integrar el respaldo institucional de GNP cuando el operador entregue el activo oficial.
- Realizar la investigación SEO cruzada antes de definir arquitectura, keywords y subpáginas.
- Construir subpáginas y landing pages únicamente después de su aprobación.
- Definir e implementar animaciones editoriales sutiles.
- Incorporar formularios, analítica y tracking solo cuando exista una decisión posterior.
