import { CATEGORY_OPTIONS } from "../categories.js";

export default function Filters({ filters, onChange }) {
  function update(field, value) {
    onChange({ ...filters, [field]: value });
  }

  return (
    <div className="filters-bar">
      <input
        type="text"
        placeholder="Suche nach Name, Ort, Beschreibung …"
        value={filters.q}
        onChange={(e) => update("q", e.target.value)}
        className="filters-search"
      />
      <select value={filters.category} onChange={(e) => update("category", e.target.value)}>
        <option value="">Alle Kategorien</option>
        {CATEGORY_OPTIONS.map((c) => (
          <option key={c.value} value={c.value}>
            {c.label}
          </option>
        ))}
      </select>
      <input
        type="text"
        placeholder="Stadt"
        value={filters.city}
        onChange={(e) => update("city", e.target.value)}
      />
      <label className="filters-date">
        von
        <input type="date" value={filters.from} onChange={(e) => update("from", e.target.value)} />
      </label>
      <label className="filters-date">
        bis
        <input type="date" value={filters.to} onChange={(e) => update("to", e.target.value)} />
      </label>
    </div>
  );
}
