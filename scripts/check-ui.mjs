#!/usr/bin/env node
/**
 * check:ui — pemeriksa mekanis aturan design system di CLAUDE.md § A–G.
 *
 * Ini ratchet, bukan linter yang menuntut kesempurnaan: `.check-ui-baseline.json`
 * mencatat berapa pelanggaran yang sudah ada per file, dan build gagal hanya kalau
 * angkanya NAIK. Turun itu bagus — jalankan `npm run check:ui -- --update` untuk
 * mengunci penurunannya supaya tidak bisa naik lagi.
 *
 * Pemakaian:
 *   npm run check:ui                      seluruh resources/views
 *   npm run check:ui -- resources/views/livewire/pos   batasi ke path tertentu
 *   npm run check:ui -- --all             tampilkan semua pelanggaran, bukan yang baru saja
 *   npm run check:ui -- --update          tulis ulang baseline dari keadaan sekarang
 *   npm run check:ui -- --json            keluaran mesin
 *
 * Pengecualian sah: tulis komentar `check-ui-allow` beserta alasannya di baris yang
 * sama atau tepat di atas baris pelanggaran.
 */

import { readFileSync, writeFileSync, existsSync, readdirSync, statSync } from 'node:fs';
import { join, relative, resolve, sep } from 'node:path';

