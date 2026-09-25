(() => {
    'use strict';

    const form = document.querySelector('#analytics-filters');
    const message = document.querySelector('#analytics-message');
    const offersList = document.querySelector('#analytics-offers');
    const apiUrl = document.body.dataset.analyticsApi;
    const number = new Intl.NumberFormat('pt-BR');
    const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    function setMessage(text, type = '') {
        message.textContent = text;
        message.className = `message ${type}`.trim();
    }

    function cell(text) {
        const element = document.createElement('td');
        element.textContent = text;
        return element;
    }

    function render(summary) {
        document.querySelector('#metric-views').textContent = number.format(summary.totals.views);
        document.querySelector('#metric-clicks').textContent = number.format(summary.totals.clicks);
        document.querySelector('#metric-ctr').textContent = `${number.format(summary.totals.ctr)}%`;
        document.querySelector('#metric-sales').textContent = number.format(summary.totals.sales);
        document.querySelector('#metric-conversion').textContent = `${number.format(summary.totals.conversion)}%`;
        document.querySelector('#metric-gross-value').textContent = currency.format(Number(summary.totals.gross_value));
        document.querySelector('#metric-commission-value').textContent = currency.format(Number(summary.totals.commission_value));
        offersList.replaceChildren();

        if (summary.offers.length === 0) {
            const row = document.createElement('tr');
            const empty = cell('Nenhum evento encontrado no período selecionado.');
            empty.colSpan = 4;
            row.append(empty);
            offersList.append(row);
            return;
        }

        summary.offers.forEach((offer) => {
            const row = document.createElement('tr');
            const offerCell = document.createElement('td');
            const title = document.createElement('strong');
            const identifier = document.createElement('small');
            title.textContent = offer.title;
            identifier.textContent = offer.offer_id === null
                ? `Produto #${offer.product_id}`
                : `Oferta #${offer.offer_id}`;
            offerCell.append(title, identifier);
            row.append(
                offerCell,
                cell(number.format(offer.views)),
                cell(number.format(offer.clicks)),
                cell(`${number.format(offer.ctr)}%`),
            );
            offersList.append(row);
        });
    }

    async function loadSummary() {
        setMessage('Carregando métricas...');
        const query = new URLSearchParams(new FormData(form));

        try {
            const response = await fetch(`${apiUrl}?${query}`, { credentials: 'same-origin' });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                const fields = Object.values(payload.error?.fields ?? {});
                throw new Error(fields[0] ?? payload.error?.message ?? 'Não foi possível carregar as métricas.');
            }

            render(payload.data.summary);
            setMessage('Métricas atualizadas.', 'success');
        } catch (error) {
            setMessage(error.message, 'error');
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadSummary();
    });
    document.querySelector('#refresh-analytics').addEventListener('click', loadSummary);
    loadSummary();
})();
