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

export async function getAllTrails() {
  const res = await fetch(`${API_BASE}api/trails`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function getTrailById(id) {
  const res = await fetch(`${API_BASE}api/trails/${id}`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function getTrailResources(id) {
  const res = await fetch(`${API_BASE}api/trails/${id}/resources`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function createTrail(trailData) {
  const res = await fetch(`${API_BASE}api/trails`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(trailData)
  });
  return safeJson(res);
}

export async function updateTrail(id, trailData) {
  const res = await fetch(`${API_BASE}api/trails/${id}`, {
    method: "PUT",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(trailData)
  });
  return safeJson(res);
}

export async function deleteTrail(id) {
  const res = await fetch(`${API_BASE}api/trails/${id}`, {
    method: "DELETE",
    credentials: "include"
  });
  return safeJson(res);
}
