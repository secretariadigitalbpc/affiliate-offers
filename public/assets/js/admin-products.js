const form = document.querySelector("#product-form");
const list = document.querySelector("#products-list");
const formMessage = document.querySelector("#form-message");
const listMessage = document.querySelector("#list-message");
const cancelEditButton = document.querySelector("#cancel-edit");
const formTitle = document.querySelector("#form-title");
const apiBase = document.body.dataset.productsApi;
let products = new Map();

function setMessage(element, message, type = "") {
    element.textContent = message;
    element.className = `message ${type}`.trim();
}

function resetForm() {
    form.reset();
    form.elements.active.checked = true;
    form.elements.id.value = "";
    formTitle.textContent = "Novo produto";
    cancelEditButton.hidden = true;
}

function populateForm(product) {
    form.elements.id.value = product.id;
    form.elements.marketplace.value = product.marketplace;
    form.elements.marketplace_product_id.value = product.marketplace_product_id;
    form.elements.title.value = product.title;
    form.elements.slug.value = product.slug;
    form.elements.image_url.value = product.image_url ?? "";
    form.elements.seller_name.value = product.seller_name ?? "";
    form.elements.rating.value = product.rating ?? "";
    form.elements.sales_count.value = product.sales_count ?? "";
    form.elements.active.checked = product.active;
    formTitle.textContent = `Editar produto #${product.id}`;
    cancelEditButton.hidden = false;
    form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function createCell(text) {
    const cell = document.createElement("td");
    cell.textContent = text;
    return cell;
}

function renderProducts(items) {
    products = new Map(items.map((product) => [String(product.id), product]));
    list.replaceChildren();

    if (items.length === 0) {
        const row = document.createElement("tr");
        const cell = createCell("Nenhum produto cadastrado.");
        cell.colSpan = 4;
        row.append(cell);
        list.append(row);
        return;
    }

    items.forEach((product) => {
        const row = document.createElement("tr");
        const productCell = document.createElement("td");
        const title = document.createElement("strong");
        const identifier = document.createElement("small");
        const actionCell = document.createElement("td");
        const editButton = document.createElement("button");

        title.textContent = product.title;
        identifier.textContent = product.marketplace_product_id;
        productCell.append(title, identifier);

        editButton.type = "button";
        editButton.className = "text-button";
        editButton.dataset.productId = product.id;
        editButton.textContent = "Editar";
        actionCell.append(editButton);

        row.append(
            productCell,
            createCell(product.marketplace === "mercado_livre" ? "Mercado Livre" : "Shopee"),
            createCell(product.active ? "Ativo" : "Inativo"),
            actionCell,
        );
        list.append(row);
    });
}

async function loadProducts() {
    setMessage(listMessage, "Carregando...");

    try {
        const response = await fetch(`${apiBase}/list.php`, { credentials: "same-origin" });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.error?.message ?? "Não foi possível carregar os produtos.");
        }

        renderProducts(payload.data.products);
        setMessage(listMessage, `${payload.data.products.length} produto(s) carregado(s).`, "success");
    } catch (error) {
        setMessage(listMessage, error.message, "error");
    }
}

form.addEventListener("submit", async (event) => {
    event.preventDefault();
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
            throw new Error(fields[0] ?? payload.error?.message ?? "Não foi possível salvar o produto.");
        }

        setMessage(formMessage, isEditing ? "Produto atualizado." : "Produto criado.", "success");
        resetForm();
        await loadProducts();
    } catch (error) {
        setMessage(formMessage, error.message, "error");
    }
});

list.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-product-id]");

    if (button) {
        const product = products.get(button.dataset.productId);

        if (product) {
            populateForm(product);
        }
    }
});

cancelEditButton.addEventListener("click", () => {
    resetForm();
    setMessage(formMessage, "Edição cancelada.");
});

document.querySelector("#refresh-products").addEventListener("click", loadProducts);
loadProducts();

