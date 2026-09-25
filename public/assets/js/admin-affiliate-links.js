const form = document.querySelector("#affiliate-link-form");
const list = document.querySelector("#affiliate-links-list");
const formMessage = document.querySelector("#form-message");
const listMessage = document.querySelector("#list-message");
const cancelEditButton = document.querySelector("#cancel-edit");
const formTitle = document.querySelector("#form-title");
const productSelect = form.elements.product_id;
const apiBase = document.body.dataset.affiliateLinksApi;
let affiliateLinks = new Map();

function setMessage(element, message, type = "") {
    element.textContent = message;
    element.className = `message ${type}`.trim();
}

function syncMarketplace() {
    const option = productSelect.selectedOptions[0];
    form.elements.marketplace.value = option?.dataset.marketplace ?? "";
}

function resetForm() {
    form.reset();
    form.elements.id.value = "";
    form.elements.active.checked = true;
    formTitle.textContent = "Novo link";
    cancelEditButton.hidden = true;
    syncMarketplace();
}

function populateForm(link) {
    form.elements.id.value = link.id;
    productSelect.value = link.product_id;
    form.elements.affiliate_url.value = link.affiliate_url;
    form.elements.tag.value = link.tag ?? "";
    form.elements.active.checked = link.active;
    syncMarketplace();
    formTitle.textContent = `Editar link #${link.id}`;
    cancelEditButton.hidden = false;
    form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function createCell(text) {
    const cell = document.createElement("td");
    cell.textContent = text;
    return cell;
}

function renderLinks(items) {
    affiliateLinks = new Map(items.map((link) => [String(link.id), link]));
    list.replaceChildren();

    if (items.length === 0) {
        const row = document.createElement("tr");
        const cell = createCell("Nenhum link cadastrado.");
        cell.colSpan = 4;
        row.append(cell);
        list.append(row);
        return;
    }

    items.forEach((link) => {
        const row = document.createElement("tr");
        const linkCell = document.createElement("td");
        const anchor = document.createElement("a");
        const tag = document.createElement("small");
        const actionCell = document.createElement("td");
        const editButton = document.createElement("button");

        anchor.href = link.affiliate_url;
        anchor.target = "_blank";
        anchor.rel = "noopener noreferrer";
        anchor.textContent = "Abrir link";
        tag.textContent = link.tag ? `Tag: ${link.tag}` : "Sem tag";
        linkCell.append(anchor, tag);

        editButton.type = "button";
        editButton.className = "text-button";
        editButton.dataset.linkId = link.id;
        editButton.textContent = "Editar";
        actionCell.append(editButton);

        row.append(
            createCell(link.product_title ?? `Produto #${link.product_id}`),
            linkCell,
            createCell(link.active ? "Ativo" : "Inativo"),
            actionCell,
        );
        list.append(row);
    });
}

async function loadLinks() {
    setMessage(listMessage, "Carregando...");

    try {
        const response = await fetch(`${apiBase}/list.php`, { credentials: "same-origin" });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.error?.message ?? "Não foi possível carregar os links.");
        }

        renderLinks(payload.data.affiliate_links);
        setMessage(listMessage, `${payload.data.affiliate_links.length} link(s) carregado(s).`, "success");
    } catch (error) {
        setMessage(listMessage, error.message, "error");
    }
}

productSelect.addEventListener("change", syncMarketplace);

form.addEventListener("submit", async (event) => {
    event.preventDefault();
    syncMarketplace();
    setMessage(formMessage, "Salvando...");

    const data = new FormData(form);
    data.set("active", form.elements.active.checked ? "1" : "0");
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
            throw new Error(fields[0] ?? payload.error?.message ?? "Não foi possível salvar o link.");
        }

        setMessage(formMessage, isEditing ? "Link atualizado." : "Link criado.", "success");
        resetForm();
        await loadLinks();
    } catch (error) {
        setMessage(formMessage, error.message, "error");
    }
});

list.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-link-id]");

    if (button) {
        const link = affiliateLinks.get(button.dataset.linkId);

        if (link) {
            populateForm(link);
        }
    }
});

cancelEditButton.addEventListener("click", () => {
    resetForm();
    setMessage(formMessage, "Edição cancelada.");
});

document.querySelector("#refresh-links").addEventListener("click", loadLinks);
syncMarketplace();
loadLinks();

