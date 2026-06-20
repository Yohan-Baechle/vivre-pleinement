#!/usr/bin/env node
/**
 * tw-lint — Améliore les classes Tailwind du projet.
 *
 * Trois passes (ordre d'exécution : migrate → size → tri) :
 *   1. MIGRATION — corrige les patterns Tailwind v3 → v4 (bg-gradient→bg-linear,
 *                  flex-shrink→shrink, *-opacity-N → syntaxe slash /N, …).
 *   2. SIZE       — fusionne les utilities directionnelles à valeur/variant égaux,
 *                  sans changer le rendu :
 *                    • w-N h-N → size-N
 *                    • padding / margin / scroll-m / scroll-p (côtés, axes, base)
 *                    • inset (top/right/bottom/left → inset-x/y → inset)
 *                    • gap-x/y, overflow-x/y, overscroll-x/y, border-width
 *                    • rounded par coin (tl/tr/bl/br → t/b/l/r → base)
 *   3. TRI        — réordonne les classes via prettier-plugin-tailwindcss
 *                  (exactement le même ordre que l'extension VSCode / Prettier).
 *
 * DÉTECTION — les classes sont reconnues dans : attributs class/className,
 *   :class / x-bind:class (Alpine), @class('...') ET @class([...]) en tableau
 *   conditionnel (les clés sont transformées, jamais les expressions PHP après =>),
 *   et les tableaux PHP 'class' => '...' (ex. extraAttributes Filament).
 *
 * SÉCURITÉ
 *   - DRY-RUN par défaut : rien n'est écrit, on affiche seulement le diff.
 *   - --write : applique réellement les changements.
 *   - Toutes les transformations préservent le rendu (set de classes équivalent).
 *   - Les transformations « risquées » (rescaling v4 de shadow/rounded/blur/ring)
 *     sont seulement SIGNALÉES, jamais appliquées sauf --aggressive (NON idempotent).
 *
 * USAGE
 *   node scripts/tw-lint.mjs                 # dry-run, tout le périmètre
 *   node scripts/tw-lint.mjs --write         # applique
 *   node scripts/tw-lint.mjs --only=sort     # une seule passe (sort|migrate|size)
 *   node scripts/tw-lint.mjs --aggressive    # tente aussi les conversions risquées
 *   node scripts/tw-lint.mjs path/to/file    # cible un fichier/dossier précis
 *
 * Prérequis : prettier + prettier-plugin-tailwindcss en devDependencies.
 * Le script propose de les installer s'ils manquent.
 */

