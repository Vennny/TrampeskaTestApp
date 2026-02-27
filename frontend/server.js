const express = require('express');
const fetch   = require('node-fetch');

const app     = express();
const PORT    = process.env.PORT || 3001;
const API_URL = process.env.API_URL || 'http://nginx:80/api';

app.use(express.urlencoded({ extended: true }));
app.use(express.json());

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderErrors(errors) {
    if (!errors || !Object.keys(errors).length) return '';
    const items = Object.values(errors).flat().map(e => `<li>${escHtml(e)}</li>`).join('');
    return `<ul style="color:red">${items}</ul>`;
}

function fieldError(errors, field) {
    if (!errors?.[field]) return '';
    return `<span style="color:red">${escHtml(errors[field][0])}</span>`;
}

function slugify(firstName, lastName, id) {
    return `${firstName}-${lastName}-${id}`
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9-]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

function getIdFromSlug(slug) {
    const parts = slug.split('-');
    const id    = parts[parts.length - 1];
    return isNaN(id) ? null : id;
}

function layout(title, metaDesc, body) {
    return `<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="${escHtml(metaDesc)}">
    <title>${escHtml(title)}</title>
</head>
<body>
${body}
</body>
</html>`;
}

function paginationHtml(meta, perPage) {
    if (!meta) return '';
    const page        = parseInt(meta.page, 10);
    const total_pages = parseInt(meta.total_pages, 10);

    const show = new Set([1, total_pages]);
    for (let i = Math.max(1, page - 2); i <= Math.min(total_pages, page + 2); i++) show.add(i);
    const sorted = [...show].sort((a, b) => a - b);

    let html = `<form method="GET" action="/" style="display:inline">
        <input type="hidden" name="page" value="1">
        Na stránce:
        <select name="per_page" onchange="this.form.submit()">
            ${[1, 5, 10, 25, 50].map(n => `<option value="${n}"${n === parseInt(perPage, 10) ? ' selected' : ''}>${n}</option>`).join('')}
        </select>
    </form> &nbsp; `;

    if (total_pages <= 1) return html + `<strong>[1]</strong>`;

    if (page > 1) html += `<a href="/?page=${page - 1}&per_page=${perPage}">« Předchozí</a> `;

    let prev = 0;
    for (const p of sorted) {
        if (prev && p - prev > 1) html += `<span>...</span> `;
        html += p === page
            ? `<strong>[${p}]</strong> `
            : `<a href="/?page=${p}&per_page=${perPage}">${p}</a> `;
        prev = p;
    }

    if (page < total_pages) html += `<a href="/?page=${page + 1}&per_page=${perPage}">Následující »</a>`;

    return html;
}

// -------------------------------------------------------
// GET / — Contact list
// -------------------------------------------------------
app.get('/', async (req, res) => {
    const page    = parseInt(req.query.page    || '1',  10);
    const perPage = parseInt(req.query.per_page || '10', 10);
    const apiRes  = await fetch(`${API_URL}/contacts/?paginate=true&page=${page}&per_page=${perPage}`, {
        headers: { 'Accept': 'application/json' }
    });
    const data     = await apiRes.json();
    const contacts = data.items ?? [];

    const rows = contacts.map(c => {
        const slug = c.slug || slugify(c.first_name, c.last_name, c.id);
        return `
        <tr>
            <td>${escHtml(c.first_name)}</td>
            <td>${escHtml(c.last_name)}</td>
            <td><a href="mailto:${escHtml(c.email)}">${escHtml(c.email)}</a></td>
            <td>${escHtml(c.phone)}</td>
            <td><button onclick="openNote('${escHtml(c.note ?? '')}')">Zobrazit</button></td>
            <td><a href="/${slug}">Upravit</a></td>
        </tr>`;
    }).join('');

    const body = `
<h1>Seznam kontaktů</h1>
<p><a href="/novy-kontakt">+ Přidat nový kontakt</a></p>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Jméno</th><th>Příjmení</th><th>E-mail</th>
            <th>Telefon</th><th>Poznámka</th><th>Akce</th>
        </tr>
    </thead>
    <tbody>
        ${rows || '<tr><td colspan="6">Žádné kontakty nenalezeny.</td></tr>'}
    </tbody>
</table>

<br><div>${paginationHtml(data._meta, perPage)}</div>

<div id="note-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; margin:10% auto; padding:20px; max-width:500px;">
        <h3>Poznámka</h3>
        <p id="note-text"></p>
        <button onclick="closeNote()">Zavřít</button>
    </div>
</div>
<script>
    function openNote(note) {
        document.getElementById('note-text').textContent = note || '(Bez poznámky)';
        document.getElementById('note-modal').style.display = 'block';
    }
    function closeNote() { document.getElementById('note-modal').style.display = 'none'; }
    document.getElementById('note-modal').addEventListener('click', e => { if (e.target === document.getElementById('note-modal')) closeNote(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNote(); });
</script>`;

    res.send(layout('Kontakty – Seznam', 'Seznam všech kontaktů.', body));
});

// -------------------------------------------------------
// GET /novy-kontakt — Create form
// -------------------------------------------------------
app.get('/novy-kontakt', (req, res) => {
    res.send(layout('Nový kontakt', 'Vytvoření nového kontaktu.', createForm()));
});

