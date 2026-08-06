"""Export the ECF Markdown documents to styled A4 PDFs with Chrome."""

from __future__ import annotations

import argparse
import html
import re
import subprocess
import tempfile
from datetime import date
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]

DEFAULT_DOCUMENTS = (
    "deploiement",
    "documentation-projet",
    "documentation-technique",
    "gestion-projet",
    "manuel-utilisation",
    "synthese-revision",
)

MONTHS = (
    "janvier",
    "février",
    "mars",
    "avril",
    "mai",
    "juin",
    "juillet",
    "août",
    "septembre",
    "octobre",
    "novembre",
    "décembre",
)


def inline_markup(value: str) -> str:
    rendered = html.escape(value, quote=False)
    rendered = re.sub(
        r"`([^`]+)`",
        lambda match: f"<code>{match.group(1)}</code>",
        rendered,
    )
    rendered = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", rendered)
    rendered = re.sub(
        r"\[([^\]]+)\]\(([^)]+)\)",
        r'<a href="\2">\1</a>',
        rendered,
    )
    rendered = re.sub(
        r"&lt;(https?://[^&]+)&gt;",
        r'<a href="\1">\1</a>',
        rendered,
    )
    return rendered


def is_table_separator(line: str) -> bool:
    cells = [cell.strip() for cell in line.strip().strip("|").split("|")]
    return bool(cells) and all(re.fullmatch(r":?-{3,}:?", cell) for cell in cells)


def table_cells(line: str) -> list[str]:
    return [cell.strip() for cell in line.strip().strip("|").split("|")]


def markdown_to_html(markdown: str) -> tuple[str, str]:
    lines = markdown.replace("\r\n", "\n").split("\n")
    output: list[str] = []
    title = "Vite & Gourmand"
    index = 0

    while index < len(lines):
        line = lines[index]
        stripped = line.strip()

        if stripped == "":
            index += 1
            continue

        fence = re.match(r"^```([A-Za-z0-9_-]*)\s*$", stripped)
        if fence:
            language = fence.group(1)
            index += 1
            block: list[str] = []
            while index < len(lines) and not lines[index].strip().startswith("```"):
                block.append(lines[index])
                index += 1
            index += 1
            source = "\n".join(block)
            if language == "mermaid":
                output.append(f'<div class="mermaid">{html.escape(source)}</div>')
            else:
                class_name = f' class="language-{language}"' if language else ""
                output.append(f"<pre><code{class_name}>{html.escape(source)}</code></pre>")
            continue

        heading = re.match(r"^(#{1,6})\s+(.+)$", stripped)
        if heading:
            level = len(heading.group(1))
            value = heading.group(2).strip()
            if level == 1 and title == "Vite & Gourmand":
                title = re.sub(r"[`*_]", "", value)
            output.append(f"<h{level}>{inline_markup(value)}</h{level}>")
            index += 1
            continue

        if (
            stripped.startswith("|")
            and index + 1 < len(lines)
            and is_table_separator(lines[index + 1])
        ):
            headers = table_cells(line)
            index += 2
            rows: list[list[str]] = []
            while index < len(lines) and lines[index].strip().startswith("|"):
                rows.append(table_cells(lines[index]))
                index += 1
            output.append("<table><thead><tr>")
            output.extend(f"<th>{inline_markup(cell)}</th>" for cell in headers)
            output.append("</tr></thead><tbody>")
            for row in rows:
                output.append("<tr>")
                output.extend(f"<td>{inline_markup(cell)}</td>" for cell in row)
                output.append("</tr>")
            output.append("</tbody></table>")
            continue

        bullet = re.match(r"^[-*]\s+(.+)$", stripped)
        if bullet:
            items: list[str] = []
            while index < len(lines):
                match = re.match(r"^\s*[-*]\s+(.+)$", lines[index])
                if not match:
                    break
                value = match.group(1).strip()
                index += 1
                while (
                    index < len(lines)
                    and lines[index].strip()
                    and not re.match(r"^\s*[-*]\s+", lines[index])
                    and not re.match(r"^#{1,6}\s+", lines[index].strip())
                    and not lines[index].strip().startswith("```")
                ):
                    value += " " + lines[index].strip()
                    index += 1
                items.append(value)
            output.append("<ul>")
            output.extend(f"<li>{inline_markup(item)}</li>" for item in items)
            output.append("</ul>")
            continue

        ordered = re.match(r"^\d+\.\s+(.+)$", stripped)
        if ordered:
            items = []
            while index < len(lines):
                match = re.match(r"^\s*\d+\.\s+(.+)$", lines[index])
                if not match:
                    break
                value = match.group(1).strip()
                index += 1
                while (
                    index < len(lines)
                    and lines[index].strip()
                    and not re.match(r"^\s*\d+\.\s+", lines[index])
                    and not re.match(r"^#{1,6}\s+", lines[index].strip())
                    and not lines[index].strip().startswith("```")
                ):
                    value += " " + lines[index].strip()
                    index += 1
                items.append(value)
            output.append("<ol>")
            output.extend(f"<li>{inline_markup(item)}</li>" for item in items)
            output.append("</ol>")
            continue

        paragraph = [stripped]
        index += 1
        while index < len(lines):
            candidate = lines[index].strip()
            if (
                candidate == ""
                or candidate.startswith("```")
                or re.match(r"^#{1,6}\s+", candidate)
                or re.match(r"^[-*]\s+", candidate)
                or re.match(r"^\d+\.\s+", candidate)
                or (
                    candidate.startswith("|")
                    and index + 1 < len(lines)
                    and is_table_separator(lines[index + 1])
                )
            ):
                break
            paragraph.append(candidate)
            index += 1
        output.append(f"<p>{inline_markup(' '.join(paragraph))}</p>")

    return "\n".join(output), title