import { execFileSync } from 'node:child_process';
import { readFileSync, writeFileSync, existsSync, statSync } from 'node:fs';
import { readdir } from 'node:fs/promises';
import { join, relative, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(fileURLToPath(import.meta.url), '..', '..');

const C = {
    reset: '\x1b[0m', dim: '\x1b[2m', bold: '\x1b[1m',
    red: '\x1b[31m', green: '\x1b[32m', yellow: '\x1b[33m',
    blue: '\x1b[34m', cyan: '\x1b[36m', gray: '\x1b[90m',
};
const c = (color, s) => `${C[color]}${s}${C.reset}`;

const argv = process.argv.slice(2);
const flags = new Set(argv.filter((a) => a.startsWith('--')));
const targets = argv.filter((a) => !a.startsWith('--'));
const WRITE = flags.has('--write');
const AGGRESSIVE = flags.has('--aggressive');
const ONLY = [...flags].find((f) => f.startsWith('--only='))?.split('=')[1] ?? null;
const runPass = (name) => !ONLY || ONLY === name;

const DEFAULT_GLOBS = [
    'resources/views',
    'resources/css',
    'resources/js',
    'app/Filament',
];

const EXT_OK = new Set(['.blade.php', '.php', '.css', '.js', '.jsx', '.ts', '.vue', '.html']);

const isCandidate = (path) => {
    if (path.endsWith('.blade.php')) return true;
    return EXT_OK.has(extname(path));
};

async function collect(entry) {
    const abs = join(ROOT, entry);
    if (!existsSync(abs)) return [];
    if (statSync(abs).isFile()) return isCandidate(abs) ? [abs] : [];

    const out = [];
    const walk = async (dir) => {
        for (const d of await readdir(dir, { withFileTypes: true })) {
            const p = join(dir, d.name);
            if (d.isDirectory()) {
                if (['node_modules', 'vendor', '.git', 'dist', 'build'].includes(d.name)) continue;
                await walk(p);
            } else if (isCandidate(p)) {
                out.push(p);
            }
        }
    };
    await walk(abs);
    return out;
}

const CLASS_ATTR_RE =
    /(\b(?:class|className)\s*=\s*|\b(?::class|x-bind:class)\s*=\s*|@class\(\s*)(["'])([\s\S]*?)\2/g;

/** Transforme une chaîne de classes en préservant les interpolations {{ }} / ${ }. */
function applyToValue(value, fn) {
    return value
        .split(/(\{\{[\s\S]*?\}\}|\$\{[\s\S]*?\})/g)
        .map((seg, i) => (i % 2 === 0 ? fn(seg) : seg))
        .join('');
}

/**
 * Détecte les blocs `@class([...])` (tableau Blade) ainsi que les tableaux PHP
 * `'class' => '...'` / `"class" => "..."` (ex. extraAttributes Filament) et applique
 * `fn` aux STRING LITERALS de classes — clés de paires conditionnelles incluses,
 * mais JAMAIS aux expressions PHP situées à droite d'un `=>`.
 */
function transformClassArrays(content, fn) {
    let out = content;

    // 1) Blocs @class([ ... ]) — on transforme chaque string literal du bloc,
    //    sauf si elle suit immédiatement un `=>` (ce serait une valeur PHP).
    out = out.replace(/@class\(\s*\[([\s\S]*?)\]\s*\)/g, (full, body) => {
        return `@class([${transformLiteralsKeysOnly(body, fn)}])`;
    });

    // 2) Tableaux PHP : 'class' => '....'  /  "class" => "...."
    out = out.replace(
        /(['"]class['"]\s*=>\s*)(['"])([\s\S]*?)\2/g,
        (full, prefix, quote, value) => `${prefix}${quote}${applyToValue(value, fn)}${quote}`
    );

    return out;
}

/**
 * Dans le corps d'un `@class([...])`, transforme uniquement les string literals
 * qui sont des CLÉS de classes. Modèle Blade : chaque élément du tableau est soit
 *   'classes...'                (clé seule → classes appliquées inconditionnellement)
 *   'classes...' => $condition  (clé conditionnelle → classes si la condition est vraie)
 * Tout ce qui suit un `=>` (jusqu'à la virgule de séparation au niveau racine) est
 * une expression PHP et ne doit JAMAIS être transformé, même si elle contient une
 * string ressemblant à des classes.
 */
function transformLiteralsKeysOnly(body, fn) {
    let out = '';
    let i = 0;
    let inValue = false; // true dès qu'on a passé un `=>` pour l'élément courant
    let depth = 0;       // profondeur de parenthèses/crochets pour repérer la virgule racine

    while (i < body.length) {
        const ch = body[i];

        // string literal
        if (ch === "'" || ch === '"') {
            const quote = ch;
            let j = i + 1;
            let raw = '';
            while (j < body.length) {
                if (body[j] === '\\') { raw += body[j] + (body[j + 1] ?? ''); j += 2; continue; }
                if (body[j] === quote) { break; }
                raw += body[j];
                j += 1;
            }
            // raw = contenu entre quotes ; j pointe sur la quote fermante
            if (inValue) {
                out += quote + raw + quote;
            } else {
                out += quote + applyToValue(raw, fn) + quote;
            }
            i = j + 1;
            continue;
        }

        // `=>` → on entre dans la partie valeur (expression PHP) de l'élément
        if (ch === '=' && body[i + 1] === '>') {
            inValue = true;
            out += '=>';
            i += 2;
            continue;
        }

        // suivi de profondeur pour ne réagir qu'aux virgules de niveau racine
        if (ch === '(' || ch === '[' || ch === '{') { depth += 1; }
        else if (ch === ')' || ch === ']' || ch === '}') { depth -= 1; }
        else if (ch === ',' && depth === 0) { inValue = false; } // nouvel élément → retour en mode clé

        out += ch;
        i += 1;
    }

    return out;
}

/** Applique `fn` à chaque chaîne de classes trouvée dans le contenu. */
function transformClassStrings(content, fn) {
    return content.replace(CLASS_ATTR_RE, (full, prefix, quote, value) => {
        return `${prefix}${quote}${applyToValue(value, fn)}${quote}`;
    });
}

const SAFE_RENAMES = [
    [/\bbg-gradient-to-([trbl]{1,2})\b/g, 'bg-linear-to-$1'],
    [/\bflex-shrink(-0)?\b/g, 'shrink$1'],
    [/\bflex-grow(-0)?\b/g, 'grow$1'],
    [/\boverflow-ellipsis\b/g, 'text-ellipsis'],
    [/\bdecoration-clone\b/g, 'box-decoration-clone'],
    [/\bdecoration-slice\b/g, 'box-decoration-slice'],
    // Échelles renommées sans changement de valeur en v4 (équivalents stricts).
    [/\b(max-w|min-w)-screen-(sm|md|lg|xl|2xl)\b/g, '$1-$2'],
    // Utilities d'opacité dépréciées : conservées telles quelles ici car la
    // conversion vers la syntaxe slash (bg-black/50) exige de fusionner deux
    // classes (couleur + opacité) — traité plus bas par mergeOpacity, pas ici.
];

/**
 * Convertit les paires « couleur + *-opacity-N » (syntaxe v3, supprimée en v4)
 * vers la syntaxe slash v4 : `bg-black bg-opacity-50` → `bg-black/50`.
 * Ne fusionne que si la couleur et l'opacité partagent le même préfixe utilitaire
 * et le même variant ; sinon laisse intact.
 */
function mergeOpacity(classStr) {
    const utils = ['bg', 'text', 'border', 'ring', 'divide', 'placeholder', 'from', 'via', 'to'];
    let tokens = classStr.split(/(\s+)/);
    const blank = (idx) => {
        tokens[idx] = '';
        if (idx + 1 < tokens.length && /^[ \t]+$/.test(tokens[idx + 1])) {
            tokens[idx + 1] = '';
        } else if (idx - 1 >= 0 && /^[ \t]+$/.test(tokens[idx - 1])) {
            tokens[idx - 1] = '';
        }
    };

    for (const u of utils) {
        const colorRe = new RegExp(`^((?:[a-z0-9-]+:)*)${u}-(?!opacity-)(\\[[^\\]]+\\]|[\\w./%-]+)$`);
        const opacityRe = new RegExp(`^((?:[a-z0-9-]+:)*)${u}-opacity-(\\[[^\\]]+\\]|[\\d]+)$`);
        const colors = [];
        const opacities = [];
        tokens.forEach((tk, idx) => {
            let m = tk.match(colorRe);
            if (m && !tk.includes('/')) { colors.push({ idx, variant: m[1], value: m[2] }); return; }
            m = tk.match(opacityRe);
            if (m) { opacities.push({ idx, variant: m[1], value: m[2] }); }
        });
        for (const op of opacities) {
            const col = colors.find((c) => c.variant === op.variant);
            if (!col) { continue; }
            tokens[col.idx] = `${col.variant}${u}-${col.value}/${op.value}`;
            blank(op.idx);
        }
    }
    return tokens.join('');
}

/** Retire les classes activatrices redondantes sans perturber l'indentation. */
function stripRedundantActivators(classStr) {
    const tokens = classStr.split(/(\s+)/);
    const redundant = /^(transform|filter|backdrop-filter)$/;
    for (let i = 0; i < tokens.length; i++) {
        if (!redundant.test(tokens[i])) {
            continue;
        }
        tokens[i] = '';
        if (i + 1 < tokens.length && /^[ \t]+$/.test(tokens[i + 1])) {
            tokens[i + 1] = '';
        } else if (i - 1 >= 0 && /^[ \t]+$/.test(tokens[i - 1])) {
            tokens[i - 1] = '';
        }
    }
    return tokens.join('');
}

const RISKY_PATTERNS = [
    { re: /\b(shadow|drop-shadow|rounded|blur|backdrop-blur)\b(?!-)/g,
      note: 'sans suffixe → en v4 c\'est l\'échelon « -sm » qu\'il faut viser (shadow→shadow-sm, rounded→rounded-sm, blur→blur-sm…). Vérifier le rendu.' },
    { re: /\b(shadow-sm|rounded-sm|blur-sm|backdrop-blur-sm)\b/g,
      note: 'le « -sm » v3 correspond au défaut sans suffixe en v4 (shadow-sm→shadow-xs). Vérifier le rendu.' },
    { re: /\bring\b(?!-)/g,
      note: 'l\'épaisseur par défaut du ring est passée de 3px (v3) à 1px (v4). Utiliser « ring-3 » pour conserver l\'aspect v3.' },
    { re: /\boutline-none\b/g,
      note: 'renommé « outline-hidden » en v4 (outline-none a désormais un autre sens).' },
];

function migrateSafe(classStr) {
    let out = classStr;
    for (const [re, repl] of SAFE_RENAMES) out = out.replace(re, repl);
    out = stripRedundantActivators(out);
    return out;
}

/**
 * ⚠️ NON IDEMPOTENT — à n'exécuter QU'UNE SEULE FOIS.
 * Le rescaling v4 décale chaque échelon d'un cran (shadow-sm→shadow-xs,
 * rounded→rounded-sm, …). Relancer cette passe re-dégraderait des classes déjà
 * converties. L'ordre des remplacements ci-dessous garantit l'absence de double
 * décalage AU SEIN d'un même passage, pas entre deux exécutions successives.
 */
function migrateAggressive(classStr) {
    let out = migrateSafe(classStr);
    out = out
        .replace(/(?<![\w-])outline-none\b/g, 'outline-hidden')
        .replace(/(?<![\w-])shadow-sm\b/g, 'shadow-xs')
        .replace(/(?<![\w-])shadow\b(?!-)/g, 'shadow-sm')
        .replace(/(?<![\w-])rounded-sm\b/g, 'rounded-xs')
        .replace(/(?<![\w-])rounded\b(?!-)/g, 'rounded-sm')
        .replace(/(?<![\w-])backdrop-blur-sm\b/g, 'backdrop-blur-xs')
        .replace(/(?<![\w-])backdrop-blur\b(?!-)/g, 'backdrop-blur-sm')
        .replace(/(?<![\w-])blur-sm\b/g, 'blur-xs')
        .replace(/(?<![\w-])blur\b(?!-)/g, 'blur-sm')
        .replace(/(?<![\w-])ring\b(?!-)/g, 'ring-3');
    return out;
}

function mergeSize(classStr) {
    const tokenVariant = /^((?:[a-z0-9-]+:)*)([wh])-(\[[^\]]+\]|[\w./%-]+)$/;

    const tokens = classStr.split(/(\s+)/);
    for (let i = 0; i < tokens.length; i++) {
        const a = tokens[i].match(tokenVariant);
        if (!a) continue;
        let j = i + 1;
        while (j < tokens.length && /^\s+$/.test(tokens[j])) j++;
        const b = tokens[j]?.match(tokenVariant);
        if (!b) continue;

        const [, va, axA, valA] = a;
        const [, vb, axB, valB] = b;
        if (va === vb && axA !== axB && valA === valB) {
            tokens[i] = `${va}size-${valA}`;
            tokens[j] = '';
            if (j - 1 > i && /^[ \t]+$/.test(tokens[j - 1])) {
                tokens[j - 1] = '';
            }
        }
    }
    return tokens.join('');
}

/**
 * Familles d'utilities « directionnelles » fusionnables sans changer le rendu.
 * Chaque entrée décrit comment construire un token à partir d'une « part » :
 *   side(p, s)  → côté individuel  (t/r/b/l)         ex. pt-4, top-0, rounded-tl-lg
 *   axis(p, a)  → axe              (x/y)             ex. px-4, inset-x-0, gap-x-4
 *   base(p)     → forme compacte   (tous côtés)      ex. p-4, inset-0, rounded-lg
 * `hasBase` à false → la famille n'a pas de forme compacte tous-côtés (ex. gap,
 * overflow ne fusionnent que x+y… mais overflow A une base, gap aussi → p-N style).
 * `corners` à true → famille à coins (rounded) : côtés = t/r/b/l, mais chaque
 * « côté » provient de deux coins (tl+tr→t, etc.).
 */
const BOX_FAMILIES = [
    // padding / margin : prop = p|m, suffixes classiques
    { prop: 'p', allowNeg: false, side: (p, s) => `${p}${s}`, axis: (p, a) => `${p}${a}`, base: (p) => p, hasBase: true },
    { prop: 'm', allowNeg: true, side: (p, s) => `${p}${s}`, axis: (p, a) => `${p}${a}`, base: (p) => p, hasBase: true },
    // scroll-margin / scroll-padding
    { prop: 'scroll-m', allowNeg: true, side: (p, s) => `${p}${s}`, axis: (p, a) => `${p}${a}`, base: (p) => p, hasBase: true },
    { prop: 'scroll-p', allowNeg: false, side: (p, s) => `${p}${s}`, axis: (p, a) => `${p}${a}`, base: (p) => p, hasBase: true },
    // inset : côtés = top/right/bottom/left, axes = inset-x/inset-y, base = inset
    {
        prop: 'inset', allowNeg: true, hasBase: true,
        side: (p, s) => ({ t: 'top', r: 'right', b: 'bottom', l: 'left' }[s]),
        axis: (p, a) => `inset-${a}`,
        base: () => 'inset',
    },
    // gap : pas de côtés individuels, seulement gap-x / gap-y → gap
    { prop: 'gap', allowNeg: false, hasBase: true, axisOnly: true, axis: (p, a) => `gap-${a}`, base: () => 'gap' },
    // overflow / overscroll : valeurs textuelles, x/y → base
    { prop: 'overflow', allowNeg: false, hasBase: true, axisOnly: true, axis: (p, a) => `overflow-${a}`, base: () => 'overflow', textual: true },
    { prop: 'overscroll', allowNeg: false, hasBase: true, axisOnly: true, axis: (p, a) => `overscroll-${a}`, base: () => 'overscroll', textual: true },
    // border width : border-x/border-y → border, et 4 côtés → border
    { prop: 'border', allowNeg: false, hasBase: true, side: (p, s) => `border-${s}`, axis: (p, a) => `border-${a}`, base: () => 'border' },
    // rounded : coins tl/tr/br/bl → côtés t/r/b/l → base
    {
        prop: 'rounded', allowNeg: false, hasBase: true, corners: true,
        side: (p, s) => `rounded-${s}`,
        axis: null,
        base: () => 'rounded',
        cornerOf: { tl: 't', tr: 't', bl: 'b', br: 'b', tl2: 'l', bl2: 'l', tr2: 'r', br2: 'r' },
    },
];

/**
 * Fusionne les utilities directionnelles dont les segments portent la MÊME valeur
 * et le MÊME variant, sans jamais changer le rendu. Couvre padding, margin, inset,
 * scroll-m/p, gap, overflow(/overscroll), border-width et rounded (coins).
 *
 * Exemples :
 *   pt-4 pb-4 → py-4 ; px-4 py-4 → p-4 ; pt-4 pr-4 pb-4 pl-4 → p-4
 *   top-0 bottom-0 → inset-y-0 ; inset-x-0 inset-y-0 → inset-0
 *   gap-x-4 gap-y-4 → gap-4 ; overflow-x-hidden overflow-y-hidden → overflow-hidden
 *   border-x-2 border-y-2 → border-2 ; rounded-tl-lg rounded-tr-lg → rounded-t-lg
 *
 * SÉCURITÉ : valeurs strictement égales, même variant, même signe. Le passage à la
 * forme compacte n'est tenté que si AUCUN segment résiduel non couvert ne subsiste.
 */
function mergeBox(classStr) {
    let tokens = classStr.split(/(\s+)/);

    const blank = (idx) => {
        tokens[idx] = '';
        if (idx + 1 < tokens.length && /^[ \t]+$/.test(tokens[idx + 1])) {
            tokens[idx + 1] = '';
        } else if (idx - 1 >= 0 && /^[ \t]+$/.test(tokens[idx - 1])) {
            tokens[idx - 1] = '';
        }
    };
    const sameVal = (a, b) => a && b && a.value === b.value;
    const valuePart = '(\\[[^\\]]+\\]|[\\w./%-]+)';
    const variantPart = '((?:[a-z0-9-]+:)*)';

    for (const fam of BOX_FAMILIES) {
        // Construit les regex de reconnaissance pour cette famille.
        const negPrefix = fam.allowNeg ? '(-?)' : '()';

        // Mappe chaque token de la ligne sur (variant, sign, segment, value, idx).
        const groups = new Map(); // clé variant|sign → { sides:{t,r,b,l}, axes:{x,y}, corners:{...} }
        tokens.forEach((tk, idx) => {
            const tryMatch = (token) => {
                const re = new RegExp(`^${variantPart}${negPrefix}${token}-${valuePart}$`);
                const m = tk.match(re);
                return m ? { variant: m[1], sign: fam.allowNeg ? m[2] : '', value: m[m.length - 1] } : null;
            };
            const reg = (variant, sign, kind, slot, value) => {
                const key = `${variant}|${sign}`;
                if (!groups.has(key)) {
                    groups.set(key, { variant, sign, sides: {}, axes: {}, corners: {} });
                }
                groups.get(key)[kind][slot] = { idx, value };
            };

            // coins (rounded)
            if (fam.corners) {
                for (const corner of ['tl', 'tr', 'br', 'bl']) {
                    const hit = tryMatch(fam.side(fam.prop, corner));
                    if (hit) { reg(hit.variant, hit.sign, 'corners', corner, hit.value); return; }
                }
            }
            // côtés t/r/b/l
            if (fam.side && !fam.axisOnly && !fam.corners) {
                for (const s of ['t', 'r', 'b', 'l']) {
                    const hit = tryMatch(fam.side(fam.prop, s));
                    if (hit) { reg(hit.variant, hit.sign, 'sides', s, hit.value); return; }
                }
            }
            // axes x/y
            if (fam.axis) {
                for (const a of ['x', 'y']) {
                    const hit = tryMatch(fam.axis(fam.prop, a));
                    if (hit) { reg(hit.variant, hit.sign, 'axes', a, hit.value); return; }
                }
            }
        });

        for (const { variant, sign, sides, axes, corners } of groups.values()) {
            const emit = (idx, token, value) => { tokens[idx] = `${variant}${sign}${token}-${value}`; };

            // --- rounded : coins → côtés ---
            if (fam.corners) {
                const { tl, tr, br, bl } = corners;
                // tl+tr → t, bl+br → b, tl+bl → l, tr+br → r (paires opposées d'abord)
                if (tl && tr && sameVal(tl, tr)) { emit(tl.idx, 'rounded-t', tl.value); blank(tr.idx); corners.t = { idx: tl.idx, value: tl.value }; delete corners.tl; delete corners.tr; }
                if (bl && br && sameVal(bl, br)) { emit(bl.idx, 'rounded-b', bl.value); blank(br.idx); corners.b = { idx: bl.idx, value: bl.value }; delete corners.bl; delete corners.br; }
                // après fusion en t/b, tenter t+b → base si rien d'autre
                const ct = corners.t, cb = corners.b;
                const leftover = Object.keys(corners).filter((k) => ['tl', 'tr', 'br', 'bl'].includes(k)).length;
                if (ct && cb && sameVal(ct, cb) && leftover === 0) {
                    emit(ct.idx, 'rounded', ct.value); blank(cb.idx);
                }
                continue;
            }

            const t = sides.t, r = sides.r, b = sides.b, l = sides.l, x = axes.x, y = axes.y;

            // 4 côtés égaux, aucun axe → base
            if (fam.hasBase && t && r && b && l && !x && !y
                && sameVal(t, r) && sameVal(t, b) && sameVal(t, l)) {
                emit(t.idx, fam.base(fam.prop), t.value);
                [r, b, l].forEach((s) => blank(s.idx));
                continue;
            }
            // x + y égaux, aucun côté → base
            if (fam.hasBase && x && y && !t && !r && !b && !l && sameVal(x, y)) {
                emit(x.idx, fam.base(fam.prop), x.value);
                blank(y.idx);
                continue;
            }
            // t + b → y
            if (fam.axis && t && b && sameVal(t, b)) {
                emit(t.idx, fam.axis(fam.prop, 'y'), t.value);
                blank(b.idx);
            }
            // l + r → x
            if (fam.axis && l && r && sameVal(l, r)) {
                emit(l.idx, fam.axis(fam.prop, 'x'), l.value);
                blank(r.idx);
            }
        }
    }

    return tokens.join('');
}

let prettierMod = null;
let pluginPath = null;
const sortCache = new Map();

async function ensurePrettier() {
    const pluginDir = join(ROOT, 'node_modules', 'prettier-plugin-tailwindcss');
    const prettierDir = join(ROOT, 'node_modules', 'prettier');

    if (!existsSync(pluginDir) || !existsSync(prettierDir)) {
        console.log(c('yellow', '⚠  prettier / prettier-plugin-tailwindcss absents.'));
        if (!WRITE) {
            console.log(c('gray', '   La passe de TRI sera ignorée en dry-run. Installe avec :'));
            console.log(c('cyan', '   npm i -D prettier prettier-plugin-tailwindcss'));
            return false;
        }
        console.log(c('gray', '   Installation en cours…'));
        try {
            execFileSync('npm', ['i', '-D', 'prettier', 'prettier-plugin-tailwindcss'],
                { cwd: ROOT, stdio: 'inherit' });
        } catch {
            console.log(c('red', '   Échec de l\'installation. Passe de tri ignorée.'));
            return false;
        }
    }

    try {
        prettierMod = (await import(join(prettierDir, 'index.mjs'))).default
            ?? await import(join(prettierDir, 'index.mjs'));
        pluginPath = join(pluginDir, 'dist', 'index.mjs');
        if (!existsSync(pluginPath)) pluginPath = 'prettier-plugin-tailwindcss';
        return true;
    } catch (e) {
        console.log(c('red', `   Impossible de charger prettier (${e.message.split('\n')[0]}). Tri ignoré.`));
        return false;
    }
}

/**
 * Trie une unique chaîne de classes via le plugin officiel, en l'isolant dans un
 * <div> jetable. Renvoie la chaîne triée (ou l'originale si trivial / en échec).
 */
async function sortOneClassString(raw) {
    const trimmed = raw.trim();
    if (!trimmed || !/\s/.test(trimmed) || /[\n\r]/.test(raw)) return raw;
    if (sortCache.has(raw)) return sortCache.get(raw);

    try {
        const formatted = await prettierMod.format(
            `<div class="${trimmed.replace(/"/g, '&quot;')}"></div>`,
            { parser: 'html', plugins: [pluginPath], printWidth: 9999 }
        );
        const m = formatted.match(/class="([\s\S]*?)"/);
        let sorted = m ? m[1].replace(/&quot;/g, '"') : raw;
        const lead = raw.match(/^\s*/)[0];
        const tail = raw.match(/\s*$/)[0];
        sorted = lead + sorted.trim() + tail;
        sortCache.set(raw, sorted);
        return sorted;
    } catch {
        sortCache.set(raw, raw);
        return raw;
    }
}

/** Trie toutes les chaînes de classes d'un contenu de fichier. */
async function prettierSort(content) {
    const toSort = new Set();
    transformClassStrings(content, (seg) => { toSort.add(seg); return seg; });
    for (const seg of toSort) {
        if (!sortCache.has(seg)) sortCache.set(seg, await sortOneClassString(seg));
    }
    return transformClassStrings(content, (seg) => sortCache.get(seg) ?? seg);
}

function showDiff(rel, before, after) {
    const a = before.split('\n');
    const b = after.split('\n');
    const max = Math.max(a.length, b.length);
    const lines = [];
    for (let i = 0; i < max; i++) {
        if (a[i] !== b[i]) {
            if (a[i] !== undefined) lines.push(c('red', `  - ${a[i]}`));
            if (b[i] !== undefined) lines.push(c('green', `  + ${b[i]}`));
        }
    }
    if (lines.length) {
        console.log(c('bold', `\n● ${rel}`));
        console.log(lines.slice(0, 40).join('\n'));
        if (lines.length > 40) console.log(c('gray', `  … (+${lines.length - 40} lignes)`));
    }
}

/** Collecte les classes correspondant à une liste de patterns (token + note). */
function scanPatterns(content, rel, patterns, sink) {
    transformClassStrings(content, (seg) => {
        for (const { re, note } of patterns) {
            const m = seg.match(re);
            if (m) {
                for (const hit of new Set(m)) {
                    sink.push({ rel, token: hit, note });
                }
            }
        }
        return seg;
    });
}

const SUGGESTION_PATTERNS = [
    {
        re: /\bspace-[xy]-[\w.]+\b/g,
        note: 'space-x/space-y : la doc v4 recommande « flex/grid + gap-* » (rendu parfois différent avec des éléments flottants/cachés). À migrer au cas par cas.',
    },
    {
        re: /\b(bg|text|border|ring|shadow|fill|stroke|from|via|to)-\[(#|rgb|hsl|oklch|[\d.]+px_)[^\]]*\]/g,
        note: 'Couleur/ombre en dur [...] : si elle se répète, déclare-la comme token dans @theme (resources/css/app.css) pour rester cohérent avec ton design system.',
    },
];

/** Affiche un récap groupé par note, avec compte par token. */
function printGrouped(title, color, sink, footer) {
    if (!sink.length) return;
    console.log(c('bold', c(color, `\n${title}`)));
    const byNote = new Map();
    for (const r of sink) {
        if (!byNote.has(r.note)) byNote.set(r.note, new Map());
        const m = byNote.get(r.note);
        m.set(r.token, (m.get(r.token) ?? 0) + 1);
    }
    for (const [note, toks] of byNote) {
        console.log(c(color, `\n  • ${note}`));
        for (const [t, n] of [...toks].sort((a, b) => b[1] - a[1]).slice(0, 12)) {
            console.log(c('gray', `      ${t} ×${n}`));
        }
        if (toks.size > 12) console.log(c('gray', `      … (+${toks.size - 12} autres)`));
    }
    if (footer) console.log(c('gray', `\n  ${footer}`));
}

(async () => {
    console.log(c('bold', '\n🎨  tw-lint — amélioration des classes Tailwind\n'));
    console.log(c('gray', `Mode    : ${WRITE ? c('green', 'WRITE (écriture réelle)') : c('yellow', 'DRY-RUN (aucune écriture)')}`));
    console.log(c('gray', `Passes  : ${ONLY ?? 'sort + migrate + size'}${AGGRESSIVE ? c('red', ' + aggressive') : ''}`));

    const entries = targets.length ? targets : DEFAULT_GLOBS;
    const files = (await Promise.all(entries.map(collect))).flat();
    console.log(c('gray', `Fichiers: ${files.length}\n`));

    let prettierReady = false;
    if (runPass('sort')) {
        prettierReady = await ensurePrettier();
    }

    let changed = 0;
    const riskySink = [];
    const suggestionSink = [];

    for (const abs of files) {
        const rel = relative(ROOT, abs);
        const original = readFileSync(abs, 'utf8');
        let next = original;

        // Ordre : migrate → size → sort. Le tri passe en DERNIER pour ranger les
        // classes nouvellement créées par les fusions (ex. inset-x-0), garantissant
        // l'idempotence (un 2ᵉ run ne trouve plus rien à réordonner).
        if (runPass('migrate')) {
            const fn = AGGRESSIVE ? migrateAggressive : migrateSafe;
            next = transformClassStrings(next, fn);
            next = transformClassArrays(next, fn);
            next = transformClassStrings(next, mergeOpacity);
            next = transformClassArrays(next, mergeOpacity);
        }

        if (runPass('size')) {
            next = transformClassStrings(next, mergeSize);
            next = transformClassStrings(next, mergeBox);
            next = transformClassArrays(next, mergeSize);
            next = transformClassArrays(next, mergeBox);
        }

        if (runPass('sort') && prettierReady) {
            next = await prettierSort(next);
        }

        scanPatterns(original, rel, RISKY_PATTERNS, riskySink);
        scanPatterns(original, rel, SUGGESTION_PATTERNS, suggestionSink);

        if (next !== original) {
            changed++;
            showDiff(rel, original, next);
            if (WRITE) writeFileSync(abs, next);
        }
    }

    if (!AGGRESSIVE) {
        printGrouped(
            '⚠  Rescaling modifié en v4 (NON converti — vérifie visuellement) :',
            'yellow', riskySink,
            '→ Confirme le rendu, puis applique avec --aggressive.'
        );
    }
    printGrouped(
        'ℹ  Suggestions de style v4 (jamais appliquées automatiquement) :',
        'cyan', suggestionSink,
        '→ Choix manuel : à traiter au cas par cas.'
    );

    console.log('');
    if (changed === 0) {
        console.log(c('green', '✓ Rien à modifier (tri/migration/size).'));
    } else if (WRITE) {
        console.log(c('green', `✓ ${changed} fichier(s) modifié(s) et écrits.`));
    } else {
        console.log(c('yellow', `↪ ${changed} fichier(s) seraient modifiés. Relance avec --write pour appliquer.`));
    }

    if (WRITE && changed > 0) {
        console.log(c('gray', '\n🔧 Vérification : npm run build…'));
        try {
            execFileSync('npm', ['run', 'build'], { cwd: ROOT, stdio: 'inherit' });
            console.log(c('green', '✓ Build OK — aucune classe ne casse la compilation Tailwind.'));
        } catch {
            console.log(c('red', '✗ Le build a échoué. Inspecte la sortie ci-dessus (git diff / git checkout pour annuler).'));
            process.exitCode = 1;
        }
    }
    console.log('');
})();
