const form = document.querySelector("#campaign-form");
const list = document.querySelector("#campaigns-list");
const formMessage = document.querySelector("#form-message");
const listMessage = document.querySelector("#list-message");
const cancelEditButton = document.querySelector("#cancel-edit");
const formTitle = document.querySelector("#form-title");
const apiBase = document.body.dataset.campaignsApi;
let campaigns = new Map();

function setMessage(element, message, type = "") {
    element.textContent = message;
    element.className = `message ${type}`.trim();
}

function resetForm() {
    form.reset();
    form.elements.id.value = "";
    form.elements.active.checked = true;
    formTitle.textContent = "Nova campanha";
    cancelEditButton.hidden = true;
}

function populateForm(campaign) {
    form.elements.id.value = campaign.id;
    form.elements.name.value = campaign.name;
    form.elements.slug.value = campaign.slug;
    form.elements.source.value = campaign.source;
    form.elements.medium.value = campaign.medium ?? "";
    form.elements.active.checked = campaign.active;
    formTitle.textContent = `Editar campanha #${campaign.id}`;
    cancelEditButton.hidden = false;
    form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function createCell(text) {
    const cell = document.createElement("td");
    cell.textContent = text;
    return cell;
}

function renderCampaigns(items) {
    campaigns = new Map(items.map((campaign) => [String(campaign.id), campaign]));
    list.replaceChildren();

    if (items.length === 0) {
        const row = document.createElement("tr");
        const cell = createCell("Nenhuma campanha cadastrada.");
        cell.colSpan = 4;
        row.append(cell);
        list.append(row);
        return;
    }

    items.forEach((campaign) => {
        const row = document.createElement("tr");
        const nameCell = document.createElement("td");
        const name = document.createElement("strong");
        const slug = document.createElement("small");
        const actionCell = document.createElement("td");
        const editButton = document.createElement("button");

        name.textContent = campaign.name;
        slug.textContent = campaign.slug;
        nameCell.append(name, slug);

        editButton.type = "button";
        editButton.className = "text-button";
        editButton.dataset.campaignId = campaign.id;
        editButton.textContent = "Editar";
        actionCell.append(editButton);

        row.append(
            nameCell,
            createCell(campaign.medium ? `${campaign.source} / ${campaign.medium}` : campaign.source),
            createCell(campaign.active ? "Ativa" : "Inativa"),
            actionCell,
        );
        list.append(row);
    });
}

async function loadCampaigns() {
    setMessage(listMessage, "Carregando...");

    try {
        const response = await fetch(`${apiBase}/list.php`, { credentials: "same-origin" });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.error?.message ?? "Não foi possível carregar as campanhas.");
        }

        renderCampaigns(payload.data.campaigns);
        setMessage(listMessage, `${payload.data.campaigns.length} campanha(s) carregada(s).`, "success");
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
            throw new Error(fields[0] ?? payload.error?.message ?? "Não foi possível salvar a campanha.");
        }

        setMessage(formMessage, isEditing ? "Campanha atualizada." : "Campanha criada.", "success");
        resetForm();
        await loadCampaigns();
    } catch (error) {
        setMessage(formMessage, error.message, "error");
    }
});

list.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-campaign-id]");

    if (button) {
        const campaign = campaigns.get(button.dataset.campaignId);

        if (campaign) {
            populateForm(campaign);
        }
    }
});

cancelEditButton.addEventListener("click", () => {
    resetForm();
    setMessage(formMessage, "Edição cancelada.");
});

document.querySelector("#refresh-campaigns").addEventListener("click", loadCampaigns);
loadCampaigns();

