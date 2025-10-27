const API_BASE = "http://localhost/Projets/calanque_web_site/backend/public/";

async function safeJson(res) {
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('Erreur de parsing JSON:', e);
    const snippet = text ? text.slice(0, 500) : '';
    return {
      success: false,
      message: 'Réponse invalide du serveur',
      status: res.status,
      data: { raw: snippet }
    };
  }
}

export async function getAllResources() {
  const res = await fetch(`${API_BASE}api/natural-resources`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function getResourceById(id) {
  const res = await fetch(`${API_BASE}api/natural-resources/${id}`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function getResourcesByType(type) {
  const res = await fetch(`${API_BASE}api/natural-resources/type/${type}`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function createResource(resourceData) {
  const res = await fetch(`${API_BASE}api/natural-resources`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(resourceData)
  });
  return safeJson(res);
}

export async function updateResource(id, resourceData) {
  const res = await fetch(`${API_BASE}api/natural-resources/${id}`, {
    method: "PUT",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(resourceData)
  });
  return safeJson(res);
}

export async function deleteResource(id) {
  const res = await fetch(`${API_BASE}api/natural-resources/${id}`, {
    method: "DELETE",
    credentials: "include"
  });
  return safeJson(res);
}
