const API_BASE = "/api";

async function request(path, options = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
    ...options,
  });

  if (!res.ok) {
    let message = `Fehler ${res.status}`;
    try {
      const body = await res.json();
      if (body.error) message = body.error;
    } catch {
      // ignore
    }
    throw new Error(message);
  }

  if (res.status === 204) return null;
  return res.json();
}

export function fetchMarkets(filters = {}) {
  const params = new URLSearchParams(
    Object.fromEntries(Object.entries(filters).filter(([, v]) => v))
  );
  const query = params.toString();
  return request(`/markets${query ? `?${query}` : ""}`);
}

export function fetchMarket(id) {
  return request(`/markets/${id}`);
}

export function createMarket(data) {
  return request("/markets", { method: "POST", body: JSON.stringify(data) });
}

export function createCheckoutSession(marketId) {
  return request("/payments/create-checkout-session", {
    method: "POST",
    body: JSON.stringify({ marketId }),
  });
}

export function adminLogin(email, password) {
  return request("/admin/login", { method: "POST", body: JSON.stringify({ email, password }) });
}

export function fetchAdminMarkets(token, status) {
  const query = status ? `?status=${status}` : "";
  return request(`/admin/markets${query}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
}

export function updateMarketStatus(token, id, status, rejectionReason) {
  return request(`/admin/markets/${id}`, {
    method: "PATCH",
    headers: { Authorization: `Bearer ${token}` },
    body: JSON.stringify({ status, rejectionReason }),
  });
}

export function deleteMarket(token, id) {
  return request(`/admin/markets/${id}`, {
    method: "DELETE",
    headers: { Authorization: `Bearer ${token}` },
  });
}
