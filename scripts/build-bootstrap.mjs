// Compiles Bootstrap's SCSS and scopes every selector under ".bootstrap-scope"
// via PostCSS, so Bootstrap's real (unmodified) class names — including the
// ones its JS bundle manipulates directly, like .show/.collapsing/.modal —
// only take effect inside a <div class="bootstrap-scope">...</div> wrapper.
// This keeps Bootstrap and Tailwind's colliding utility names (mt-3, container,
// text-center, ...) from ever fighting outside that wrapper.
import { compile } from 'sass';
import postcss from 'postcss';
import prefixSelector from 'postcss-prefix-selector';
import { writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const srcPath = path.resolve(__dirname, '../resources/css/bootstrap.scss');
const outDir = path.resolve(__dirname, '../public/vendor');
const outPath = path.join(outDir, 'bootstrap.css');

const SCOPE = '.bootstrap-scope';

const { css } = compile(srcPath, {
    style: 'compressed',
    quietDeps: true,
    logger: { warn() {} },
    loadPaths: [path.resolve(__dirname, '../node_modules')],
});

const result = await postcss([
    prefixSelector({
        prefix: SCOPE,
        transform(prefix, selector, prefixedSelector) {
            // :root holds Bootstrap's CSS custom properties — attach them
            // directly to the scope element (not as a descendant selector)
            // so they still cascade to everything inside it.
            if (selector === ':root') {
                return SCOPE;
            }

            return prefixedSelector;
        },
    }),
]).process(css, { from: undefined });

mkdirSync(outDir, { recursive: true });
writeFileSync(outPath, result.css);

console.log(`[build-bootstrap] wrote ${path.relative(process.cwd(), outPath)} (${(result.css.length / 1024).toFixed(1)} KB)`);
