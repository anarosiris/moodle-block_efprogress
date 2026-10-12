#!/bin/bash
# Regenera las guías en PDF tras editar guia.html o guide_en.html (requiere weasyprint y ./fonts).
cd "$(dirname "$0")"
python3 -c "from weasyprint import HTML; HTML('guia.html').write_pdf('Guia_block_efprogress.pdf'); print('PDF regenerado: Guia_block_efprogress.pdf')"
python3 -c "from weasyprint import HTML; HTML('guide_en.html').write_pdf('Guide_block_efprogress_en.pdf'); print('PDF regenerated: Guide_block_efprogress_en.pdf')"
