import { useEffect, useState } from "react";
import AdminLoginPage from "./AdminLoginPage.jsx";
import { CATEGORY_LABELS } from "../categories.js";
import { fetchAdminMarkets, updateMarketStatus, deleteMarket } from "../api/client.js";

const STATUS_LABELS = {
  pending_payment: "Zahlung ausstehend",
  pending_review: "Prüfung ausstehend",
  published: "Veröffentlicht",
  rejected: "Abgelehnt",
};

export default function AdminDashboardPage() {
  const [token, setToken] = useState(() => localStorage.getItem("adminToken"));
  const [statusFilter, setStatusFilter] = useState("pending_review");
  const [markets, setMarkets] = useState([]);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  function load() {
    if (!token) return;
    setLoading(true);
    setError(null);
    fetchAdminMarkets(token, statusFilter)
      .then(setMarkets)
      .catch((err) => {
        if (err.message.includes("401")) {
          logout();
        } else {
          setError(err.message);
        }
      })
      .finally(() => setLoading(false));
  }

  useEffect(load, [token, statusFilter]);

  function logout() {
    localStorage.removeItem("adminToken");
    setToken(null);
  }

  async function handlePublish(id) {
    await updateMarketStatus(token, id, "published");
    load();
  }

  async function handleReject(id) {
    const reason = window.prompt("Grund der Ablehnung (optional):") || "";
    await updateMarketStatus(token, id, "rejected", reason);
    load();
  }

  async function handleDelete(id) {
    if (!window.confirm("Diesen Eintrag endgültig löschen?")) return;
    await deleteMarket(token, id);
    load();
  }

  if (!token) {
    return <AdminLoginPage />;
  }

  return (
    <div className="admin-dashboard">
      <div className="admin-dashboard-header">
        <h1>Kuratierung</h1>
        <button onClick={logout} className="btn-secondary">
          Abmelden
        </button>
      </div>

      <div className="admin-filter-bar">
        {Object.entries(STATUS_LABELS).map(([value, label]) => (
          <button
            key={value}
            className={`filter-btn ${statusFilter === value ? "filter-btn-active" : ""}`}
            onClick={() => setStatusFilter(value)}
          >
            {label}
          </button>
        ))}
        <button
          className={`filter-btn ${statusFilter === "" ? "filter-btn-active" : ""}`}
          onClick={() => setStatusFilter("")}
        >
          Alle
        </button>
      </div>

      {error && <div className="error-box">{error}</div>}
      {loading && <p>Lade …</p>}
      {!loading && markets.length === 0 && <p>Keine Einträge in diesem Status.</p>}

      <div className="admin-market-list">
        {markets.map((m) => (
          <div key={m.id} className="admin-market-row">
            <div>
              <strong>{m.name}</strong> &ndash; {CATEGORY_LABELS[m.category] || m.category}
              <div className="admin-market-meta">
                {m.street}, {m.postalCode} {m.city} · {m.startDate} bis {m.endDate}
              </div>
              <div className="admin-market-meta">
                Kontakt: {m.contactName} ({m.contactEmail}) · Zahlung: {m.paymentStatus} · Status:{" "}
                {STATUS_LABELS[m.status] || m.status}
              </div>
              {m.rejectionReason && (
                <div className="admin-market-meta">Ablehnungsgrund: {m.rejectionReason}</div>
              )}
            </div>
            <div className="admin-market-actions">
              {m.status !== "published" && m.paymentStatus === "paid" && (
                <button className="btn-primary" onClick={() => handlePublish(m.id)}>
                  Veröffentlichen
                </button>
              )}
              {m.status !== "rejected" && (
                <button className="btn-secondary" onClick={() => handleReject(m.id)}>
                  Ablehnen
                </button>
              )}
              <button className="btn-danger" onClick={() => handleDelete(m.id)}>
                Löschen
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
