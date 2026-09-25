(() => {
    'use strict';

    const form = document.querySelector('#sale-form');
    const importForm = document.querySelector('#sales-import-form');
    const list = document.querySelector('#sales-list');
    const formMessage = document.querySelector('#form-message');
    const listMessage = document.querySelector('#list-message');
    const importMessage = document.querySelector('#import-message');
    const importErrors = document.querySelector('#import-errors');
    const marketplace = form.elements.marketplace;
    const product = form.elements.product_id;
    const productOptions = Array.from(product.options).slice(1);
    const apiBase = document.body.dataset.salesApi;
    const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const dateTime = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });

    function setMessage(element, text, type = '') {
        element.textContent = text;
        element.className = `message ${type}`.trim();
    }

    function cell(text) {
        const element = document.createElement('td');
        element.textContent = text;
        return element;
    }

    function filterProducts() {
        productOptions.forEach((option) => {
            option.hidden = option.dataset.marketplace !== marketplace.value;
            option.disabled = option.hidden;
        });

        if (product.selectedOptions[0]?.disabled) {
            product.value = '';
        }
    }

    function marketplaceLabel(value) {
        return value === 'mercado_livre' ? 'Mercado Livre' : 'Shopee';
    }

    function statusLabel(value) {
        return ({
            pending: 'Pendente',
            approved: 'Aprovada',
            cancelled: 'Cancelada',
            refunded: 'Reembolsada',
        })[value] ?? value;
    }

    function renderSales(items) {
        list.replaceChildren();

        if (items.length === 0) {
            const row = document.createElement('tr');
            const empty = cell('Nenhuma venda registrada.');
            empty.colSpan = 5;
            row.append(empty);
            list.append(row);
            return;
        }

        items.forEach((sale) => {
            const row = document.createElement('tr');
            const saleCell = document.createElement('td');
            const market = document.createElement('strong');
            const reference = document.createElement('small');
            const relationCell = document.createElement('td');
            const productName = document.createElement('strong');
            const campaignName = document.createElement('small');

            market.textContent = `${marketplaceLabel(sale.marketplace)} · ${dateTime.format(new Date(sale.sale_date.replace(' ', 'T')))}`;
            reference.textContent = sale.external_sale_reference ?? `Registro #${sale.id}`;
            saleCell.append(market, reference);

            productName.textContent = sale.product_title ?? 'Sem produto vinculado';
            campaignName.textContent = sale.campaign_name ?? 'Sem campanha vinculada';
            relationCell.append(productName, campaignName);

            row.append(
                saleCell,
                relationCell,
                cell(`${currency.format(Number(sale.gross_value))} · ${sale.quantity} un.`),
                cell(currency.format(Number(sale.commission_value))),
                cell(statusLabel(sale.status)),
            );
            list.append(row);
        });
    }

    async function loadSales() {
        setMessage(listMessage, 'Carregando...');

        try {
            const response = await fetch(`${apiBase}/list.php`, { credentials: 'same-origin' });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.error?.message ?? 'Não foi possível carregar as vendas.');
            }

            renderSales(payload.data.sales);
            setMessage(listMessage, `${payload.data.sales.length} venda(s) carregada(s).`, 'success');
        } catch (error) {
            setMessage(listMessage, error.message, 'error');
        }
    }

    function renderImportErrors(errors) {
        importErrors.replaceChildren();

        errors.slice(0, 20).forEach((error) => {
            const item = document.createElement('li');
            item.textContent = `Linha ${error.line}: ${Object.values(error.fields).join(' ')}`;
            importErrors.append(item);
        });

        if (errors.length > 20) {
            const item = document.createElement('li');
            item.textContent = `${errors.length - 20} erro(s) adicional(is) não exibido(s).`;
            importErrors.append(item);
        }
    }

    importForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setMessage(importMessage, 'Validando e importando...');
        importErrors.replaceChildren();

        try {
            const response = await fetch(`${apiBase}/import.php`, {
                method: 'POST',
                body: new FormData(importForm),
                credentials: 'same-origin',
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                const fields = Object.values(payload.error?.fields ?? {});
                throw new Error(fields[0] ?? payload.error?.message ?? 'Não foi possível importar o CSV.');
            }

            const result = payload.data.import;
            renderImportErrors(result.errors);
            setMessage(
                importMessage,
                `${result.imported} importada(s), ${result.ignored} ignorada(s) e ${result.invalid} inválida(s) em ${result.total_rows} linha(s).`,
                result.invalid === 0 ? 'success' : 'error',
            );
            importForm.reset();
            await loadSales();
        } catch (error) {
            setMessage(importMessage, error.message, 'error');
        }
    });

    marketplace.addEventListener('change', filterProducts);
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setMessage(formMessage, 'Registrando...');

        try {
            const response = await fetch(`${apiBase}/manual.php`, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                const fields = Object.values(payload.error?.fields ?? {});
                throw new Error(fields[0] ?? payload.error?.message ?? 'Não foi possível registrar a venda.');
            }

            form.reset();
            form.elements.quantity.value = '1';
            form.elements.commission_value.value = '0,00';
            filterProducts();
            setMessage(formMessage, 'Venda registrada.', 'success');
            await loadSales();
        } catch (error) {
            setMessage(formMessage, error.message, 'error');
        }
    });

    document.querySelector('#refresh-sales').addEventListener('click', loadSales);
    filterProducts();
    loadSales();
})();
