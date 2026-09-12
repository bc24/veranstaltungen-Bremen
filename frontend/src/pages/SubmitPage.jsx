import { useState } from "react";
import { CATEGORY_OPTIONS } from "../categories.js";
import { createMarket, createCheckoutSession } from "../api/client.js";

const INITIAL = {
  name: "",
  category: "kuerbismarkt",
  description: "",
  street: "",
  postalCode: "",
  city: "",
  latitude: "",
  longitude: "",
  startDate: "",
  endDate: "",
  openingHours: "",
  website: "",
  contactName: "",
  contactEmail: "",
};

export default function SubmitPage() {
  const [form, setForm] = useState(INITIAL);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);

  function update(field, value) {
    setForm((f) => ({ ...f, [field]: value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      const { id } = await createMarket({
        ...form,
        latitude: parseFloat(form.latitude),
        longitude: parseFloat(form.longitude),
      });
      const { url } = await createCheckoutSession(id);
      window.location.href = url;
    } catch (err) {
      setError(err.message);
      setSubmitting(false);
    }
  }

  return (
    <div className="submit-page">
      <h1>Markt eintragen</h1>
      <p className="submit-intro">
        Alle Einträge in diesem Verzeichnis sind kostenpflichtig &ndash; es gibt keine kostenlose
        Basisversion. Nach der Zahlung wird dein Eintrag redaktionell geprüft und danach
        veröffentlicht.
      </p>

      <form onSubmit={handleSubmit} className="submit-form">
        <label>
          Name des Marktes *
          <input required value={form.name} onChange={(e) => update("name", e.target.value)} />
        </label>

        <label>
          Kategorie *
          <select value={form.category} onChange={(e) => update("category", e.target.value)}>
            {CATEGORY_OPTIONS.map((c) => (
              <option key={c.value} value={c.value}>
                {c.label}
              </option>
            ))}
          </select>
        </label>

        <label>
          Beschreibung *
          <textarea
            required
            rows={4}
            value={form.description}
            onChange={(e) => update("description", e.target.value)}
          />
        </label>

        <div className="form-row">
          <label>
            Straße &amp; Hausnummer *
            <input required value={form.street} onChange={(e) => update("street", e.target.value)} />
          </label>
          <label>
            PLZ *
            <input
              required
              value={form.postalCode}
              onChange={(e) => update("postalCode", e.target.value)}
            />
          </label>
          <label>
            Stadt *
            <input required value={form.city} onChange={(e) => update("city", e.target.value)} />
          </label>
        </div>

        <div className="form-row">
          <label>
            Breitengrad (Latitude) *
            <input
              required
              type="number"
              step="any"
              placeholder="z. B. 53.0793"
              value={form.latitude}
              onChange={(e) => update("latitude", e.target.value)}
            />
          </label>
          <label>
            Längengrad (Longitude) *
            <input
              required
              type="number"
              step="any"
              placeholder="z. B. 8.8017"
              value={form.longitude}
              onChange={(e) => update("longitude", e.target.value)}
            />
          </label>
        </div>
        <p className="form-hint">
          Koordinaten findest du z. B. über{" "}
          <a href="https://www.openstreetmap.org" target="_blank" rel="noopener noreferrer">
            OpenStreetMap
          </a>{" "}
          (Rechtsklick auf den Standort → „Was ist hier?“).
        </p>

        <div className="form-row">
          <label>
            Beginn *
            <input
              required
              type="date"
              value={form.startDate}
              onChange={(e) => update("startDate", e.target.value)}
            />
          </label>
          <label>
            Ende *
            <input
              required
              type="date"
              value={form.endDate}
              onChange={(e) => update("endDate", e.target.value)}
            />
          </label>
        </div>

        <label>
          Öffnungszeiten
          <input
            placeholder="z. B. Mo–So 10:00–18:00"
            value={form.openingHours}
            onChange={(e) => update("openingHours", e.target.value)}
          />
        </label>

        <label>
          Website
          <input
            type="url"
            placeholder="https://…"
            value={form.website}
            onChange={(e) => update("website", e.target.value)}
          />
        </label>

        <div className="form-row">
          <label>
            Ansprechpartner *
            <input
              required
              value={form.contactName}
              onChange={(e) => update("contactName", e.target.value)}
            />
          </label>
          <label>
            Kontakt-E-Mail *
            <input
              required
              type="email"
              value={form.contactEmail}
              onChange={(e) => update("contactEmail", e.target.value)}
            />
          </label>
        </div>

        {error && <div className="error-box">{error}</div>}

        <button type="submit" className="btn-primary" disabled={submitting}>
          {submitting ? "Wird verarbeitet …" : "Weiter zur kostenpflichtigen Anmeldung"}
        </button>
      </form>
    </div>
  );
}
