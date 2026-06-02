# Homeo Tabeeb

**Homeo Tabeeb — AI-Powered Homeopathic Doctor**  
Prescription Verified by Specialist Homeopathic Doctor Before Delivery.

A shared-hosting ready PHP 8.3/MySQL application for patient symptom intake, AI-assisted homeopathic case analysis through OpenRouter, specialist doctor verification, prescription generation, COD order workflow, manual WhatsApp confirmation, dataset import, safety red flags, and doctor correction learning.

## Features

- Public homepage, privacy, terms, case tracking, consultation form, chat, order request, and order success pages.
- Multi-step patient consultation form with required, optional, pregnancy, history, and consent fields.
- Backend-only OpenRouter integration; the API key is stored only in `includes/config.php` and never in frontend JavaScript.
- Emergency red-flag detection before AI remedy output.
- Local retrieval from rubrics, remedies, aliases, materia medica, extra sections, formulas, approved formulas, corrections, and safety rules.
- AI JSON output saved as a draft report for specialist doctor review.
- Role-based admin panel for super admin, admin, manager, and doctor users.
- Doctor review workflow to approve, edit, reject, generate printable prescriptions, and save corrections for future AI context.
- Manager/admin COD order tracking with manual WhatsApp `wa.me` confirmation links.
- Import-ready dataset tools for JSON/JSONL Homeo Tabeeb, OOREP/OpenHomeopath, materia medica, extra book sections, and murakkab/formula candidates.

## Requirements

- PHP 8.3+
- MySQL 8+ or compatible MariaDB with InnoDB and FULLTEXT support
- PHP extensions: PDO MySQL, cURL, mbstring, JSON
- Shared hosting or Apache-compatible hosting

## Installation

1. Upload all files to `doctor.hsninnovators.com`.
2. Create a MySQL database and user in your hosting panel.
3. Import `database/schema.sql` into the database.
4. Import `database/seed.sql`.
5. Edit `includes/config.php`.
6. Add database credentials: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
7. Add `OPENROUTER_API_KEY` and set `OPENROUTER_MODEL`.
8. Upload dataset files into `storage/imports/`.
9. Run `tools/import/import_all.php` from browser or CLI.
10. Login as the first super admin: `admin@homeotabeeb.local` / `ChangeMe123!`, then change the password immediately.
11. Test patient consultation at `consultation-form.php`.
12. Test AI chat at `chat.php` using the generated case ID.
13. Test doctor approval from `admin/cases.php` → case view → prescription review.
14. Test order request from the patient chat screen.
15. Test WhatsApp confirmation link from `admin/order-view.php`.
16. Test prescription print/PDF page from `admin/prescriptions.php`.

## Dataset file names

Place import files in `storage/imports/`:

- `knowledge_sources.json`
- `remedies.json`
- `remedy_aliases.json`
- `symptoms_rubrics.json`
- `remedy_symptom_relations.json`
- `materia_medica_pages.jsonl`
- `extra_documents.jsonl`
- `extra_sections.jsonl`
- `formula_candidates.jsonl`
- `approved_formulas.json`

Importers support JSON arrays or JSONL where practical, batch inserts, progress output, resumable re-runs with `INSERT IGNORE` where unique keys exist, and import logs in `storage/logs/`.

## Security checklist

- Change the seeded super admin password immediately.
- Keep `includes/config.php`, `storage/`, and `tools/` protected by `.htaccess`.
- Do not put `OPENROUTER_API_KEY` into JavaScript, HTML, public logs, or browser-visible files.
- Use HTTPS on `https://doctor.hsninnovators.com`.
- Keep `APP_ENV` as `production` after setup.
- Review admin user roles regularly.

## Medical safety

Homeo Tabeeb is designed to support homeopathic consultation and medicine selection. AI output is always a draft. Final prescription is verified by a specialist homeopathic doctor before delivery. Emergency symptoms require urgent medical care and stop remedy recommendations.