function createForm(old = {}, errors = {}) {
    return `
<h1>Nový kontakt</h1>
<p><a href="/">« Zpět na seznam</a></p>
${renderErrors(errors)}
<form method="POST" action="/novy-kontakt">
    <table border="0" cellpadding="6">
        <tr><td><label>Jméno *</label></td><td>
            <input type="text" name="first_name" value="${escHtml(old.first_name)}" required>
            ${fieldError(errors, 'first_name')}
        </td></tr>
        <tr><td><label>Příjmení *</label></td><td>
            <input type="text" name="last_name" value="${escHtml(old.last_name)}" required>
            ${fieldError(errors, 'last_name')}
        </td></tr>
        <tr><td><label>E-mail *</label></td><td>
            <input type="text" name="email" value="${escHtml(old.email)}" required>
            ${fieldError(errors, 'email')}
        </td></tr>
        <tr><td><label>Telefon</label></td><td>
            <input type="text" name="phone" value="${escHtml(old.phone)}" required placeholder="+420777123456">
            ${fieldError(errors, 'phone')}
        </td></tr>
        <tr><td><label>Poznámka</label></td><td>
            <textarea name="note" rows="5" cols="40">${escHtml(old.note)}</textarea>
            ${fieldError(errors, 'note')}
        </td></tr>
        <tr><td colspan="2"><button type="submit">Vytvořit kontakt</button></td></tr>
    </table>
</form>`;
}

// -------------------------------------------------------
// POST /novy-kontakt — Create submit
// -------------------------------------------------------
app.post('/novy-kontakt', async (req, res) => {
    const apiRes = await fetch(`${API_URL}/contacts/`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(req.body),
    });

    if (apiRes.ok) {
        return res.redirect(302, '/');
    }

    const err = await apiRes.json();
    res.status(422).send(layout('Nový kontakt', 'Vytvoření nového kontaktu.', createForm(req.body, err.errors ?? {})));
});

// -------------------------------------------------------
// GET /:slug — Edit form
// -------------------------------------------------------
app.get('/:slug', async (req, res, next) => {
    const id = getIdFromSlug(req.params.slug);
    if (!id) return next();

    const apiRes = await fetch(`${API_URL}/contacts/${id}`, {
        headers: { 'Accept': 'application/json' }
    });

    if (apiRes.status === 404) {
        return res.status(404).send(layout('Nenalezeno', 'Kontakt nenalezen.', '<h1>Kontakt nenalezen</h1><p><a href="/">Zpět</a></p>'));
    }

    const contact       = await apiRes.json();
    const canonicalSlug = contact.slug || slugify(contact.first_name, contact.last_name, contact.id);

    // 301 redirect if slug is outdated (e.g. name changed)
    if (req.params.slug !== canonicalSlug) {
        return res.redirect(301, `/${canonicalSlug}`);
    }

    res.send(layout(
        `${contact.first_name} ${contact.last_name} – Editace`,
        `Editace kontaktu ${contact.first_name} ${contact.last_name}.`,
        editForm(canonicalSlug, contact)
    ));
});

function editForm(slug, old = {}, errors = {}) {
    return `
<h1>Upravit: ${escHtml(old.first_name)} ${escHtml(old.last_name)}</h1>
<p><a href="/">« Zpět na seznam</a></p>
${renderErrors(errors)}
<form method="POST" action="/${slug}">
    <table border="0" cellpadding="6">
        <tr><td><label>Jméno *</label></td><td>
            <input type="text" name="first_name" value="${escHtml(old.first_name)}" required>
            ${fieldError(errors, 'first_name')}
        </td></tr>
        <tr><td><label>Příjmení *</label></td><td>
            <input type="text" name="last_name" value="${escHtml(old.last_name)}" required>
            ${fieldError(errors, 'last_name')}
        </td></tr>
        <tr><td><label>E-mail *</label></td><td>
            <input type="text" name="email" value="${escHtml(old.email)}" required>
            ${fieldError(errors, 'email')}
        </td></tr>
        <tr><td><label>Telefon</label></td><td>
            <input type="text" name="phone" value="${escHtml(old.phone)}" required placeholder="+420777123456">
            ${fieldError(errors, 'phone')}
        </td></tr>
        <tr><td><label>Poznámka</label></td><td>
            <textarea name="note" rows="5" cols="40">${escHtml(old.note)}</textarea>
            ${fieldError(errors, 'note')}
        </td></tr>
        <tr><td colspan="2"><button type="submit">Uložit</button></td></tr>
    </table>
</form>
<hr>
<form method="POST" action="/smazat/${getIdFromSlug(slug)}" onsubmit="return confirm('Opravdu smazat?')">
    <button type="submit">Smazat kontakt</button>
</form>`;
}

// -------------------------------------------------------
// POST /:slug — Edit submit
// -------------------------------------------------------
app.post('/:slug', async (req, res, next) => {
    const id = getIdFromSlug(req.params.slug);
    if (!id) return next();

    const apiRes = await fetch(`${API_URL}/contacts/${id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(req.body),
    });

    if (apiRes.ok) {
        const updated       = await apiRes.json();
        const canonicalSlug = updated.slug || slugify(updated.first_name, updated.last_name, updated.id);
        return res.redirect(302, `/${canonicalSlug}`);
    }

    const err    = await apiRes.json();
    const errors = err.errors ?? {};

    res.status(422).send(layout(
        'Editace – Chyba',
        'Editace kontaktu.',
        editForm(req.params.slug, req.body, errors)
    ));
});

// -------------------------------------------------------
// POST /smazat/:id — Delete
// -------------------------------------------------------
app.post('/smazat/:id', async (req, res) => {
    await fetch(`${API_URL}/contacts/${req.params.id}`, {
        method:  'DELETE',
        headers: { 'Accept': 'application/json' },
    });
    res.redirect(302, '/');
});

app.listen(PORT, () => console.log(`Frontend running on http://localhost:${PORT}`));