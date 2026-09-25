const form = document.querySelector("#offer-form");
const list = document.querySelector("#offers-list");
const formMessage = document.querySelector("#form-message");
const listMessage = document.querySelector("#list-message");
const cancelEditButton = document.querySelector("#cancel-edit");
const formTitle = document.querySelector("#form-title");
const productSelect = form.elements.product_id;
const affiliateLinkSelect = form.elements.affiliate_link_id;
const apiBase = document.body.dataset.offersApi;
const affiliateLinkOptions = Array.from(affiliateLinkSelect.options).slice(1);
const currency = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" });
let offers = new Map();

function setMessage(element, message, type = "") {
    element.textContent = message;
    element.className = `message ${type}`.trim();
}

function filterAffiliateLinks(selectedId = "") {
    const productId = productSelect.value;
    affiliateLinkOptions.forEach((option) => {
        option.hidden = option.dataset.productId !== productId;
        option.disabled = option.hidden;
    });
    affiliateLinkSelect.value = affiliateLinkOptions.some(
        (option) => !option.hidden && option.value === String(selectedId),
    ) ? String(selectedId) : "";
}

function toLocalInput(value) {
    return value ? value.replace(" ", "T").slice(0, 16) : "";
}

function resetForm() {
    form.reset();
    form.elements.id.value = "";
    filterAffiliateLinks();
    formTitle.textContent = "Nova oferta";
    cancelEditButton.hidden = true;
}

function populateForm(offer) {
    form.elements.id.value = offer.id;
    productSelect.value = offer.product_id;
    filterAffiliateLinks(offer.affiliate_link_id);
    form.elements.price.value = offer.price;
    form.elements.old_price.value = offer.old_price ?? "";
    form.elements.coupon_text.value = offer.coupon_text ?? "";
    form.elements.shipping_text.value = offer.shipping_text ?? "";
    form.elements.status.value = offer.status;
    form.elements.starts_at.value = toLocalInput(offer.starts_at);
    form.elements.expires_at.value = toLocalInput(offer.expires_at);
    formTitle.textContent = `Editar oferta #${offer.id}`;
    cancelEditButton.hidden = false;
    form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function createCell(text) {
    const cell = document.createElement("td");
    cell.textContent = text;
    return cell;
}

function renderOffers(items) {
    offers = new Map(items.map((offer) => [String(offer.id), offer]));
    list.replaceChildren();

    if (items.length === 0) {
        const row = document.createElement("tr");
        const cell = createCell("Nenhuma oferta cadastrada.");
        cell.colSpan = 4;
        row.append(cell);
        list.append(row);
        return;
    }

    items.forEach((offer) => {
        const row = document.createElement("tr");
        const priceCell = document.createElement("td");
        const currentPrice = document.createElement("strong");
        const discount = document.createElement("small");
        const actionCell = document.createElement("td");
        const editButton = document.createElement("button");

        currentPrice.textContent = currency.format(Number(offer.price));
        discount.textContent = Number(offer.discount_percentage) > 0
            ? `${offer.discount_percentage}% de desconto`
            : "Sem desconto";
        priceCell.append(currentPrice, discount);

        editButton.type = "button";
        editButton.className = "text-button";
        editButton.dataset.offerId = offer.id;
        editButton.textContent = "Editar";
        actionCell.append(editButton);

        row.append(
            createCell(offer.product_title ?? `Produto #${offer.product_id}`),
            priceCell,
            createCell(offer.status),
            actionCell,
        );
        list.append(row);
    });
}

async function loadOffers() {
    setMessage(listMessage, "Carregando...");

    try {
        const response = await fetch(`${apiBase}/list.php`, { credentials: "same-origin" });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.error?.message ?? "Não foi possível carregar as ofertas.");
        }

        renderOffers(payload.data.offers);
        setMessage(listMessage, `${payload.data.offers.length} oferta(s) carregada(s).`, "success");
    } catch (error) {
        setMessage(listMessage, error.message, "error");
    }
}

productSelect.addEventListener("change", () => filterAffiliateLinks());

form.addEventListener("submit", async (event) => {
    event.preventDefault();
    setMessage(formMessage, "Salvando...");

    const data = new FormData(form);
    const isEditing = data.get("id") !== "";
    const endpoint = isEditing ? "update.php" : "create.php";

    try {
        const response = await fetch(`${apiBase}/${endpoint}`, {
            method: "POST",
            body: data,
            credentials: "same-origin",
        });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            const fields = Object.values(payload.error?.fields ?? {});
            throw new Error(fields[0] ?? payload.error?.message ?? "Não foi possível salvar a oferta.");
        }

        setMessage(formMessage, isEditing ? "Oferta atualizada." : "Oferta criada.", "success");
        resetForm();
        await loadOffers();
    } catch (error) {
        setMessage(formMessage, error.message, "error");
    }
});

list.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-offer-id]");

    if (button) {
        const offer = offers.get(button.dataset.offerId);

        if (offer) {
            populateForm(offer);
        }
    }
});

cancelEditButton.addEventListener("click", () => {
    resetForm();
    setMessage(formMessage, "Edição cancelada.");
});

document.querySelector("#refresh-offers").addEventListener("click", loadOffers);
filterAffiliateLinks();
loadOffers();

