const API_BASE = "http://localhost/Projets/calanque_web_site/backend/public/";

async function safeJson(res) {
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    const snippet = text ? text.slice(0, 500) : '';
    return {
      success: false,
      message: 'Réponse invalide du serveur',
      status: res.status,
      data: { raw: snippet }
    };
  }
}

export async function getCampingsAvailability(startDate, endDate) {
  const params = new URLSearchParams();
  if (startDate) params.append("start_date", startDate);
  if (endDate) params.append("end_date", endDate);
  const res = await fetch(`${API_BASE}api/campings/availability?${params.toString()}`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function createReservation(reservationData) {
  const res = await fetch(`${API_BASE}api/reservations`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(reservationData)
  });
  return safeJson(res);
}

export async function createReservationForCheckout(reservationData) {
  const res = await fetch(`${API_BASE}api/reservation/checkout/create`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(reservationData)
  });
  return safeJson(res);
}

export async function createReservationCheckoutSession(data) {
  const res = await fetch(`${API_BASE}api/reservation/checkout/session`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
  });
  return safeJson(res);
}

export async function getUserReservations(userId) {
  const res = await fetch(`${API_BASE}api/reservations/user/${userId}`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function getReservationById(id) {
  const res = await fetch(`${API_BASE}api/reservations/${id}`, {
    method: "GET",
    credentials: "include"
  });
  return safeJson(res);
}

export async function updateReservationPaymentStatus(id, paymentStatus) {
  const res = await fetch(`${API_BASE}api/reservations/${id}/payment-status`, {
    method: "PUT",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ payment_status: paymentStatus })
  });
  return safeJson(res);
}