def document_html(
    content: str,
    title: str,
    mermaid_uri: str,
    version_date: date,
) -> str:
    version = (
        f"Version du {version_date.day} "
        f"{MONTHS[version_date.month - 1]} {version_date.year}"
    )
    return f"""<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>{html.escape(title)}</title>
  <style>
    @page {{ size: A4; margin: 18mm 16mm 20mm; }}
    * {{ box-sizing: border-box; }}
    html {{ color: #18352f; font-family: Arial, Helvetica, sans-serif; font-size: 11.5pt; }}
    body {{ margin: 0; line-height: 1.48; }}
    .document-meta {{ color: #526b65; font-size: 9.5pt; margin: 0 0 9mm; }}
    h1 {{ font-size: 25pt; line-height: 1.2; margin: 0 0 8mm; }}
    h2 {{ border-bottom: 1px solid #cbd9d0; font-size: 18pt; margin: 9mm 0 4mm; padding-bottom: 2mm; }}
    h3 {{ color: #9b2c2c; font-size: 14pt; margin: 7mm 0 3mm; }}
    h4 {{ font-size: 12pt; margin: 6mm 0 2mm; }}
    h1, h2, h3, h4 {{ break-after: avoid; }}
    p {{ margin: 0 0 3.5mm; }}
    ul, ol {{ margin: 0 0 4mm; padding-left: 7mm; }}
    li {{ margin: 1mm 0; }}
    a {{ color: #8f2929; text-decoration: none; }}
    code {{ background: #eef4ef; border-radius: 3px; font-family: Consolas, monospace; font-size: 0.9em; padding: 1px 4px; }}
    pre {{ background: #f4f7f5; border: 1px solid #d4e0d8; border-radius: 5px; break-inside: avoid; margin: 3mm 0 5mm; overflow-wrap: anywhere; padding: 4mm; white-space: pre-wrap; }}
    pre code {{ background: transparent; padding: 0; }}
    table {{ border-collapse: collapse; font-size: 9.5pt; margin: 3mm 0 6mm; width: 100%; }}
    thead {{ display: table-header-group; }}
    tr {{ break-inside: avoid; }}
    th, td {{ border: 1px solid #bdcec3; padding: 2.2mm; text-align: left; vertical-align: top; }}
    th {{ background: #eaf1ec; font-weight: 700; }}
    .mermaid {{ break-inside: avoid; margin: 4mm auto 6mm; text-align: center; }}
    .mermaid svg {{ height: auto !important; max-height: 205mm; max-width: 100% !important; width: 100%; }}
  </style>
  <script src="{mermaid_uri}"></script>
</head>
<body>
  <p class="document-meta">Vite &amp; Gourmand - ECF 2026 - {version}</p>
  {content}
  <script>
    mermaid.initialize({{
      startOnLoad: false,
      theme: 'base',
      securityLevel: 'loose',
      themeVariables: {{ primaryColor: '#eef4ef', primaryTextColor: '#18352f' }}
    }});
    mermaid.run({{ querySelector: '.mermaid' }}).finally(() => {{
      document.documentElement.dataset.mermaidReady = 'true';
    }});
  </script>
</body>
</html>"""


