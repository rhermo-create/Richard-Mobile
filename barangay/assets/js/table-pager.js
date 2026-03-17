// Simple table paginator: paginates <table class="portal-table"> elements
(function () {
  const DEFAULT_ROWS_PER_PAGE = 15;

  function createNodeFromHTML(html) {
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstChild;
  }

  function paginateTable(table) {
    const tbody = table.tBodies[0];
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const totalRows = rows.length;
    const rowsPerPage = parseInt(table.getAttribute('data-rows')) || DEFAULT_ROWS_PER_PAGE;
    const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

    if (totalPages <= 1) return; // no pager needed

    // create pager container
    const pager = document.createElement('div');
    pager.className = 'table-pager';

    let currentPage = 1;

    function showPage(page) {
      currentPage = Math.min(Math.max(1, page), totalPages);
      const start = (currentPage - 1) * rowsPerPage;
      const end = start + rowsPerPage;
      rows.forEach((r, i) => {
        r.style.display = (i >= start && i < end) ? '' : 'none';
      });
      renderPager();
    }

    function renderPager() {
      pager.innerHTML = '';

      const prev = createNodeFromHTML('<button class="pager-btn pager-prev">Previous</button>');
      prev.disabled = currentPage === 1;
      prev.addEventListener('click', () => showPage(currentPage - 1));
      pager.appendChild(prev);

      // page number buttons with simple ellipsis handling
      const maxButtons = 7; // total numbered buttons to show
      let start = Math.max(1, currentPage - Math.floor(maxButtons / 2));
      let end = start + maxButtons - 1;
      if (end > totalPages) { end = totalPages; start = Math.max(1, end - maxButtons + 1); }

      if (start > 1) {
        const first = createNodeFromHTML('<button class="pager-page">1</button>');
        first.addEventListener('click', () => showPage(1));
        pager.appendChild(first);
        if (start > 2) pager.appendChild(createNodeFromHTML('<span class="pager-ellipsis">…</span>'));
      }

      for (let p = start; p <= end; p++) {
        const btn = createNodeFromHTML('<button class="pager-page"></button>');
        btn.textContent = String(p);
        if (p === currentPage) btn.classList.add('pager-page--active');
        btn.addEventListener('click', () => showPage(p));
        pager.appendChild(btn);
      }

      if (end < totalPages) {
        if (end < totalPages - 1) pager.appendChild(createNodeFromHTML('<span class="pager-ellipsis">…</span>'));
        const last = createNodeFromHTML('<button class="pager-page"></button>');
        last.textContent = String(totalPages);
        last.addEventListener('click', () => showPage(totalPages));
        pager.appendChild(last);
      }

      const next = createNodeFromHTML('<button class="pager-btn pager-next">Next</button>');
      next.disabled = currentPage === totalPages;
      next.addEventListener('click', () => showPage(currentPage + 1));
      pager.appendChild(next);
    }

    // insert pager after table
    table.parentNode.insertBefore(pager, table.nextSibling);
    showPage(1);
  }

  function init() {
    document.querySelectorAll('table.portal-table').forEach(paginateTable);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
