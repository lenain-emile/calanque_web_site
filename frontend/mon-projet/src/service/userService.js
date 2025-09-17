const API_BASE = "http://localhost/backend/public/"; 

export async function loginUser(email, password) {
  const res = await fetch(`${API_BASE}/api/users/login`, {
    method: "POST",
    credentials: "include", // envoie le cookie de session PHP
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password })
  });
  return res.json(); // { success, message, data }
}

export async function logoutUser() {
  const res = await fetch(`${API_BASE}/api/users/logout`, {
    method: "POST",
    credentials: "include"
  });
  return res.json();
}

export async function getUser() {
  const res = await fetch(`${API_BASE}/api/users/me`, {
    method: "GET",
    credentials: "include"
  });
  return res.json();
}
