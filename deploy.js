const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

console.log("🔨 Iniciando build do Angular...");

try {
  execSync("ng build --configuration production", { stdio: "inherit" });
  console.log("✅ Build do Angular concluído com sucesso!");
} catch (error) {
  console.error("❌ Erro no build do Angular");
  process.exit(1);
}

const distDir = "./dist/pereira-guincho-angular"; // Ajuste o nome se necessário
const browserDir = path.join(distDir, "browser"); // Pasta browser onde ficam os arquivos finais
const prodDir = "./src/production";

console.log("\n📦 Preparando arquivos para deploy...");

// Verifica se a pasta browser existe (onde fica o build final do Angular)
if (!fs.existsSync(browserDir)) {
  console.error("❌ Pasta browser não encontrada:", browserDir);
  console.error(
    "   Verifique se o build do Angular foi executado corretamente"
  );
  process.exit(1);
}

// Copia os 3 arquivos obrigatórios da pasta production para dentro de browser
const filesToCopy = ["index.php", "404.html", ".htaccess"];
let copiedFiles = 0;

filesToCopy.forEach((fileName) => {
  const srcFile = path.join(prodDir, fileName);
  const destFile = path.join(browserDir, fileName);

  if (fs.existsSync(srcFile)) {
    fs.copyFileSync(srcFile, destFile);
    console.log(`   ✅ ${fileName} copiado para browser/`);
    copiedFiles++;
  } else {
    console.warn(`   ⚠️ ${fileName} não encontrado em src/production/`);
  }
});

console.log(`\n📊 Resumo do deploy:`);
console.log(`   📁 Arquivos do Angular: gerados em browser/`);
console.log(
  `   📄 Arquivos de produção: ${copiedFiles}/3 copiados para browser/`
);

if (copiedFiles === 3) {
  console.log("\n🎉 Deploy preparado com sucesso!");
  console.log("📂 Pasta pronta para upload:", path.resolve(browserDir));
  console.log("\n📤 Próximo passo:");
  console.log("   1. Abra a pasta dist/pereira-guincho-angular/browser/");
  console.log("   2. Selecione TODOS os arquivos (Angular + PHP)");
  console.log("   3. Faça upload para public_html/ (raiz do servidor)");
  console.log("\n📋 Arquivos que estarão em public_html/:");
  console.log("   ✓ index.html (Angular SPA)");
  console.log("   ✓ index.php (Controlador de rotas)");
  console.log("   ✓ 404.html (Página de erro personalizada)");
  console.log("   ✓ .htaccess (Configurações do servidor)");
  console.log("   ✓ assets/ + outros arquivos do Angular");
  console.log("\n   📁 A pasta CORRETA para fazer upload é: browser/");
} else {
  console.log("\n⚠️ Deploy preparado, mas alguns arquivos estão faltando!");
  console.log("Verifique se todos os arquivos existem em src/production/");
}