def chrome_path() -> Path:
    candidates = (
        Path(r"C:\Program Files\Google\Chrome\Application\chrome.exe"),
        Path(r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"),
        Path(r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"),
    )
    for candidate in candidates:
        if candidate.exists():
            return candidate
    raise FileNotFoundError("Chrome or Edge was not found.")


def trim_blank_trailing_pages(pdf_path: Path) -> None:
    try:
        from pypdf import PdfReader, PdfWriter
    except ImportError:
        return

    reader = PdfReader(pdf_path)
    page_count = len(reader.pages)
    while page_count > 1:
        page = reader.pages[page_count - 1]
        if (page.extract_text().strip() or page.images or page.get_contents()):
            break
        page_count -= 1

    if page_count == len(reader.pages):
        return

    writer = PdfWriter()
    for page in reader.pages[:page_count]:
        writer.add_page(page)
    temporary_path = pdf_path.with_suffix(".trimmed.pdf")
    with temporary_path.open("wb") as stream:
        writer.write(stream)
    temporary_path.replace(pdf_path)


def export_document(
    slug: str,
    mermaid_path: Path,
    html_dir: Path,
    version_date: date,
) -> Path:
    source = ROOT / "docs" / f"{slug}.md"
    destination = ROOT / "docs" / "pdf" / f"{slug}.pdf"
    content, title = markdown_to_html(source.read_text(encoding="utf-8"))
    html_path = html_dir / f"{slug}.html"
    html_path.write_text(
        document_html(
            content,
            title,
            mermaid_path.resolve().as_uri(),
            version_date,
        ),
        encoding="utf-8",
    )

    destination.parent.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix="ecf-chrome-") as profile:
        command = [
            str(chrome_path()),
            "--headless=new",
            "--disable-gpu",
            "--allow-file-access-from-files",
            "--run-all-compositor-stages-before-draw",
            "--virtual-time-budget=12000",
            "--no-pdf-header-footer",
            f"--user-data-dir={profile}",
            f"--print-to-pdf={destination.resolve()}",
            html_path.resolve().as_uri(),
        ]
        subprocess.run(command, check=True, capture_output=True, text=True)

    trim_blank_trailing_pages(destination)
    if not destination.exists() or destination.stat().st_size < 1000:
        raise RuntimeError(f"PDF export failed for {slug}.")
    return destination


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--mermaid", type=Path, required=True)
    parser.add_argument("--version-date", type=date.fromisoformat, default=date.today())
    parser.add_argument("documents", nargs="*", default=DEFAULT_DOCUMENTS)
    arguments = parser.parse_args()

    if not arguments.mermaid.exists():
        raise FileNotFoundError(arguments.mermaid)

    html_dir = ROOT / "tmp" / "pdfs" / "export-html"
    html_dir.mkdir(parents=True, exist_ok=True)
    for slug in arguments.documents:
        output = export_document(
            slug,
            arguments.mermaid,
            html_dir,
            arguments.version_date,
        )
        print(output)


if __name__ == "__main__":
    main()
