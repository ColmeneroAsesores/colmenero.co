import { existsSync, readFileSync, readdirSync } from "node:fs";
import { extname, join, relative, resolve } from "node:path";

const root = resolve(process.cwd());
const errors = [];
const htmlFiles = [];

function walk(directory) {
  for (const entry of readdirSync(directory, { withFileTypes: true })) {
    if ([".git", "node_modules", "tmp"].includes(entry.name)) continue;
    const absolutePath = join(directory, entry.name);
    if (entry.isDirectory()) walk(absolutePath);
    else if (extname(entry.name) === ".html") htmlFiles.push(absolutePath);
  }
}

function count(content, pattern) { return (content.match(pattern) || []).length; }

function validateHtml(file) {
  const content = readFileSync(file, "utf8");
  const displayName = relative(root, file);
  const requiredPatterns = [
    [/<html\b/i, "falta <html>"], [/<head\b/i, "falta <head>"],
    [/<body\b/i, "falta <body>"], [/<main\b/i, "falta <main>"],
    [/<title>[^<]+<\/title>/i, "falta un <title> con contenido"],
    [/<meta\s+name=["']description["']/i, "falta meta description"],
  ];
  for (const [pattern, message] of requiredPatterns) if (!pattern.test(content)) errors.push(`${displayName}: ${message}`);
  for (const tag of ["html", "head", "body", "main"]) {
    if (count(content, new RegExp(`<${tag}\\b`, "gi")) !== count(content, new RegExp(`</${tag}>`, "gi"))) errors.push(`${displayName}: apertura y cierre de <${tag}> no coinciden`);
  }
  for (const [, reference] of content.matchAll(/(?:href|src)=["']([^"']+)["']/gi)) {
    if (/^(?:https?:|mailto:|tel:|#)/i.test(reference)) continue;
    const target = resolve(file, "..", reference.split(/[?#]/)[0]);
    if (!existsSync(target)) errors.push(`${displayName}: recurso inexistente ${reference}`);
  }
}

walk(root);
for (const file of htmlFiles) validateHtml(file);
for (const requiredFile of ["robots.txt", "sitemap.xml", "favicon/favicon.svg", "css/animations.css"]) {
  if (!existsSync(join(root, requiredFile))) errors.push(`Falta archivo crítico: ${requiredFile}`);
}
if (errors.length) {
  console.error("Validación fallida:\n");
  for (const error of errors) console.error(`- ${error}`);
  process.exit(1);
}
console.log(`Validación correcta: ${htmlFiles.length} archivos HTML y recursos internos verificados.`);
