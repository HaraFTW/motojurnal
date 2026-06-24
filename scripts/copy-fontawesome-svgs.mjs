import { cpSync, existsSync, rmSync } from 'node:fs';

const source = 'node_modules/@fortawesome/fontawesome-free/svgs';
const destination = 'public/build/fontawesome/svgs';

if (! existsSync(source)) {
    console.error('Font Awesome SVG source not found. Run npm install first.');
    process.exit(1);
}

if (existsSync(destination)) {
    rmSync(destination, { recursive: true });
}

cpSync(source, destination, { recursive: true });

console.log('Font Awesome SVGs copied to public/build/fontawesome/svgs');