const ROOT = resolve(new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'));
const BASELINE_PATH = join(ROOT, '.check-ui-baseline.json');
const SCAN_ROOT = 'resources/views';
const COMPONENTS_DIR = 'resources/views/components/';

/* ------------------------------------------------------------------ argumen */

const argv = process.argv.slice(2);
const flags = new Set(argv.filter((a) => a.startsWith('--')));
const scopes = argv.filter((a) => !a.startsWith('--')).map((p) => p.replace(/\\/g, '/').replace(/\/$/, ''));

const UPDATE = flags.has('--update');
const SHOW_ALL = flags.has('--all');
const AS_JSON = flags.has('--json');

const tty = process.stdout.isTTY && !AS_JSON;
const c = {
    red: (s) => (tty ? `\x1b[31m${s}\x1b[0m` : s),
    green: (s) => (tty ? `\x1b[32m${s}\x1b[0m` : s),
    yellow: (s) => (tty ? `\x1b[33m${s}\x1b[0m` : s),
    dim: (s) => (tty ? `\x1b[2m${s}\x1b[0m` : s),
    bold: (s) => (tty ? `\x1b[1m${s}\x1b[0m` : s),
};

/* -------------------------------------------------------------------- utils */

/**
 * Apakah posisi ini berada di luar nilai atribut HTML?
 *
 * Dihitung dari jumlah `"` sejak awal file, bukan sejak awal baris: atribut Alpine
 * dan wire: sering membentang beberapa baris, sehingga baris lanjutannya selalu
 * terlihat "genap" kalau paritasnya dihitung per baris.
 */
function outsideAttribute(ctx, lineIndex, column) {
    const before = ctx.quotesBeforeLine[lineIndex] + countQuotes(ctx.lines[lineIndex], column);
    return before % 2 === 0;
}

function countQuotes(line, until = line.length) {
    let n = 0;
    for (let i = 0; i < until; i++) if (line[i] === '"') n++;
    return n;
}

/** Rule berbasis baris. `filter(match, line, column, ctx, lineIndex)` membuang false positive. */
function linePattern(pattern, filter) {
    return (text, lines, path, ctx) => {
        const hits = [];
        lines.forEach((line, i) => {
            const re = new RegExp(pattern.source, pattern.flags.includes('g') ? pattern.flags : pattern.flags + 'g');
            let m;
            while ((m = re.exec(line)) !== null) {
                if (filter && !filter(m, line, m.index, ctx, i)) continue;
                hits.push({ line: i + 1, snippet: line.trim() });
                break; // satu temuan per baris sudah cukup untuk laporan
            }
        });
        return hits;
    };
}

const isBladeComment = (line) => /^\s*\{\{--/.test(line);

/* -------------------------------------------------------------------- aturan */

const RULES = [
    {
        id: 'radius-scale',
        level: 'SEDANG',
        title: 'Radius di luar skala tema',
        hint: 'Pakai rounded-xl (--radius-box) untuk kartu/panel/tabel/modal.',
        // Varian sisi ikut dihitung: rounded-t-3xl sama saja dengan rounded-3xl.
        run: linePattern(/\brounded-(?:[tblrse]{1,2}-)?(2xl|3xl|4xl)\b|\brounded-(?:[tblrse]{1,2}-)?\[(\d+(?:\.\d+)?)(px|rem)\]/, (m) => {
            if (!m[2]) return true; // rounded-2xl/3xl/4xl
            const value = parseFloat(m[2]);
            return m[3] === 'rem' ? value >= 1 : value >= 16;
        }),
    },
    {
        id: 'static-shadow',
        level: 'SEDANG',
        title: 'Shadow di permukaan statis',
        hint: 'Tema ini meratakan semua shadow di terang dan membuangnya di gelap — hierarki lewat base-100/200/300. Shadow hanya untuk modal/dropdown/toast/sticky (beri komentar check-ui-allow).',
        run: linePattern(/\bshadow-(sm|md|lg|xl|2xl)\b|\bshadow-\[/),
    },
    {
        id: 'raw-color',
        level: 'SEDANG',
        title: 'Warna di luar token tema',
        hint: 'Pakai bg-base-100/200/300, text-base-content/70, text-primary, badge-success, dst.',
        run: linePattern(
            /\b(?:bg|text|border|from|via|to|ring|divide|outline|fill|stroke|placeholder)-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-\d{2,3}\b|\b(?:bg|text|border)-(?:white|black)\b/,
        ),
    },
    {
        id: 'raw-hex',
        level: 'SEDANG',
        title: 'Warna hex mentah',
        hint: 'Nilai warna hanya boleh hidup di resources/css/app.css.',
        run: linePattern(/#[0-9a-fA-F]{6}\b/, (m, line) => !isBladeComment(line)),
    },
    {
        id: 'manual-dark',
        level: 'SEDANG',
        title: 'Varian dark: manual',
        hint: 'Token daisyUI sudah mengikuti tema — dark: manual justru membuat dua tema divergen.',
        run: linePattern(/\bdark:/),
    },
    {
        id: 'component-bypass',
        level: 'SEDANG',
        title: 'Markup tandingan komponen',
        hint: 'Pakai <x-button>, <x-badge>, <x-input>, <x-select>, <x-textarea> dari resources/views/components.',
        skip: (path) => path.startsWith(COMPONENTS_DIR),
        run: linePattern(
            /class="[^"]*\b(?:btn|badge)\b|\binput input-bordered\b|\bselect select-bordered\b|\btextarea textarea-bordered\b/,
        ),
    },
    {
        id: 'raw-select',
        level: 'TINGGI',
        title: '<select> mentah',
        hint: '<x-select> yang mengirim data-placeholder/data-clearable ke enhance.js (Tom Select). Select mentah kehilangan pencarian dan tombol clear.',
        skip: (path) => path.startsWith(COMPONENTS_DIR),
        run: linePattern(/<select\b/),
    },
    {
        id: 'dynamic-class',
        level: 'TINGGI',
        title: 'Kelas dirakit dari variabel',
        hint: 'Scanner Tailwind v4 tidak membacanya, jadi kelasnya tidak ikut ter-build. Tulis nama kelas penuh di tiap cabang, atau pakai <x-status-badge>.',
        run: linePattern(/\b(?:bg|text|border|badge|btn|alert|ring|from|to)-\{\{/),
    },
    {
        id: 'enum-internals',
        level: 'TINGGI',
        title: 'Nilai internal dirender ke layar',
        hint: 'Backing value (order_in, no_show) dan token warna bukan bahasa manusia. Pakai ->label() atau <x-status-badge>.',
        run: linePattern(/\{\{\s*\$[^}]*->(?:value|color\(\))\s*\}\}/, (m, line, column, ctx, lineIndex) =>
            outsideAttribute(ctx, lineIndex, column),
        ),
    },
    {
        id: 'dead-affordance',
        level: 'TINGGI',
        title: 'Afordans mati',
        hint: 'href="#" terlihat bisa diklik tapi tidak ke mana-mana. Pakai route() yang nyata, atau bukan link.',
        run: linePattern(/href="#"|href=""|href='#'/),
    },
    {
        id: 'eyebrow',
        level: 'SEDANG',
        title: 'Eyebrow uppercase + tracking lebar',
        hint: 'Judul section cukup satu baris, sentence case.',
        run: linePattern(/tracking-\[0\.[2-9]\d*em\]/),
    },
    {
        id: 'motion',
        level: 'SEDANG',
        title: 'Gerak di luar aturan',
        hint: 'Hanya opacity dan translate, 150–300 ms. Tanpa hover:scale-*.',
        run: linePattern(/\b(?:hover|group-hover|focus):scale-|\bduration-(?:400|500|700|1000)\b|\bduration-\[(?:[4-9]\d\d|\d{4,})ms\]/),
    },
    {
        id: 'live-debounce',
        level: 'SEDANG',
        title: 'wire:model.live tanpa debounce',
        hint: 'Tambahkan .debounce.300ms — tiap ketukan tombol kalau tidak.',
        run: linePattern(/wire:model\.live(?!\.debounce)(?![\w.]*\.debounce)/),
    },
    {
        id: 'plain-loading',
        level: 'SEDANG',
        title: 'Loading berupa teks polos',
        hint: 'Pakai <x-skeleton> yang menyerupai bentuk isinya.',
        run: linePattern(/Memuat data\.\.\.|Memuat\.\.\.|Loading\.\.\./),
    },
    {
        id: 'icon-button-label',
        level: 'SEDANG',
        title: 'Tombol ikon-saja tanpa label',
        hint: 'shape="square|circle" wajib punya `label` — itu yang jadi aria-label dan title.',
        run: (text) => {
            const hits = [];
            const re = /<x-button\b[^>]*>/g;
            let m;
            while ((m = re.exec(text)) !== null) {
                const tag = m[0];
                if (!/\bshape=/.test(tag)) continue;
                if (/\blabel=|\baria-label=/.test(tag)) continue;
                const line = text.slice(0, m.index).split('\n').length;
                hits.push({ line, snippet: tag.replace(/\s+/g, ' ').slice(0, 120) });
            }
            return hits;
        },
    },
    {
        id: 'directive-in-tag',
        level: 'TINGGI',
        title: 'Direktif atau komentar Blade di dalam tag komponen',
        hint: 'Tag komponen tidak ikut ter-compile dan bocor mentah ke HTML. Pakai bentuk prop (:checked="(bool) old(...)"), dan taruh komentar di atas tag, bukan di dalamnya.',
        run: (text) => {
            const hits = [];
            const re = /<x-[\w.:-]+(?:\s[^>]*)?>/g;
            let m;
            while ((m = re.exec(text)) !== null) {
                if (!/@(checked|selected|disabled|readonly|required|class|style)\s*\(|\{\{--/.test(m[0])) continue;
                hits.push({
                    line: text.slice(0, m.index).split('\n').length,
                    snippet: m[0].replace(/\s+/g, ' ').slice(0, 120),
                });
            }
            return hits;
        },
    },
    {
        id: 'money-format',
        level: 'RENDAH',
        title: 'Rupiah tanpa number_format',
        hint: "Rp {{ number_format((float) \$x, 0, ',', '.') }} — plus tabular-nums di kolom angka.",
        run: linePattern(/Rp\s*\{\{(?![^}]*number_format)[^}]*\}\}/),
    },
];

const RULE_BY_ID = new Map(RULES.map((r) => [r.id, r]));
const LEVEL_ORDER = { TINGGI: 0, SEDANG: 1, RENDAH: 2 };

/* ------------------------------------------------------------------ scanning */

function walk(dir, out = []) {
    for (const entry of readdirSync(dir)) {
        const full = join(dir, entry);
        const st = statSync(full);
        if (st.isDirectory()) walk(full, out);
        else if (entry.endsWith('.blade.php')) out.push(relative(ROOT, full).split(sep).join('/'));
    }
    return out;
}

function inScope(path) {
    if (scopes.length === 0) return true;
    return scopes.some((s) => path === s || path.startsWith(s + '/'));
}

/** Baris yang membawa `check-ui-allow`, atau yang tepat di atasnya, dimaafkan. */
function isAllowed(lines, lineNumber) {
    const here = lines[lineNumber - 1] ?? '';
    const above = lines[lineNumber - 2] ?? '';
    return here.includes('check-ui-allow') || above.includes('check-ui-allow');
}

const files = walk(join(ROOT, SCAN_ROOT)).filter(inScope).sort();

// { ruleId: { path: [{line, snippet}] } }
const found = new Map(RULES.map((r) => [r.id, new Map()]));

for (const path of files) {
    const text = readFileSync(join(ROOT, path), 'utf8');
    const lines = text.split(/\r?\n/);

    // Jumlah kutip ganda kumulatif sampai awal tiap baris — dipakai untuk tahu
    // apakah sebuah posisi berada di dalam nilai atribut yang membentang multi-baris.
    const quotesBeforeLine = [0];
    for (let i = 0; i < lines.length; i++) quotesBeforeLine.push(quotesBeforeLine[i] + countQuotes(lines[i]));
    const ctx = { lines, quotesBeforeLine };

    for (const rule of RULES) {
        if (rule.skip?.(path)) continue;
        const hits = rule.run(text, lines, path, ctx).filter((h) => !isAllowed(lines, h.line));
        if (hits.length) found.get(rule.id).set(path, hits);
    }
}

/* ------------------------------------------------------------------ baseline */

const emptyBaseline = { generated: null, note: 'Plafon pelanggaran warisan per file. Turun boleh, naik tidak — lihat CLAUDE.md § H.', rules: {} };
const baseline = existsSync(BASELINE_PATH) ? JSON.parse(readFileSync(BASELINE_PATH, 'utf8')) : emptyBaseline;

const baselineFor = (ruleId, path) => baseline.rules?.[ruleId]?.[path] ?? 0;

if (UPDATE) {
    // Dalam mode scope, hanya file dalam cakupan yang ditulis ulang; sisanya dipertahankan.
    const rules = {};
    for (const rule of RULES) {
        const previous = baseline.rules?.[rule.id] ?? {};
        const next = {};
        for (const [path, count] of Object.entries(previous)) {
            if (!inScope(path)) next[path] = count; // di luar cakupan — biarkan apa adanya
        }
        for (const [path, hits] of found.get(rule.id)) next[path] = hits.length;
        if (Object.keys(next).length) rules[rule.id] = Object.fromEntries(Object.entries(next).sort());
    }

    const payload = { ...emptyBaseline, generated: new Date().toISOString().slice(0, 10), rules };
    writeFileSync(BASELINE_PATH, JSON.stringify(payload, null, 2) + '\n');

    const total = Object.values(rules).reduce((sum, files) => sum + Object.values(files).reduce((a, b) => a + b, 0), 0);
    console.log(`${c.green('✓')} baseline ditulis ulang: ${total} pelanggaran warisan di ${BASELINE_PATH.replace(ROOT + sep, '')}`);
    process.exit(0);
}

/* -------------------------------------------------------------------- verdict */

const report = [];
let newCount = 0;
let fixedCount = 0;

for (const rule of RULES) {
    const perFile = found.get(rule.id);
    const paths = new Set([...perFile.keys(), ...Object.keys(baseline.rules?.[rule.id] ?? {}).filter(inScope)]);

    let current = 0;
    let allowed = 0;
    const fresh = [];

    for (const path of [...paths].sort()) {
        const hits = perFile.get(path) ?? [];
        const limit = baselineFor(rule.id, path);
        current += hits.length;
        allowed += limit;
        if (hits.length > limit) fresh.push({ path, hits, limit });
    }

    const delta = current - allowed;
    if (delta > 0) newCount += delta;
    if (delta < 0) fixedCount += -delta;

    report.push({ rule, current, allowed, delta, fresh, all: perFile });
}

if (AS_JSON) {
    console.log(
        JSON.stringify(
            {
                files: files.length,
                new: newCount,
                fixed: fixedCount,
                rules: report.map((r) => ({
                    id: r.rule.id,
                    level: r.rule.level,
                    current: r.current,
                    baseline: r.allowed,
                    delta: r.delta,
                    newViolations: r.fresh.flatMap(({ path, hits, limit }) =>
                        hits.slice(limit).map((h) => ({ path, line: h.line, snippet: h.snippet })),
                    ),
                })),
            },
            null,
            2,
        ),
    );
    process.exit(newCount > 0 ? 1 : 0);
}

const scopeLabel = scopes.length ? scopes.join(', ') : SCAN_ROOT;
console.log(`\n${c.bold('check:ui')} ${c.dim(`— ${RULES.length} aturan · ${files.length} file · ${scopeLabel}`)}\n`);

report.sort((a, b) => LEVEL_ORDER[a.rule.level] - LEVEL_ORDER[b.rule.level] || b.delta - a.delta);

for (const { rule, current, allowed, delta, fresh, all } of report) {
    if (current === 0 && allowed === 0) continue;

    const mark = delta > 0 ? c.red('✗') : delta < 0 ? c.yellow('↓') : c.green('✓');
    const counts = `${String(current).padStart(3)} / ${String(allowed).padEnd(3)}`;
    const deltaLabel = delta > 0 ? c.red(` +${delta} BARU`) : delta < 0 ? c.yellow(` -${-delta} beres`) : '';
    console.log(`${mark} ${rule.id.padEnd(20)} ${c.dim(counts)}${deltaLabel}  ${c.dim(rule.title)}`);

    if (delta > 0) {
        for (const { path, hits, limit } of fresh) {
            for (const hit of hits.slice(limit)) {
                console.log(`    ${path}:${hit.line}  ${c.dim(hit.snippet.slice(0, 110))}`);
            }
        }
        console.log(`    ${c.dim('→ ' + rule.hint)}\n`);
    } else if (SHOW_ALL && current > 0) {
        for (const [path, hits] of [...all].sort()) {
            for (const hit of hits) console.log(`    ${c.dim(`${path}:${hit.line}  ${hit.snippet.slice(0, 110)}`)}`);
        }
        console.log('');
    }
}

console.log(c.dim('\n   kolom angka: sekarang / plafon baseline\n'));

if (newCount > 0) {
    console.log(`${c.red('GAGAL')} — ${newCount} pelanggaran baru.`);
    console.log(c.dim('Perbaiki, atau kalau memang pengecualian sah tulis komentar `check-ui-allow` + alasannya di baris itu.\n'));
    process.exit(1);
}

if (fixedCount > 0) {
    console.log(`${c.green('LOLOS')} — dan ${fixedCount} pelanggaran warisan hilang.`);
    console.log(c.dim('Kunci penurunannya: npm run check:ui -- --update\n'));
    process.exit(0);
}

console.log(`${c.green('LOLOS')} — tidak ada pelanggaran baru.\n`);
process.exit(0);
