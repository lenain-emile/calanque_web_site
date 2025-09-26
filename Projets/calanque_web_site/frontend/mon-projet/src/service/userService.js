
const API_BASE = "http://localhost/Projets/calanque_web_site/backend/public/";


export async function loginUser(email, password) {
  const res = await fetch(`${API_BASE}api/users/login`, {
    method: "POST",
    credentials: "include", // envoie le cookie de session PHP
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password })
  });
  return res.json(); // { success, message, data }
}


export async function logoutUser() {
  const res = await fetch(`${API_BASE}api/users/logout`, {
    method: "POST",
    credentials: "include"
  });
  return res.json();
}


// Cette route n'existe pas côté backend, à adapter si besoin
export async function getUser() {
  // Placeholder: à implémenter côté backend si besoin
  return { error: 'Route /api/users/me non implémentée côté backend' };
}
