import { CATEGORY_LABELS } from "../categories.js";

function formatDateRange(start, end) {
  const opts = { day: "2-digit", month: "long", year: "numeric" };
  const s = new Date(start).toLocaleDateString("de-DE", opts);
  const e = new Date(end).toLocaleDateString("de-DE", opts);
  return s === e ? s : `${s} – ${e}`;
}

export default function MarketCard({ market, active, onSelect }) {
  return (
    <div
      className={`market-card ${active ? "market-card-active" : ""}`}
      onClick={() => onSelect(market.id)}
    >
      <span className={`category-badge category-${market.category}`}>
        {CATEGORY_LABELS[market.category] || market.category}
      </span>
      <h3>{market.name}</h3>
      <p className="market-card-desc">{market.description}</p>
      <div className="market-card-meta">
        <div>📅 {formatDateRange(market.startDate, market.endDate)}</div>
        <div>📍 {market.street}, {market.postalCode} {market.city}</div>
        {market.openingHours && <div>🕒 {market.openingHours}</div>}
        {market.website && (
          <div>
            🔗{" "}
            <a href={market.website} target="_blank" rel="noopener noreferrer" onClick={(e) => e.stopPropagation()}>
              Website
            </a>
          </div>
        )}
      </div>
    </div>
  );
}
